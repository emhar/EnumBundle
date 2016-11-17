<?php

namespace Fervo\EnumBundle\Doctrine;

use AppBundle\Enum\CommentStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Types\Type;

class EnumArrayType extends Type
{
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value == null) {
            return null;
        }

        $data = json_decode($value, true);

        if (count($data['values']) == 0) {
            return [];
        }

        $enumClass = $data['class'];
        $values = array_map(function($enumValue) use ($enumClass) {
            return new $enumClass($enumValue);
        }, $data['values']);

        return $values;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value == null) {
            return null;
        }

        if ($value == []) {
            return json_encode(['values' => []]);
        }

        $struct = [
            'class' => get_class(array_values($value)[0]),
            'values' => array_map(function($enumInstance) {
                return $enumInstance->getValue();
            }, $value),
        ];

        return json_encode($struct);
    }

    public function getName()
    {
        return 'enumarray';
    }
}
