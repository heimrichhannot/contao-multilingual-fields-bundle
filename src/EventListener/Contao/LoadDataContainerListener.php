<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener\Contao;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use HeimrichHannot\MultilingualFieldsBundle\Util\MultilingualFieldsUtil;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
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
    /**
     * @var ContainerUtil
     */
    protected $containerUtil;
    /**
     * @var ArrayUtil
     */
    protected $arrayUtil;
    /**
     * @var MultilingualFieldsUtil
     */
    protected $multilingualFieldsUtil;
    /**
     * @var ModelUtil
     */
    protected $modelUtil;

    public function __construct(
        array $bundleConfig,
        MultilingualFieldsUtil $multilingualFieldsUtil,
        UrlUtil $urlUtil,
        Request $request,
        StringUtil $stringUtil,
        DcaUtil $dcaUtil,
        ContainerUtil $containerUtil,
        ArrayUtil $arrayUtil,
        ModelUtil $modelUtil
    ) {
        $this->bundleConfig = $bundleConfig;
        $this->multilingualFieldsUtil = $multilingualFieldsUtil;
        $this->urlUtil = $urlUtil;
        $this->request = $request;
        $this->stringUtil = $stringUtil;
        $this->dcaUtil = $dcaUtil;
        $this->containerUtil = $containerUtil;
        $this->arrayUtil = $arrayUtil;
        $this->modelUtil = $modelUtil;
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

        if ('tl_content' === $table) {
            $this->addContentLanguageField();
        }
    }

    protected function initAssets()
    {
        if (!$this->containerUtil->isBackend()) {
            return;
        }

        $GLOBALS['TL_CSS']['contao-multilingual-fields-bundle'] = 'bundles/heimrichhannotmultilingualfields/assets/contao-multilingual-fields-bundle.css';
    }

    protected function initConfig($table)
    {
        if (!isset($this->bundleConfig['data_containers'][$table]) || !\is_array($this->bundleConfig['data_containers'])) {
            return;
        }

        $config = $this->bundleConfig['data_containers'][$table];
        $languages = $this->bundleConfig['languages'];

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

            foreach ($languages as $language) {
                $translatedFieldname = $language.'_'.$field;
                $selectorField = $language.'_translate_'.$field;
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
                $fieldDca['eval']['translationSelectorField'] = $selectorField;

                // copy the field
                $dca['fields'][$translatedFieldname] = $fieldDca;

                if (isset($dca['fields'][$translatedFieldname]['eval']['rte'])) {
                    $dca['fields'][$translatedFieldname]['eval']['tl_class'] = 'long clr';
                }

                // add the original fields as readonly
                $readOnlyFields[] = $field;

                // mark as translated
                $dca['fields'][$field]['eval']['isTranslatedField'] = true;

                // put to next line
                $dca['fields'][$field]['eval']['tl_class'] .= ' clr';

                // remove selector behavior
                unset($dca['fields'][$field]['eval']['submitOnChange']);
                $this->arrayUtil->removeValue($field, $dca['palettes']['__selector__']);

                // link the translation fields
                if (!isset($dca['fields'][$field]['eval']['translationConfig'])) {
                    $dca['fields'][$field]['eval']['translationConfig'] = [];
                }

                if (!isset($dca['fields'][$field]['eval']['translationConfig'][$language])) {
                    $dca['fields'][$field]['eval']['translationConfig'][$language] = [];
                }

                $dca['fields'][$field]['eval']['translationConfig'][$language]['field'] = $translatedFieldname;
                $dca['fields'][$field]['eval']['translationConfig'][$language]['selector'] = $selectorField;

                // add the selector
                $dca['fields'][$selectorField] = [
                    'label' => [
                        sprintf($GLOBALS['TL_LANG']['MSC']['multilingualFieldsBundle']['mf_translateField'][0],
                            $GLOBALS['TL_LANG']['LNG'][$language]),
                        $GLOBALS['TL_LANG']['MSC']['multilingualFieldsBundle']['mf_translateField'][1],
                    ],
                    'exclude' => true,
                    'inputType' => 'checkbox',
                    'eval' => [
                        'tl_class' => 'w50 translate-checkbox',
                        'submitOnChange' => true,
                        'translationField' => $translatedFieldname,
                        'translatedField' => $field,
                    ],
                    'sql' => "char(1) NOT NULL default ''",
                ];

                // add "clr" css class to the first field
                if ($isEditMode && 0 === array_search($language, $languages)) {
                    $dca['fields'][$selectorField]['eval']['tl_class'] .= ' clr';
                }

                // add the subpalette
                $dca['palettes']['__selector__'][] = $selectorField;
                $dca['subpalettes'][$selectorField] = $translatedFieldname;

                // add field to palette data
                if (!isset($paletteData[$field])) {
                    $paletteData[$field] = [];
                }

                $paletteData[$field][] = $selectorField;
            }
        }

        // set fields to readonly
        if ($isEditMode) {
            $this->dcaUtil->setFieldsToReadOnly($dca, [
                'fields' => $readOnlyFields,
            ]);

            // skip validation for original fields
            foreach ($readOnlyFields as $readOnlyField) {
                unset($dca['fields'][$readOnlyField]['eval']['mandatory']);
            }
        }

        // add language switch
        $dca['fields']['mf_editLanguages'] = [
            'inputType' => 'hyperlink',
            'eval' => [
                'text' => &$GLOBALS['TL_LANG']['MSC']['multilingualFieldsBundle'][$isEditMode ? 'mf_closeEditLanguages' : 'mf_editLanguages'],
                'linkClass' => 'tl_submit',
                'tl_class' => 'w50 edit-languages',
                'url' => function (DataContainer $dc) use ($isEditMode) {
                    if ($isEditMode) {
                        return $this->urlUtil->removeQueryString([static::EDIT_LANGUAGES_PARAM]);
                    }

                    return $this->urlUtil->addQueryString(static::EDIT_LANGUAGES_PARAM.'=1');
                },
            ],
        ];

        // create onload callback for the palette generation
        $dca['config']['onload_callback'][] = function (DataContainer $dc) use ($isEditMode, $paletteData, $config, &$dca, $table) {
            if (null === ($element = $this->modelUtil->findModelInstanceByPk('tl_content', $dc->id))) {
                return;
            }

            // create palette for editing the fields
            if ($isEditMode) {
                $fixedPalette = '';

                // prepare palette
                $paletteName = $this->dcaUtil->getCurrentPaletteName($table, $dc->id);
                $explodedPalette = $this->explodeByPaletteManipulator($dca['palettes'][$paletteName]);
                $paletteFields = [];

                foreach ($explodedPalette as $fieldData) {
                    $paletteFields = array_merge($paletteFields, $fieldData['fields']);
                }

                $paletteFields = array_unique($paletteFields);

                // prepare sub palettes
                $explodedSubPalettes = [];

                foreach ($dca['subpalettes'] as $selector => $subpalette) {
                    $explodedSubPalettes[$selector] = $this->explodeByPaletteManipulator($subpalette);
                }

                // fix legends
                $fixedPaletteData = [];

                foreach ($paletteData as $originalField => $fields) {
                    $legend = $this->getLegendForField($originalField, $explodedPalette, $explodedSubPalettes);

                    if (false === $legend) {
                        continue;
                    }

                    if (!isset($fixedPaletteData[$legend])) {
                        $fixedPaletteData[$legend] = [];
                    }

                    $fixedPaletteData[$legend][$originalField] = $fields;
                }

                foreach ($fixedPaletteData as $legend => $fieldData) {
                    if (!$this->stringUtil->endsWith($legend, '_legend')) {
                        $legend .= '_legend';
                    }

                    $fixedPalette .= '{'.$legend.'},';

                    foreach ($fieldData as $originalField => $fields) {
                        if (!$this->isFieldInActivePalette($originalField, $explodedPalette) &&
                            !$this->isFieldInSubpalette($originalField, $paletteFields, $explodedSubPalettes)) {
                            continue;
                        }

                        $fixedPalette .= $originalField.',';

                        foreach ($fields as $field) {
                            $fixedPalette .= $field.',';
                        }
                    }

                    $fixedPalette = rtrim($fixedPalette, ',');

                    $fixedPalette .= ';';
                }

                $fixedPalette = 'mf_editLanguages;'.$fixedPalette;
            } else {
                $fixedPalette = ('tl_content' === $table && $this->multilingualFieldsUtil->hasContentLanguageField($element) ? 'mf_language,' : '').
                    'mf_editLanguages;'.$dc->getPalette();
            }

            foreach (array_keys($dca['palettes']) as $palette) {
                if ('__selector__' === $palette) {
                    continue;
                }

                $dca['palettes'][$palette] = $fixedPalette;
            }
        };
    }

    protected function getLegendForField(string $field, array $explodedPalette, array $explodedSubPalettes)
    {
        if (false !== ($legend = $this->findLegendForFieldByPaletteManipulator($explodedPalette, $field))) {
            return $legend;
        }

        foreach ($explodedSubPalettes as $selector => $explodedSubPalette) {
            if (!isset($explodedSubPalette[0]['fields']) || !\is_array($explodedSubPalette[0]['fields'])) {
                continue;
            }

            if (false !== strpos($selector, '_')) {
                $selectorParts = explode('_', $selector);

                if (\in_array($field, $explodedSubPalette[0]['fields'])) {
                    return $this->findLegendForFieldByPaletteManipulator(
                        $explodedPalette,
                        $selectorParts[0]
                    );
                }
            } else {
                if (\in_array($field, $explodedSubPalette[0]['fields'])) {
                    return $this->findLegendForFieldByPaletteManipulator(
                        $explodedPalette,
                        $selector
                    );
                }
            }

            if (\in_array($field, $explodedSubPalette[0]['fields'])) {
                return true;
            }
        }

        return false;
    }

    protected function addContentLanguageField(): void
    {
        if (!$this->multilingualFieldsUtil->hasContentLanguageField()) {
            return;
        }

        $multilingualFieldsUtil = $this->multilingualFieldsUtil;

        $dca = &$GLOBALS['TL_DCA']['tl_content'];

        /*
         * Fields
         */
        $dca['fields']['mf_language'] = [
            'label' => &$GLOBALS['TL_LANG']['MSC']['multilingualFieldsBundle']['mf_language'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'rgxp' => 'locale', 'tl_class' => 'w50'],
            'options_callback' => static function () use ($multilingualFieldsUtil) {
                $languages = \Contao\System::getLanguages();

                foreach ($multilingualFieldsUtil->getLanguages(true) as $language) {
                    $options[$language] = $languages[$language];
                }

                asort($options);

                return $options;
            },
            'sql' => "varchar(5) NOT NULL default ''",
        ];
    }

    protected function explodeByPaletteManipulator(string $palette)
    {
        $pm = new PaletteManipulator();
        $ref = new \ReflectionClass($pm);

        $method = $ref->getMethod('explode');
        $method->setAccessible(true);

        return $method->invoke($pm, $palette);
    }

    protected function findLegendForFieldByPaletteManipulator(array $config, string $field)
    {
        $pm = new PaletteManipulator();
        $ref = new \ReflectionClass($pm);

        $method = $ref->getMethod('findLegendForField');
        $method->setAccessible(true);

        return $method->invoke($pm, $config, $field);
    }

    protected function isFieldInActivePalette(string $field, array $explodedPalette): bool
    {
        foreach ($explodedPalette as $data) {
            if (\in_array($field, $data['fields'])) {
                return true;
            }
        }

        return false;
    }

    protected function isFieldInSubpalette(string $field, array $paletteFields, array $explodedSubPalettes)
    {
        foreach ($explodedSubPalettes as $selector => $explodedSubPalette) {
            if (!isset($explodedSubPalette[0]['fields']) || !\is_array($explodedSubPalette[0]['fields'])) {
                continue;
            }

            if (false !== strpos($selector, '_')) {
                $selectorParts = explode('_', $selector);

                if (!\in_array($selectorParts[0], $paletteFields)) {
                    continue;
                }
            } else {
                if (!\in_array($selector, $paletteFields)) {
                    continue;
                }
            }

            if (\in_array($field, $explodedSubPalette[0]['fields'])) {
                return true;
            }
        }

        return false;
    }
}
