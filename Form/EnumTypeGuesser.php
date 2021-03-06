<?php

namespace Fervo\EnumBundle\Form;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping\MappingException as LegacyMappingException;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 *
 */
class EnumTypeGuesser implements FormTypeGuesserInterface
{
    protected $registry;
    protected $doctrineFormMap;

    private $cache = array();

    public function __construct(ManagerRegistry $registry, array $doctrineFormMap)
    {
        $this->registry = $registry;
        $this->doctrineFormMap = $doctrineFormMap;
    }

    /**
     * {@inheritDoc}
     */
    public function guessType($class, $property)
    {
        if (!$ret = $this->getMetadata($class)) {
            return null;
        }

        list($metadata, $name) = $ret;

        $doctrineType = $metadata->getTypeOfField($property);

        if (isset($this->doctrineFormMap[$doctrineType])) {
            return new TypeGuess($this->doctrineFormMap[$doctrineType]['class'], array(), Guess::HIGH_CONFIDENCE);
        }
    }

    protected function getMetadata($class)
    {
        // normalize class name
        $class = ClassUtils::getRealClass(ltrim($class, '\\'));

        if (array_key_exists($class, $this->cache)) {
            return $this->cache[$class];
        }

        $this->cache[$class] = null;
        foreach ($this->registry->getManagers() as $name => $em) {
            try {
                return $this->cache[$class] = array($em->getClassMetadata($class), $name);
            } catch (MappingException $e) {
                // not an entity or mapped super class
            } catch (LegacyMappingException $e) {
                // not an entity or mapped super class, using Doctrine ORM 2.2
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function guessRequired($class, $property)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function guessMaxLength($class, $property)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function guessPattern($class, $property)
    {

    }
}
