<?php

namespace HeimrichHannot\MultilingualFieldsBundle\Multilingual;

use Contao\Model;

class MultilingualField
{
    public readonly string $fieldname;

    public function __construct(
        private readonly array $fieldConfig,
        private readonly string $fallbackLanguage = 'en',
    ) {
        $this->fieldname = $fieldConfig['name'];
    }

    public function getFieldNameFor(string $language): string
    {
        if ($language === $this->fallbackLanguage) {
            return $this->fieldname;
        }

        return $language . '_' . $this->fieldname;
    }

    public function getSelectorFieldNameFor(string $language): string
    {
        return $language . '_translate_' . $this->fieldname;
    }

    public function valueFor(array|Model $row, ?string $language = null, mixed $fallback = null): mixed
    {
        if ($row instanceof Model) {
            $row = $row->row();
        }

        if (null === $language) {
            $language = $this->fallbackLanguage;
        }

        if ($language !== $this->fallbackLanguage) {
            $selectorField = $this->getSelectorFieldNameFor($language);
            $fieldName = $this->getFieldNameFor($language);
            if (!empty($row[$selectorField]) && isset($row[$fieldName])) {
                return $row[$fieldName];
            }
        }

        return $fallback ?: $row[$this->fieldname] ?? null;
    }
}
