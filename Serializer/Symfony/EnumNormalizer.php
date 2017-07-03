<?php

namespace Fervo\EnumBundle\Serializer\Symfony;

use Fervo\EnumBundle\Enum\AbstractTranslatableEnum;
use MyCLabs\Enum\Enum;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * {@inheritDoc}
 */
class EnumNormalizer implements NormalizerInterface, DenormalizerInterface
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
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (!$object instanceof Enum) {
            throw new InvalidArgumentException('The object must extends "\MyCLabs\Enum\Enum".');
        }

        if ($object instanceof AbstractTranslatableEnum) {
            return $this->translator->trans($object->getTranslationKey(), array(), 'enums');
        }

        return $object->getValue();
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

        if (null === $data) {
            return null;
        }
        $values = $class::toArray();
        /* @var $values array */
        $allowedValues = array();
        foreach ($values as $constant => $constantValue) {
            //Allows both translation and constant key
            try {
                $enum = $class::$constant();
                if ($enum instanceof AbstractTranslatableEnum) {
                    $value =
                    if ($this->translator->trans($enum->getTranslationKey(), array(), 'enums') === ((string)$data)) {
                        return $enum;
                    }
                    $allowedValues[] = $value;
                } else {
                    $allowedValues[] = $constantValue;
                }
                if ($data === $constantValue) {
                    return $class::$constant();
                }
            } catch (\UnexpectedValueException  $e) {

            }

        }

        throw new UnexpectedValueException($this->translator->trans(
            '%value% is not a valid value. Allowed values: %allowed_values%',
            array('%value%' => $data, '%allowed_values%' => implode(',', $allowedValues))),
            'validators'
        );
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
