<?php

namespace HeimrichHannot\MultilingualFieldsBundle\Multilingual;

use HeimrichHannot\MultilingualFieldsBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TableBuilder
{
    private array $tableCache = [];

    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function buildTableFor(string $table): ?MultilingualTable
    {
        if (isset($this->tableCache[$table])) {
            return $this->tableCache[$table];
        }

        if (!$this->parameterBag->has(Configuration::ROOT_ID)) {
            return null;
        }

        $bundleConfig = $this->parameterBag->get(Configuration::ROOT_ID);
        if (empty($bundleConfig['data_containers'][$table]) || !\is_array($bundleConfig['data_containers'][$table])) {
            return null;
        }

        $fields = [];
        foreach ($bundleConfig['data_containers'][$table]['fields'] as $fieldConfig) {
            $field = new MultilingualField($fieldConfig);
            $fields[$field->fieldname] = $field;
        }

        if (empty($fields)) {
            return null;
        }

        $mlTable = new MultilingualTable(
            $table,
            $fields,
            $bundleConfig['data_containers'][$table],
            $bundleConfig['languages'] ?? [],
        );

        $this->tableCache[$table] = $mlTable;

        return $mlTable;
    }
}
