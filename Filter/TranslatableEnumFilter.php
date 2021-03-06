<?php

namespace Fervo\EnumBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\QueryBuilder;
use Fervo\EnumBundle\Enum\AbstractTranslatableEnum;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * {@inheritDoc}
 */
class TranslatableEnumFilter extends AbstractFilter
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param RequestStack $requestStack
     * @param LoggerInterface|null $logger
     * @param array|null $properties
     * @param TranslatorInterface $translator
     */
    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack, LoggerInterface $logger = null, array $properties = null, TranslatorInterface $translator)
    {
        parent::__construct($managerRegistry, $requestStack, $logger, $properties);
        $this->translator = $translator;
    }

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
            $values = array();
            /* @var $enumClass AbstractTranslatableEnum|string */
            foreach ($enumClass::values() as $enum) {
                /* @var $enum AbstractTranslatableEnum */
                $values[] = $this->translator->trans($enum->getTranslationKey(), [], 'enums');
            }
            $description[$property] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => ['description' => sprintf(
                    'Filter on "%s", allowed values: ["%s"]', $property, implode('","', $values)
                )],
            ];
            $description[$property . '[]'] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => ['description' => sprintf(
                    'Filter on "%s", allowed values: ["%s"]', $property, implode('","', $values)
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
        if (!is_subclass_of($enumClass, AbstractTranslatableEnum::class)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid translatable enums class for filter "%s", on resource %s ',
                $property,
                $resourceClass
            ));
        }
        $enums = array();
        /* @var $enumClass AbstractTranslatableEnum|string */
        foreach ($enumClass::values() as $enum) {
            /* @var $enum AbstractTranslatableEnum */
            $enums[$this->translator->trans($enum->getTranslationKey(), [], 'enums')] = $enum;
        }

        $values = array();
        foreach ((array)$value as $item) {
            if (!array_key_exists($item, $enums)) {
                $this->logger->notice('Invalid filter ignored', [
                    'exception' => new InvalidArgumentException(sprintf('Invalid value for "[%s]", expected a translated enum value', $item)),
                ]);
                continue;
            }
            $values[] = $enums[$item];
        }
        $parameterName = $queryNameGenerator->generateParameterName($property); // Generate a unique parameter name to avoid collisions with other filters
        $expressions = array();
        foreach ($values as $key => $enum) {
            $expressions[] = $queryBuilder->expr()->eq('o.' . $property, ':' . $parameterName . $key);
            $queryBuilder->setParameter($parameterName . $key, $enum);
        }
        $queryBuilder->andWhere($queryBuilder->expr()->orX(...$expressions));
    }
}