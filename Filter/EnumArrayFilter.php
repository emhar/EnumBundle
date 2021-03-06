<?php

namespace Fervo\EnumBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\QueryBuilder;
use Fervo\EnumBundle\Enum\AbstractTranslatableEnum;
use MyCLabs\Enum\Enum;

/**
 * {@inheritDoc}
 */
class EnumArrayFilter extends AbstractFilter
{
    /**
     * {@inheritDoc}
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];
        foreach ($this->properties as $property => $enumClass) {
            if (!is_subclass_of($enumClass, AbstractTranslatableEnum::class)) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid translatable enums class for filter "%s", on resource %s ',
                    $property,
                    $resourceClass
                ));
            }
            /* @var $enumClass AbstractTranslatableEnum|string */
            $description[$property] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => ['description' => sprintf(
                    'Filter on "%s", allowed values: ["%s"]', $property, implode('","', $enumClass::values())
                )],
            ];
            $description[$property . '[]'] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => ['description' => sprintf(
                    'Filter on "%s", allowed values: ["%s"]', $property, implode('","', $enumClass::values())
                )],
            ];
        }

        return $description;
    }

    /**
     * {@inheritDoc}
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (!isset($this->properties[$property])) {
            return;
        }
        $enumClass = $this->properties[$property];
        if (!is_subclass_of($enumClass, Enum::class)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid enums class for filter "%s", on resource %s ',
                $property,
                $resourceClass
            ));
        }
        $enums = array();
        /* @var $enumClass Enum|string */
        foreach ($enumClass::values() as $enum) {
            /* @var $enum Enum */
            $enums[(string)$enum] = $enum;
        }

        $values = array();
        foreach ((array)$value as $item) {
            if (!array_key_exists($item, $enums)) {
                $this->logger->notice('Invalid filter ignored', [
                    'exception' => new InvalidArgumentException(sprintf('Invalid value for "[%s]", expected an enum value', $item)),
                ]);
                continue;
            }
            $values[] = $enums[$item];
        }
        $parameterName = $queryNameGenerator->generateParameterName($property); // Generate a unique parameter name to avoid collisions with other filters
        $expressions = array();
        foreach ($values as $key => $enum) {
            $expressions[] = $queryBuilder->expr()->like('o.' . $property, ':' . $parameterName . $key);
            $queryBuilder->setParameter($parameterName . $key, '%\\"' . $enum . '\\"%');
        }
        $queryBuilder->andWhere($queryBuilder->expr()->orX(...$expressions));
    }
}