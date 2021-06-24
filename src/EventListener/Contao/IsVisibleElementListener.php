<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\MultilingualFieldsBundle\Util\MultilingualFieldsUtil;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;

/**
 * @Hook("isVisibleElement")
 */
class IsVisibleElementListener
{
    /**
     * @var MultilingualFieldsUtil
     */
    protected $multilingualFieldsUtil;
    /**
     * @var ContainerUtil
     */
    protected $containerUtil;

    public function __construct(
        MultilingualFieldsUtil $multilingualFieldsUtil,
        ContainerUtil $containerUtil
    ) {
        $this->multilingualFieldsUtil = $multilingualFieldsUtil;
        $this->containerUtil = $containerUtil;
    }

    public function __invoke($element, $return)
    {
        if ($this->containerUtil->isBackend()) {
            return $return;
        }

        if ($this->multilingualFieldsUtil->hasContentLanguageField($element->id) && $element->mf_language && $element->mf_language !== $GLOBALS['TL_LANGUAGE']) {
            return false;
        }

        // adjust fields
        if ($this->multilingualFieldsUtil->isTranslatable('tl_content')) {
            foreach ($this->multilingualFieldsUtil->getTranslatableFields('tl_content') as $field) {
                if (!$element->{$GLOBALS['TL_LANGUAGE'].'_translate_'.$field}) {
                    continue;
                }

                $element->{$field} = $element->{$GLOBALS['TL_LANGUAGE'].'_'.$field};
            }
        }

        return $return;
    }
}
