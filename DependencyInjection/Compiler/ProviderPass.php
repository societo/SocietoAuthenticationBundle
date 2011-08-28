<?php

/**
 * SocietoAuthenticationBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\AuthenticationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('societo.user.provider')) {
            return;
        }

        $definition = $container->getDefinition('societo.user.provider');

        $calls = $definition->getMethodCalls();
        $definition->setMethodCalls(array());
        foreach ($container->findTaggedServiceIds('societo.auth.user.provider') as $id => $attributes) {
            $definition->addMethodCall('addProvider', array($id, new Reference($id)));
        }
        $definition->setMethodCalls(array_merge($definition->getMethodCalls(), $calls));
    }
}
