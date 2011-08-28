<?php

/**
 * SocietoAuthenticationBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\AuthenticationBundle\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * UserConfigProvider
 *
 * @author Kousuke Ebihara <ebihara@php.net>
 */
class UserConfigProvider implements UserProviderInterface
{
    private $em, $namespace, $configName;

    public function __construct($em, $namespace, $configName)
    {
        $this->em = $em;
        $this->namespace = $namespace;
        $this->configName = $configName;
    }

    public function loadUserByUsername($username)
    {
        $config = $this->em->getRepository('SocietoBaseBundle:MemberConfig')
            ->findOneBy(array(
                'namespace' => $this->namespace,
                'name'      => $this->configName,
                'value'     => $username,
            ));

        if (!$config) {
            throw new UsernameNotFoundException('Not found');
        }

        return new MemberConfigUser($this->em, $config);
    }

    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUserName());
    }

    public function supportsClass($class)
    {
        return true;
    }
}
