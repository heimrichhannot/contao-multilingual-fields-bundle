<?php

namespace HeimrichHannot\MultilingualFieldsBundle\Multilingual;

class MultilingualTable
{
    public function __construct(
        private readonly array $fields = [],
        public readonly array $languages = [],
    ) {
    }

    /**
     * @return MultilingualField[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getField(string $field): ?MultilingualField
    {
        return $this->fields[$field] ?? null;
    }
}
