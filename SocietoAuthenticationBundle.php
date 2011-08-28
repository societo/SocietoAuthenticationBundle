<?php

/**
 * SocietoAuthenticationBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\AuthenticationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\HttpKernel\KernelEvents;

use Societo\AuthenticationBundle\DependencyInjection\Compiler\ProviderPass;

/**
 * SocietoAuthenticationBundle
 *
 * @author Kousuke Ebihara <ebihara@php.net>
 */
class SocietoAuthenticationBundle extends Bundle
{
    const AUTH_MODE_NAME = '_societo_auth_mode';

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ProviderPass());
    }

    public function boot()
    {
        $container = $this->container;
        $dispatcher = $container->get('event_dispatcher');
        $priority = 90; // TODO: magic number / how do we decide this number?

        $dispatcher->addListener(KernelEvents::REQUEST, function($event) use ($container) {
            if ($container->get('session')->has(SocietoAuthenticationBundle::AUTH_MODE_NAME)) {
                $authMode = $container->get('session')->get(SocietoAuthenticationBundle::AUTH_MODE_NAME);
            } else {
                $authMode = $event->getRequest()->get(SocietoAuthenticationBundle::AUTH_MODE_NAME);
            }

            $container->get('societo.user.provider')->setCurrentProvider($authMode);
        }, $priority);

        $dispatcher->addListener(SecurityEvents::INTERACTIVE_LOGIN, function($event) use ($container) {
            $authMode = $container->get('societo.user.provider')->getCurrentProviderName();
            $container->get('session')->set(SocietoAuthenticationBundle::AUTH_MODE_NAME, $authMode);
        }, $priority);
    }
}
