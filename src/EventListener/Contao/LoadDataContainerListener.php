<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener\Contao;

use Contao\CoreBundle\Slug\Slug;
use Contao\StringUtil;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Intl\Locales;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Database;
use Contao\DataContainer;
use HeimrichHannot\MultilingualFieldsBundle\EventListener\DataContainer\LanguageEditSwitchButtonCallback;
use HeimrichHannot\MultilingualFieldsBundle\Util\DcaUtil;
use HeimrichHannot\MultilingualFieldsBundle\Util\MultilingualFieldsUtil;
use HeimrichHannot\UtilsBundle\StaticUtil\SUtils;
use Symfony\Component\HttpFoundation\RequestStack;

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
    protected MultilingualFieldsUtil $multilingualFieldsUtil;
    private RequestStack $requestStack;
    private Locales $locales;

    public function __construct(
        array $bundleConfig,
        MultilingualFieldsUtil $multilingualFieldsUtil,
        RequestStack $requestStack,
        Locales $locales,
        private readonly Slug $slug,
        private readonly DcaUtil $dcaUtil,
    )
    {
        $this->bundleConfig = $bundleConfig;
        $this->multilingualFieldsUtil = $multilingualFieldsUtil;
        $this->requestStack = $requestStack;
        $this->locales = $locales;
    }

    public function __invoke($table): void
    {
        // only run once
        if (\in_array($table, static::$processedTables)) {
            return;
        }

        static::$processedTables[] = $table;

        $this->initConfig($table);

        if ('tl_content' === $table) {
            $this->addContentLanguageField();
        }
    }

    protected function initConfig($table)
    {
        if (!isset($this->bundleConfig['data_containers'][$table]) || !\is_array($this->bundleConfig['data_containers'])) {
            return;
        }

        $config = $this->bundleConfig['data_containers'][$table];
        $languages = $this->bundleConfig['languages'];
        $request = $this->requestStack->getCurrentRequest();

        $dca = &$GLOBALS['TL_DCA'][$table];

        $isEditMode = $request ? $request->query->get(static::EDIT_LANGUAGES_PARAM, false) : false;

        // add translated fields
        $paletteData = [];
        $readOnlyFields = [];

        foreach ($config['fields'] as $fieldConfig) {
            $field = $fieldConfig['name'];

            if (!isset($dca['fields'][$field])) {
                continue;
            }

            foreach ($languages as $language) {
                $translatedFieldname = $language . '_' . $field;
                $selectorField = $language . '_translate_' . $field;
                $fieldDca = $dca['fields'][$field];

                // adjust the label
                if (isset($dca['fields'][$field]['label'])) {
                    $label = $dca['fields'][$field]['label'];
                } else {
                    $label = $GLOBALS['TL_LANG'][$table][$field];
                }

                $translatedLabel[0] = ((string)$label[0]) . ' (' . $GLOBALS['TL_LANG']['LNG'][$language] . ')';
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

                $this->handleAliasField($dca, $fieldConfig, $translatedFieldname, $language);

                // add the original fields as readonly
                $readOnlyFields[] = $field;

                // mark as translated
                $dca['fields'][$field]['eval']['isTranslatedField'] = true;

                if ($isEditMode) {
                    // put to next line
                    $dca['fields'][$field]['eval']['tl_class'] .= ' clr';

                    unset($dca['fields'][$translatedFieldname]['eval']['submitOnChange']);
                }

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
        $langId = $isEditMode ? 'MSC.multilingualFieldsBundle.mf_closeEditLanguages' : 'MSC.multilingualFieldsBundle.mf_editLanguages';
        $dca['fields']['mf_editLanguages'] = [
            'inputType' => 'mf_editLanguages',
            'input_field_callback' => [LanguageEditSwitchButtonCallback::class, '__invoke'],
        ];

        // create onload callback for the palette generation
        $dca['config']['onload_callback'][] = function (DataContainer $dc = null) use ($isEditMode, $paletteData, $config, $table, &$dca) {
            if (null === $dc || !$dc->id) {
                return;
            }

            // check sql condition
            if (isset($config['sql_condition'])) {
                $sqlCondition = $config['sql_condition'];
                $sqlConditionValues = $config['sql_condition_values'] ?? [];

                $values = array_merge([$dc->id], $sqlConditionValues);

                $check = Database::getInstance()->prepare("SELECT id FROM $table WHERE id=? AND $sqlCondition")->limit(1);

                $check = \call_user_func_array([$check, 'execute'], $values);

                if ($check->numRows < 1) {
                    return;
                }
            }



            $paletteName = $dc->getPalette() ?: 'default';

            // create palette for editing the fields
            if ($isEditMode) {
                $paletteManipulator = PaletteManipulator::create();

                $translatableFields = $this->multilingualFieldsUtil->getTranslatableFields($table);

                // remove untranslatable fields
                foreach ($dca['fields'] as $field => $data) {
                    if (!\in_array($field, $translatableFields) && !isset($data['eval']['translationConfig']) && !isset($data['eval']['translatedField'])) {
                        $paletteManipulator->removeField($field);
                    }
                }

                foreach ($paletteData as $originalField => $fields) {
                    if (!\in_array($originalField, StringUtil::trimsplit('[;,]', $dc->getPalette()))) {
                        if (!$this->dcaUtil->isSubPaletteField($originalField, $table)) {
                            $paletteManipulator->removeField($originalField);

                            continue;
                        }
                        // sub palette field and selector in palette?
                        if (!($selector = $this->dcaUtil->getSubPaletteFieldSelector($originalField, $table)) ||
                            !\in_array($selector, StringUtil::trimsplit('[;,]', $dc->getPalette()))
                        ) {
                            $paletteManipulator->removeField($originalField);

                            continue;
                        }

                        // add the sub palette field as an ordinary field
                        if (isset($paletteData[$selector]) && \is_array($paletteData[$selector])) {
                            $selector = $paletteData[$selector][\count($paletteData[$selector]) - 1];
                        }

                        $paletteManipulator->addField($originalField, $selector);
                    }

                    $lastInsertedField = $originalField;

                    foreach ($fields as $field) {
                        $paletteManipulator->addField($field, $lastInsertedField);

                        $lastInsertedField = $field;
                    }

                    // remove selector behavior
                    unset($dca['fields'][$originalField]['eval']['submitOnChange']);
                    SUtils::array()::removeValue($originalField, $dca['palettes']['__selector__']);
                }

                $paletteManipulator->applyToPalette($paletteName, $table);

                $dca['palettes'][$paletteName] = 'mf_editLanguages;' . $dca['palettes'][$paletteName];
            } else {
                $pm = PaletteManipulator::create();
                if ('tl_content' === $table && $this->multilingualFieldsUtil->hasContentLanguageField($dc->id)) {
                    $pm->addField('mf_language', null, PaletteManipulator::POSITION_BEFORE);
                }
                $pm->addField('mf_editLanguages', null, PaletteManipulator::POSITION_BEFORE);
                $pm->applyToPalette($paletteName, $table);
            }
        };
    }

    protected function addContentLanguageField(): void
    {
        if (!$this->multilingualFieldsUtil->hasContentLanguageField()) {
            return;
        }

        $multilingualFieldsUtil = $this->multilingualFieldsUtil;
        $locales = $this->locales;

        $dca = &$GLOBALS['TL_DCA']['tl_content'];
        $dca['fields']['mf_language'] = [
            'label' => &$GLOBALS['TL_LANG']['MSC']['multilingualFieldsBundle']['mf_language'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'rgxp' => 'locale', 'tl_class' => 'w50'],
            'options_callback' => static function () use ($multilingualFieldsUtil, $locales) {
                $languages = $locales->getLocales(null, true);
                $options = [];

                foreach ($multilingualFieldsUtil->getLanguages(true) as $language) {
                    $options[$language] = $languages[$language];
                }

                asort($options);

                return $options;
            },
            'sql' => "varchar(5) NOT NULL default ''",
        ];
    }

    private function handleAliasField(array &$dca, array $fieldConfig, string $translatedFieldName, string $language): void
    {
        if (!($fieldConfig['is_alias_field'] ?? false) || empty($fieldConfig['alias_base_field'])) {
            return;
        }

        $aliasBaseField = $fieldConfig['alias_base_field'];
        $slug = $this->slug;

        $dca['fields'][$translatedFieldName]['save_callback'] = [
            function ($value, DataContainer $dc) use ($translatedFieldName, $language, $aliasBaseField, $slug) {

                $baseFieldValue = $dc->activeRecord->{$language . '_translate_' . $aliasBaseField}
                    ? $dc->activeRecord->{$language . '_' . $aliasBaseField}
                    : $dc->activeRecord->{$aliasBaseField};

                $aliasExists = (static fn(string $alias): bool => Database::getInstance()
                        ->prepare("SELECT id FROM $dc->table WHERE $translatedFieldName=? AND id!=?")
                        ->execute($alias, $dc->id)
                        ->numRows > 0);

                // Generate an alias if there is none
                if (!$value) {
                    $value = $slug->generate($baseFieldValue, [], $aliasExists);
                } elseif (preg_match('/^[1-9]\d*$/', (string)$value)) {
                    throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $value));
                } elseif ($aliasExists($value)) {
                    throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
                }

                return $value;
            }
        ];
    }
}
