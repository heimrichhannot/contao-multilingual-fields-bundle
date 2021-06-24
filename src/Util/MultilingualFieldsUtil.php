<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle\Util;

use Contao\Controller;
use Contao\Model;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Model\Collection;

class MultilingualFieldsUtil
{
    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var ModelUtil
     */
    protected $modelUtil;

    public function __construct(array $bundleConfig, ModelUtil $modelUtil)
    {
        $this->bundleConfig = $bundleConfig;
        $this->modelUtil = $modelUtil;
    }

    public function isTranslatable(string $table)
    {
        return isset($this->bundleConfig['data_containers'][$table]);
    }

    public function getTranslatableFields(string $table)
    {
        if (!isset($this->bundleConfig['data_containers'][$table]['fields'])) {
            return false;
        }

        return array_map(function ($row) {
            return $row['name'];
        }, $this->bundleConfig['data_containers'][$table]['fields']);
    }

    public function translateModel(string $table, Model $model, string $language = ''): ?Model
    {
        if (false === ($translatableFields = $this->getTranslatableFields($table))) {
            return $model;
        }

        $language = $language ?: $GLOBALS['TL_LANGUAGE'];

        foreach ($translatableFields as $field) {
            if (!$model->{$language.'_translate_'.$field}) {
                continue;
            }

            $model->{$field} = $model->{$language.'_'.$field};
        }

        return $model;
    }

    /**
     * @param string $table The translated table
     */
    public function translateModels(string $table, Collection $models, string $language = ''): array
    {
        $result = [];

        foreach ($models as $model) {
            $result[] = $this->translateModel($table, $model, $language);
        }

        return $result;
    }

    public function getRenderedMultilingualContentElements(Collection $models): string
    {
        return implode('', array_map(function ($element) {
            return Controller::getContentElement($element);
        }, $this->translateModels('tl_content', $models)));
    }

    public function hasContentLanguageField($element = null): bool
    {
        if (!isset($this->bundleConfig['content_language_select']['enabled']) || true !== $this->bundleConfig['content_language_select']['enabled']) {
            return false;
        }

        if (null !== ($element = $this->modelUtil->findModelInstanceByPk('tl_content', $element))) {
            if (!isset($this->bundleConfig['content_language_select']['types']) || !\is_array($this->bundleConfig['content_language_select']['types']) ||
                empty($this->bundleConfig['content_language_select']['types'])) {
                return true;
            }

            if (!\in_array($element->type, $this->bundleConfig['content_language_select']['types'])) {
                return false;
            }
        }

        return true;
    }

    public function getLanguages(bool $includeFallbackLanguage = false): array
    {
        $languages = [];

        if ($includeFallbackLanguage) {
            $languages[] = $this->bundleConfig['fallback_language'];
        }

        if (isset($this->bundleConfig['languages']) && \is_array($this->bundleConfig['languages'])) {
            $languages = array_merge($languages, $this->bundleConfig['languages']);
        }

        return $languages;
    }
}
