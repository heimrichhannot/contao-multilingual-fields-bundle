<?php

namespace HeimrichHannot\MultilingualFieldsBundle\Twig\Runtime;

use Contao\Model;
use HeimrichHannot\MultilingualFieldsBundle\Multilingual\TableBuilder;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class MultilingualFieldsRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly TableBuilder $tableBuilder,
        private readonly RequestStack $requestStack,
        private readonly Utils $utils,
    ) {
    }

    public function mfFieldValue(string $field, Model|array $entity, string $fallback = '', ?string $language = null): mixed
    {
        $entity = $this->fetchModel($entity);
        if (!$entity) {
            return $fallback;
        }

        $mfTable = $this->tableBuilder->buildTableFor($entity::getTable());

        if (!$mfTable || !$mfTable->conditionMet($entity->id)) {
            return $entity->{$field} ?? $fallback;
        }

        $mfField = $mfTable->getField($field);
        if (!$mfField) {
            return $entity->{$field} ?? $fallback;
        }

        $language = $language ?? $this->requestStack->getCurrentRequest()?->getLocale();

        if (!$language) {
            return $entity->{$field} ?? $fallback;
        }

        return $mfField->valueFor($entity->row(), $language) ?? $fallback;
    }

    private function fetchModel(mixed $entity): ?Model
    {
        if ($entity instanceof Model) {
            return $entity;
        }

        $table = $entity[0] ?? null;
        $id = $entity[1] ?? null;

        if (!\is_string($table) || !\is_numeric($id)) {
            throw new \InvalidArgumentException('Invalid entity format. Expected [table, id].');
        }

        return $this->utils->model()->findModelInstanceByPk($table, (int) $id);

    }
}