<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Slug\Slug;
use Contao\CoreBundle\Intl\Locales;
use Contao\Database;
use Contao\DataContainer;
use HeimrichHannot\MultilingualFieldsBundle\EventListener\DataContainer\ConfigOnPaletteListener;
use HeimrichHannot\MultilingualFieldsBundle\EventListener\DataContainer\LanguageEditSwitchButtonCallback;
use HeimrichHannot\MultilingualFieldsBundle\Util\DcaUtil;
use HeimrichHannot\MultilingualFieldsBundle\Util\MultilingualFieldsUtil;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsHook('loadDataContainer', priority: -256)]
class LoadDataContainerListener
{
    public const EDIT_LANGUAGES_PARAM = 'edit_languages';

    protected static $processedTables = [];

    public function __construct(
        private array                    $bundleConfig,
        protected MultilingualFieldsUtil $multilingualFieldsUtil,
        private readonly RequestStack    $requestStack,
        private readonly Locales         $locales,
        private readonly Slug            $slug,
        private readonly DcaUtil         $dcaUtil,
    )
    {
        $this->bundleConfig = $bundleConfig;
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

        $isEditMode = $request && $request->query->get(static::EDIT_LANGUAGES_PARAM, false);

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
                $languageName = $this->locales->getLocales(null)[$language] ?? $language;



                // adjust the label
                if (isset($dca['fields'][$field]['label'])) {
                    $label = $dca['fields'][$field]['label'];
                } else {
                    $label = $GLOBALS['TL_LANG'][$table][$field];
                }

                $translatedLabel[0] = ((string)$label[0]) . ' (' . $languageName . ')';
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
                        sprintf(
                            $GLOBALS['TL_LANG']['MSC']['multilingualFieldsBundle']['mf_translateField'][0],
                            $languageName
                        ),
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
            'inputType' => 'mf_editLanguages',
            'input_field_callback' => [LanguageEditSwitchButtonCallback::class, '__invoke'],
        ];

        $dca['config']['onpalette_callback'][] = [ConfigOnPaletteListener::class, '__invoke'];
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
