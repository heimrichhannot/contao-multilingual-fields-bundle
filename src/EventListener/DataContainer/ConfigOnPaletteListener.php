<?php

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use Contao\StringUtil;
use HeimrichHannot\MultilingualFieldsBundle\EventListener\Contao\LoadDataContainerListener;
use HeimrichHannot\MultilingualFieldsBundle\Multilingual\TableBuilder;
use HeimrichHannot\MultilingualFieldsBundle\Util\MultilingualFieldsUtil;
use Symfony\Component\HttpFoundation\RequestStack;

class ConfigOnPaletteListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly MultilingualFieldsUtil $fieldsUtil,
        private readonly TableBuilder $tableBuilder,
    ) {
    }

    public function __invoke(string $palette, DataContainer $dc): string
    {
        $isEditMode = (bool) $this->requestStack->getCurrentRequest()
            ?->query->get(LoadDataContainerListener::EDIT_LANGUAGES_PARAM, false) ?? false;

        return match ($isEditMode) {
            true => $this->buildEditPalette($palette, $dc),
            false => $this->addEditFields($palette, $dc),
        };
    }

    private function addEditFields(string $palette, DataContainer $dc): string
    {
        $prependPalette = 'mf_editLanguages;';

        if ('tl_content' === $dc->table && $this->fieldsUtil->hasContentLanguageField($dc->id)) {
            $prependPalette = 'mf_language,' . $prependPalette;
        }

        return $prependPalette . $palette;
    }

    private function buildEditPalette(string $originalPalette, DataContainer $dc): string
    {
        $mlTable = $this->tableBuilder->buildTableFor($dc->table);
        if (!$mlTable) {
            return $originalPalette;
        }

        $paletteFields = StringUtil::trimsplit('[;,]', $originalPalette);
        $paletteManipulator = PaletteManipulator::create();

        foreach ($paletteFields as $paletteField) {
            if (str_starts_with($paletteField, '{')) {
                continue;
            }
            $mlField = $mlTable->getField($paletteField);
            if (!$mlField) {
                $paletteManipulator->removeField($paletteField);
                continue;
            }

            foreach ($mlTable->languages as $language) {
                $selectorFieldName = $mlField->getSelectorFieldNameFor($language);
                $paletteManipulator->addField($selectorFieldName, $paletteField);
                if ($dc->getCurrentRecord()[$selectorFieldName] ?? false) {
                    $paletteManipulator->addField($mlField->getFieldNameFor($language), $selectorFieldName);
                }
            }
            $paletteManipulator->removeField($paletteField);
        }

        $palette = $paletteManipulator->applyToString($originalPalette);

        return 'mf_editLanguages;' . $palette;
    }
}
