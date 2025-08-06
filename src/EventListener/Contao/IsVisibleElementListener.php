<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use HeimrichHannot\MultilingualFieldsBundle\Util\MultilingualFieldsUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;

#[AsHook('isVisibleElement')]
class IsVisibleElementListener
{
    public function __construct(
        protected MultilingualFieldsUtil $multilingualFieldsUtil,
        private Utils $utils,
    ) {
    }

    public function __invoke($element, $return)
    {
        if ($this->utils->container()->isBackend()) {
            return $return;
        }

        if ($this->multilingualFieldsUtil->hasContentLanguageField($element->id) && $element->mf_language && $element->mf_language !== $GLOBALS['TL_LANGUAGE']) {
            return false;
        }

        // adjust fields
        if ($this->multilingualFieldsUtil->isTranslatable('tl_content')) {
            foreach ($this->multilingualFieldsUtil->getTranslatableFields('tl_content') as $field) {
                if (!$element->{$GLOBALS['TL_LANGUAGE'] . '_translate_' . $field}) {
                    continue;
                }

                $element->{$field} = $element->{$GLOBALS['TL_LANGUAGE'] . '_' . $field};
            }
        }

        return $return;
    }
}
