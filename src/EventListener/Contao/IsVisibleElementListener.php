<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener\Contao;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Model;
use HeimrichHannot\MultilingualFieldsBundle\Multilingual\TableBuilder;
use HeimrichHannot\MultilingualFieldsBundle\Util\MultilingualFieldsUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsHook('isVisibleElement')]
class IsVisibleElementListener
{
    public function __construct(
        protected MultilingualFieldsUtil $multilingualFieldsUtil,
        private readonly Utils $utils,
        private readonly RequestStack $requestStack,
        private readonly TableBuilder $tableBuilder,
    ) {
    }

    public function __invoke(Model $element, bool $isVisible): bool
    {
        if ($this->utils->container()->isBackend() || !($element instanceof ContentModel)) {
            return $isVisible;
        }

        $language = $this->requestStack->getCurrentRequest()?->getLocale() ?: 'en';

        if ($this->multilingualFieldsUtil->hasContentLanguageField($element->id)
            && $element->mf_language
            && $element->mf_language !== $language
        ) {
            return false;
        }

        $mfTable = $this->tableBuilder->buildTableFor($element::getTable());
        if (!$mfTable) {
            return $isVisible;
        }

        foreach ($mfTable->fields as $field) {
            $element->{$field->fieldname} = $field->valueFor($element, $language);
        }

        return $isVisible;
    }
}
