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
            return true;
        }

        if ($this->multilingualFieldsUtil->hasContentLanguageField($element->id) && $element->mf_language && $element->mf_language !== $GLOBALS['TL_LANGUAGE']) {
            return false;
        }

        return $return;
    }
}
