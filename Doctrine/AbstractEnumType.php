<?php

namespace Fervo\EnumBundle\Doctrine;

use AppBundle\Enum\CommentStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * {@inheritDoc}
 */
abstract class AbstractEnumType extends Type
{
    /**
     * {@inheritDoc}
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value == null) {
            return null;
        }

        $enumClass = $this->getEnumClass();

        return new $enumClass($value);
    }

    abstract public function getEnumClass();

    /**
     * {@inheritDoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value == null) {
            return null;
        }

        return $value->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
