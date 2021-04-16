<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;

/**
 * @Hook("loadDataContainer", priority=-256)
 */
class LoadDataContainerListener
{
    const EDIT_LANGUAGES_PARAM = 'edit_languages';

    protected static $processedTables = [];

    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var UrlUtil
     */
    protected $urlUtil;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var StringUtil
     */
    protected $stringUtil;
    /**
     * @var DcaUtil
     */
    protected $dcaUtil;

    public function __construct(array $bundleConfig, UrlUtil $urlUtil, Request $request, StringUtil $stringUtil, DcaUtil $dcaUtil)
    {
        $this->bundleConfig = $bundleConfig;
        $this->urlUtil = $urlUtil;
        $this->request = $request;
        $this->stringUtil = $stringUtil;
        $this->dcaUtil = $dcaUtil;
    }

    public function __invoke($table)
    {
        // only run once
        if (\in_array($table, static::$processedTables)) {
            return;
        }

        static::$processedTables[] = $table;

        $this->initAssets();
        $this->initConfig($table);
    }

    protected function initAssets()
    {
        $GLOBALS['TL_CSS']['contao-multilingual-fields-bundle'] = 'bundles/heimrichhannotmultilingualfields/js/contao-multilingual-fields-bundle.css';
    }

    protected function initConfig($table)
    {
        if (!isset($this->bundleConfig['data_containers']) || !\is_array($this->bundleConfig['data_containers']) ||
            !isset($this->bundleConfig['data_containers'][$table])) {
            return;
        }

        $config = $this->bundleConfig['data_containers'][$table];

        $dca = &$GLOBALS['TL_DCA'][$table];

        $isEditMode = $this->request->getGet(static::EDIT_LANGUAGES_PARAM);

        // add translated fields
        $paletteData = [];
        $readOnlyFields = [];

        foreach ($config['fields'] as $fieldConfig) {
            $field = $fieldConfig['name'];

            if (!isset($dca['fields'][$field])) {
                continue;
            }

            if (!isset($paletteData[$fieldConfig['legend']])) {
                $paletteData[$fieldConfig['legend']] = [];
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

                $translatedLabel[0] = ((string) $label[0]).' ('.$GLOBALS['TL_LANG']['LNG'][$language].')';
                $translatedLabel[1] = $label[1];

                // release the reference
                unset($fieldDca['label']);

                $fieldDca['label'] = $translatedLabel;

                // link to the original field
                $fieldDca['eval']['translatedField'] = $field;

                // copy the field
                $dca['fields'][$translatedFieldname] = $fieldDca;

                // add the original fields as readonly
                $readOnlyFields[] = $field;

                // add the selector
                $checkboxField = $language.'_translate_'.$field;

                $dca['fields'][$checkboxField] = [
                    'label' => [
                        sprintf($GLOBALS['TL_LANG']['MSC']['multilingualFieldsBundle']['mf_translateField'][0],
                            ((string) $label[0]), $GLOBALS['TL_LANG']['LNG'][$language]),
                        $GLOBALS['TL_LANG']['MSC']['multilingualFieldsBundle']['mf_translateField'][1],
                    ],
                    'exclude' => true,
                    'inputType' => 'checkbox',
                    'eval' => ['tl_class' => 'w50', 'submitOnChange' => true, 'translationField' => $translatedFieldname],
                    'sql' => "char(1) NOT NULL default ''",
                ];

                // add the subpalette
                $dca['palettes']['__selector__'][] = $checkboxField;
                $dca['subpalettes'][$checkboxField] = $translatedFieldname;

                // add field to palette data
                if (!\in_array($field.','.$checkboxField, $paletteData[$fieldConfig['legend']])) {
                    $paletteData[$fieldConfig['legend']][] = $field.','.$checkboxField;
                }
            }
        }

        // set fields to readonly
        if ($isEditMode) {
            $this->dcaUtil->setFieldsToReadOnly($dca, [
                'fields' => $readOnlyFields,
            ]);
        }

        // add language switch
        $dca['fields']['mf_editLanguages'] = [
            'inputType' => 'hyperlink',
            'eval' => [
                'text' => &$GLOBALS['TL_LANG']['MSC']['multilingualFieldsBundle'][
                $isEditMode ? 'mf_closeEditLanguages' : 'mf_editLanguages'
                ],
                'linkClass' => 'tl_submit',
                'tl_class' => 'long edit-languages',
                'url' => function (DataContainer $dc) use ($isEditMode) {
                    if ($isEditMode) {
                        return $this->urlUtil->removeQueryString([static::EDIT_LANGUAGES_PARAM]);
                    }

                    return $this->urlUtil->addQueryString(static::EDIT_LANGUAGES_PARAM.'=1');
                },
            ],
        ];

        // create palette for editing the fields
        if ($isEditMode) {
            $fixedPalette = '';

            foreach ($paletteData as $legend => $fields) {
                if (!$this->stringUtil->endsWith($legend, '_legend')) {
                    $legend .= '_legend';
                }

                $fixedPalette .= '{'.$legend.'},';

                foreach ($fields as $field) {
                    $fixedPalette .= $field.',';
                }

                $fixedPalette = rtrim($fixedPalette, ',');

                $fixedPalette .= ';';
            }

            $fixedPalette = 'mf_editLanguages;'.$fixedPalette;
        } else {
            $fixedPalette = 'mf_editLanguages;'.$dca['palettes']['default'];
        }

        foreach ($config['palettes'] as $palette) {
            $dca['palettes'][$palette] = $fixedPalette;
        }
    }
}
