<?php

namespace Fervo\EnumBundle\Serializer\JMS;

use Fervo\EnumBundle\Serializer\AbstractEnumNormalizer;
use JMS\Serializer\Context;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\VisitorInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

class EnumHandler extends AbstractEnumNormalizer
{
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
            try {
                return $this->normalizeEnum($data);
            } catch (InvalidArgumentException $e) {
                throw new \UnexpectedValueException($e->getMessage());
            }
        }
        if (((int)$context->getDirection()) === GraphNavigator::DIRECTION_DESERIALIZATION) {

            try {
                return $this->denormalizeEnum($data, $type['name']);
            } catch (UnexpectedValueException $e) {
                throw new RuntimeException($e->getMessage());
            }
        }

        throw new \UnexpectedValueException('Invalid direction');
    }
}
