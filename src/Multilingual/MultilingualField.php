<?php

namespace HeimrichHannot\MultilingualFieldsBundle\Multilingual;

class MultilingualField
{
    public readonly string $fieldname;

    public function __construct(
        private readonly array $fieldConfig,
    )
    {
        $this->fieldname = $fieldConfig['name'];
    }

    public function getFieldNameFor(string $language): string
    {
        return $language . '_' . $this->fieldname;
    }

    public function getSelectorFieldNameFor(string $language): string
    {
        return $language . '_translate_' . $this->fieldname;

    }
}