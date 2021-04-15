<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener\Contao;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Hook;

/**
 * @Hook("loadDataContainer", priority=-256)
 */
class LoadDataContainerListener
{
    protected static $processedTables = [];

    /**
     * @var array
     */
    protected $bundleConfig;

    public function __construct(array $bundleConfig)
    {
        $this->bundleConfig = $bundleConfig;
    }

    public function __invoke($table)
    {
        // only run once
        if (\in_array($table, static::$processedTables)) {
            return;
        }

        static::$processedTables[] = $table;

        if (!isset($this->bundleConfig['data_containers']) || !\is_array($this->bundleConfig['data_containers']) ||
            !isset($this->bundleConfig['data_containers'][$table]) || !isset($this->bundleConfig['languages']) ||
            empty($this->bundleConfig['languages'])) {
            return;
        }

        $config = $this->bundleConfig['data_containers'][$table];

        $dca = &$GLOBALS['TL_DCA'][$table];

        foreach ($config['fields'] as $fieldConfig) {
            $field = $fieldConfig['name'];

            if (!isset($dca['fields'][$field])) {
                continue;
            }

            foreach ($this->bundleConfig['languages'] as $language) {
                $translatedFieldname = $language.'_'.$field;
                $fieldDca = $dca['fields'][$field];

                // adjust the label
                if (isset($dca['fields'][$field]['label'])) {
                    $label = $dca['fields'][$field]['label'];
                } else {
                    $label = $GLOBALS['TL_LANG'][$table][$field];
                }

                $label[0] = ((string) $label[0]).' ('.$GLOBALS['TL_LANG']['LNG'][$language].')';

                // release the reference
                unset($fieldDca['label']);

                $fieldDca['label'] = $label;

                // copy the field
                $dca['fields'][$translatedFieldname] = $fieldDca;

                // add to palette
                PaletteManipulator::create()
                    ->addField($translatedFieldname, $field)
                    ->applyToPalette('default', $table)
                ;
            }
        }
    }
}
