<?php

namespace Fervo\EnumBundle\Serializer\Symfony;

use Fervo\EnumBundle\Serializer\AbstractEnumNormalizer;
use MyCLabs\Enum\Enum;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * {@inheritDoc}
 */
class EnumNormalizer extends AbstractEnumNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $this->normalizeEnum($object);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Enum;
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return $this->denormalizeEnum($data, $class);
    }

    /**
     * {@inheritdoc}
     * @throws \ReflectionException
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, Enum::class);
    }
}
