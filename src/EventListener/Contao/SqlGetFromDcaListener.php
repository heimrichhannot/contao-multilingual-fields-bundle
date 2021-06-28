<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\MultilingualFieldsBundle\Util\MultilingualFieldsUtil;

/**
 * @Hook("sqlGetFromDca")
 */
class SqlGetFromDcaListener
{
    /**
     * @var MultilingualFieldsUtil
     */
    protected $multilingualFieldsUtil;

    /**
     * SqlGetFromDcaListener constructor.
     */
    public function __construct(MultilingualFieldsUtil $multilingualFieldsUtil)
    {
        $this->multilingualFieldsUtil = $multilingualFieldsUtil;
    }

    public function __invoke(array $sqlDcaData)
    {
        if (!$this->multilingualFieldsUtil->hasContentLanguageField()) {
            return $sqlDcaData;
        }

        $sqlDcaData['tl_content']['TABLE_FIELDS']['mf_language'] = "`mf_language` varchar(5) NOT NULL default ''";

        return $sqlDcaData;
    }
}
