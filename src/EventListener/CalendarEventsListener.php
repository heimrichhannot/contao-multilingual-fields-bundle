<?php

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener;

use Contao\CalendarModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Template;
use HeimrichHannot\EventRegistrationBundle\Model\CalendarEventsModel;
use HeimrichHannot\MultilingualFieldsBundle\Multilingual\TableBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

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
}