<?php

namespace HeimrichHannot\MultilingualFieldsBundle\Multilingual;

use HeimrichHannot\MultilingualFieldsBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TableBuilder
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
    )
    {
    }

    public function buildTableFor(string $table): ?MultilingualTable
    {
        if (!$this->parameterBag->has(Configuration::ROOT_ID)) {
            return null;
        }

        $bundleConfig = $this->parameterBag->get(Configuration::ROOT_ID);
        if (empty($bundleConfig['data_containers'][$table]) || !\is_array($bundleConfig['data_containers'][$table])) {
            return null;
        }

        $dca = &$GLOBALS['TL_DCA'][$table];

        $fields = [];
        foreach ($bundleConfig['data_containers'][$table]['fields'] as $fieldConfig) {
            $field = new MultilingualField($fieldConfig);
            $fields[$field->fieldname] = $field;
        }

        if (empty($fields)) {
            return null;
        }

        return new MultilingualTable(
            $fields,
            $bundleConfig['languages'] ?? [],
        );
    }
}