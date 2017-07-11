<?php

namespace Fervo\EnumBundle\Serializer;

use Fervo\EnumBundle\Enum\AbstractTranslatableEnum;
use MyCLabs\Enum\Enum;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractEnumNormalizer
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var bool
     */
    protected $includeValuesInValidationMessage;

    /**
     * @var bool
     */
    protected $translateValuesInValidationMessage;

    /**
     * @var bool
     */
    protected $translateValueInResource;

    /**
     * @param TranslatorInterface $translator
     * @param bool $includeValuesInValidationMessage
     * @param bool $translateValuesInValidationMessage
     * @param bool $translateValueInResource
     */
    public function __construct(TranslatorInterface $translator, $includeValuesInValidationMessage, $translateValuesInValidationMessage, $translateValueInResource)
    {
        $this->translator = $translator;
        $this->includeValuesInValidationMessage = $includeValuesInValidationMessage;
        $this->translateValuesInValidationMessage = $translateValuesInValidationMessage;
        $this->translateValueInResource = $translateValueInResource;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function normalizeEnum($object)
    {
        if (!$object instanceof Enum) {
            throw new InvalidArgumentException('The object must extends "\MyCLabs\Enum\Enum".');
        }

        if ($object instanceof AbstractTranslatableEnum && $this->translateValueInResource) {
            return $this->translator->trans($object->getTranslationKey(), array(), 'enums');
        }

        return $object->getValue();
    }

    /**
     * @param $data
     * @param $class
     * @return Enum
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    protected function denormalizeEnum($data, $class)
    {
        /* @var $class AbstractTranslatableEnum|string */
        if (null === $data) {
            return null;
        }
        $values = $class::toArray();
        /* @var $values array */
        $allowedValues = array();
        foreach ($values as $constant => $constantValue) {
            try {
                $enum = $class::$constant();
                if ($enum instanceof AbstractTranslatableEnum && $this->translateValueInResource) {
                    $value = $this->translator->trans($enum->getTranslationKey(), array(), 'enums');
                    if ($value === ((string)$data)) {
                        return $enum;
                    }
                } elseif ($data === $constantValue) {
                    return $enum;
                }
                if ($enum instanceof AbstractTranslatableEnum && $this->translateValuesInValidationMessage) {
                    $allowedValues[] = $this->translator->trans($enum->getTranslationKey(), array(), 'enums');
                } else {
                    $allowedValues[] = $constantValue;
                }
            } catch (\UnexpectedValueException  $e) {
                $allowedValues[] = $constantValue;
            }

        }
        if ($this->includeValuesInValidationMessage) {
            throw new UnexpectedValueException($this->translator->trans(
                '%value% is not a valid value. Allowed values: %allowed_values%',
                array('%value%' => $data, '%allowed_values%' => implode(',', $allowedValues)),
                'validators'
            ));
        }
        throw new UnexpectedValueException($this->translator->trans(
            '%value% is not a valid enum value.',
            array('%value%' => $data),
            'validators'
        ));
    }
}