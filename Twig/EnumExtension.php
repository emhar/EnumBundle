<?php

namespace Fervo\EnumBundle\Twig;

use MyCLabs\Enum\Enum;
use Symfony\Component\Translation\TranslatorInterface;

/**
 *
 */
class EnumExtension extends \Twig_Extension
{
    protected $translator;
    protected $enumMap;

    public function __construct(TranslatorInterface $translator, array $enumMap)
    {
        $this->translator = $translator;
        $this->enumMap = $enumMap;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'enum';
    }

    /**
     * {@inheritDoc}
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('enum', [$this, 'isEnum']),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('enum_trans', [$this, 'getEnumTranslation']),
        ];
    }

    public function isEnum(Enum $left, $rightEnumClass, $rightEnumConst)
    {
        return $left == new $rightEnumClass(constant("$rightEnumClass::$rightEnumConst"));
    }

    public function getEnumTranslation(Enum $enum)
    {
        if (isset($this->enumMap[get_class($enum)])) {
            $enumType = $this->enumMap[get_class($enum)];
            return $this->translator->trans(sprintf('%s.%s', $enumType, $enum->getValue()), [], 'enums');
        }

        return $enum->getValue();
    }
}
