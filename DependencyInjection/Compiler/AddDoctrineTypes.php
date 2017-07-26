<?php

namespace Fervo\EnumBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * {@inheritDoc}
 */
class AddDoctrineTypes implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     * @throws \Symfony\Component\DependencyInjection\Exception\OutOfBoundsException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('doctrine.dbal.connection_factory')) {
            return;
        }

        $types = $container->getParameter('doctrine.dbal.connection_factory.types');
        $enumTypes = $container->getParameter('fervo_enum.doctrine_type_classes');
        $arrayType = ['enumarray' => [
            'commented' => true,
            'class' => 'Fervo\EnumBundle\Doctrine\EnumArrayType',
        ]];

        $allTypes = array_merge($types, $enumTypes, $arrayType);
        $container->setParameter('fervo_enum.all_types', $allTypes);

        $container->findDefinition('doctrine.dbal.connection_factory')
            ->replaceArgument(0, '%fervo_enum.all_types%');
    }
}
