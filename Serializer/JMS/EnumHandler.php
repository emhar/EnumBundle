<?php

namespace Fervo\EnumBundle\Serializer\JMS;

use Fervo\EnumBundle\Enum\AbstractTranslatableEnum;
use JMS\Serializer\Context;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\VisitorInterface;
use MyCLabs\Enum\Enum;
use Symfony\Component\Translation\TranslatorInterface;

class EnumHandler
{
    /**
     * @var TranslatorInterface
     */
    public $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param VisitorInterface $visitor
     * @param $data
     * @param array $type
     * @param Context $context
     * @return mixed|null
     * @throws \UnexpectedValueException
     * @throws \JMS\Serializer\Exception\RuntimeException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function serializeEnumToJson(VisitorInterface $visitor, $data, array $type, Context $context)
    {
        if (((int)$context->getDirection()) === GraphNavigator::DIRECTION_SERIALIZATION) {
            if (!$data instanceof Enum) {
                throw new \UnexpectedValueException(sprintf('%s is not a valid enum', $data));
            }
            if ($data instanceof AbstractTranslatableEnum) {
                return $this->translator->trans($data->getTranslationKey(), array(), 'enums');
            }
            return $data->getValue();
        }
        if (((int)$context->getDirection()) === GraphNavigator::DIRECTION_DESERIALIZATION) {
            $enumClass = $type['name'];

            if (null === $data) {
                return null;
            }
            $values = $enumClass::toArray();
            /* @var $values array */
            foreach ($values as $constant => $constantValue) {
                //Allows both translation and constant key
                try {
                    $enum = $enumClass::$constant();
                    if ($enum instanceof AbstractTranslatableEnum
                        && $this->translator->trans($enum->getTranslationKey(), array(), 'enums') === ((string)$data)
                    ) {
                        return $enum;
                    }
                } catch (\UnexpectedValueException  $e) {

                }
                if ($data === $constantValue) {
                    return $enumClass::$constant();
                }
            }

            throw new RuntimeException(sprintf('%s is not a valid %s value', $data, $enumClass));
        }

        throw new \UnexpectedValueException('Invalid direction');
    }
}
