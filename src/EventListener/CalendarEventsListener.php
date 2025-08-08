<?php

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener;

use Contao\CalendarModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Input;
use Contao\PageModel;
use Contao\Template;
use HeimrichHannot\EventRegistrationBundle\Model\CalendarEventsModel;
use HeimrichHannot\MultilingualFieldsBundle\Multilingual\TableBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;
use Terminal42\ChangeLanguage\EventListener\Navigation\NavigationHandlerInterface;

class CalendarEventsListener
{
    public function __construct(
        private readonly TableBuilder $tableBuilder,
        private readonly RequestStack $requestStack,
    )
    {
    }

    #[AsHook('parseTemplate')]
    public function onParseTemplate(Template $template): void
    {
        if (!str_starts_with($template->getName(), 'event_')) {
            return;
        }
        if (!($template->calendar instanceof CalendarModel)) {
            return;
        }

        $mlTable = $this->tableBuilder->buildFor(CalendarEventsModel::getTable());
        if (!$mlTable) {
            return;
        }

        $calendarEvent = CalendarEventsModel::findByPk($template->id);
        if (!$calendarEvent) {
            return;
        }

        $language = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'en';

        foreach ($mlTable->fields as $mlField) {
            $template->{$mlField->fieldname} = $mlField->valueFor($calendarEvent->row(), $language);
        }
    }

    #[AsHook('changelanguageNavigation', priority: -1)]
    public function onChangelanguageNavigation(ChangelanguageNavigationEvent $event): void
    {
        $current = $this->findCurrent();

        if (null === $current) {
            return;
        }

        $navigationItem = $event->getNavigationItem();

        if ($navigationItem->isCurrentPage()) {
            return;
        }

        /** @var CalendarModel|null $parent */
        $parent = $current->getRelated('pid');
        if (null === $parent) {
            return;
        }


        if (0 !== (int) $parent->master) {
            return;
        }

        $targetPage = $navigationItem->getTargetPage();

        if (null === $targetPage) {
            return;
        }

        $event->getUrlParameterBag()->setUrlAttribute($this->getUrlKey(), $current->alias ?: $current->id);
        $event->getNavigationItem()->setTitle($current->title);
        $event->getNavigationItem()->setPageTitle($current->pageTitle);
    }

    protected function findCurrent(): ?\Contao\CalendarEventsModel
    {
        $alias = $this->getAutoItem();

        if ('' === $alias) {
            return null;
        }

        return CalendarEventsModel::findByAlias($alias);
    }

    protected function getAutoItem(): string
    {
        $strKey = $this->getUrlKey();

        if (
            !isset($GLOBALS['TL_CONFIG']['useAutoItem'])
            || (
                $GLOBALS['TL_CONFIG']['useAutoItem']
                && isset($GLOBALS['TL_AUTO_ITEM'])
                && \in_array($strKey, $GLOBALS['TL_AUTO_ITEM'], true)
            )
        ) {
            $strKey = 'auto_item';
        }

        return (string) Input::get($strKey, false, true);
    }

    protected function getUrlKey(): string
    {
        return isset($GLOBALS['TL_CONFIG']['useAutoItem']) ? 'events' : 'auto_item';
    }
}