<?php

namespace Fervo\EnumBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * {@inheritDoc}
 */
class UnregisterFormGuesser implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('doctrine')) {
            $container->removeDefinition('fervo_enum.form_guesser');
        }
    }
}
