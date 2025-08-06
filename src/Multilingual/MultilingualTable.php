<?php

namespace HeimrichHannot\MultilingualFieldsBundle\Multilingual;

use Contao\Database;

class MultilingualTable
{
    /**
     * @internal Use TableBuilder to create instances of this class.
     */
    public function __construct(
        public readonly string $table,
        /**
         * @var MultilingualField[]
         */
        public readonly array $fields = [],
        private readonly array $config = [],
        public readonly array $languages = [],
        public readonly string $fallbackLanguage = 'en',
    ) {
    }

    public function getField(string $field): ?MultilingualField
    {
        return $this->fields[$field] ?? null;
    }

    public function conditionMet(int $id): bool
    {
        if (empty($this->config['sql_condition'])) {
            return true;
        }

        $sqlCondition = $this->config['sql_condition'];
        $sqlConditionValues = $this->config['sql_condition_values'] ?? [];

        $values = array_merge([$id], $sqlConditionValues);

        $query = "SELECT id FROM {$this->table} WHERE id=? AND $sqlCondition";

        $result = Database::getInstance()
            ->prepare($query)
            ->limit(1)
            ->execute(...$values);
        ;

        return $result->numRows > 0;
    }
}
