<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener\Contao;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\MultilingualFieldsBundle\Util\MultilingualFieldsUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * @Hook("replaceInsertTags")
 */
class ReplaceInsertTagsListener
{
    /**
     * @var MultilingualFieldsUtil
     */
    protected $multilingualFieldsUtil;
    /**
     * @var ContaoFramework
     */
    protected $framework;
    private Utils $utils;

    public function __construct(
        ContaoFramework $framework,
        MultilingualFieldsUtil $multilingualFieldsUtil,
        Utils $utils
    ) {
        $this->framework = $framework;
        $this->multilingualFieldsUtil = $multilingualFieldsUtil;
        $this->utils = $utils;
    }

    public function __invoke($tag)
    {
        $tagData = explode('::', $tag);

        if (0 !== strpos($tagData[0], 'mf')) {
            return false;
        }

        switch ($tagData[0]) {
            case 'mf_event_url':
            case 'mf_news_url':
            case 'mf_faq_url':
                if (!isset($tagData[1]) || !isset($tagData[2])) {
                    return false;
                }

                $type = explode('_', $tagData[0])[1];

                if ('event' === $type) {
                    $table = 'tl_calendar_events';
                } else {
                    $table = 'tl_'.$type;
                }

                $entity = $tagData[1];
                $language = $tagData[2];

                if (null === ($entityObj = $this->utils->model()->findModelInstanceByPk($table, $entity))) {
                    return false;
                }

                if (!$this->multilingualFieldsUtil->isTranslatable($table)) {
                    return $this->framework->getAdapter(Controller::class)->replaceInsertTags(
                        '{{'.$type.'_url::'.$entityObj->id.'}}', false
                    );
                }

                if (empty($GLOBALS['TL_DCA'][$table])) {
                    Controller::loadDataContainer($table);
                }
                $dca = $GLOBALS['TL_DCA'][$table];

                $ptable = $dca['config']['ptable'] ?? null;

                if (!$ptable || null === ($archive = $this->utils->model()->findOneModelInstanceBy($ptable, [$ptable.'.id=?'], [$entityObj->pid]))) {
                    return false;
                }

                $url = $this->framework->getAdapter(Controller::class)->replaceInsertTags(
                    '{{changelanguage_link_url::'.$archive->jumpTo.'::'.$language.'}}'
                );

                // alias
                $entityObj = $this->multilingualFieldsUtil->translateModel($table, $entityObj, $language);

                $url .= '/'.$entityObj->alias;

                return $url;
        }

        return false;
    }
}
