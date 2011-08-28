<?php

/**
 * SocietoAuthenticationBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\AuthenticationBundle\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * MemberConfigUser
 *
 * @author Kousuke Ebihara <ebihara@php.net>
 */
class MemberConfigUser implements UserInterface
{
    private $username, $password;

    public function __construct($em, $config)
    {
        $this->username = (string)$config;
        $member = $config->getMember();

        $this->password = $this->getConfig($em, $config->getNamespace(), 'password', $member);
    }

    // TODO: MOVE
    public function getConfig($em, $namespace, $name, $member)
    {
        $config = $em->getRepository('SocietoBaseBundle:MemberConfig')
            ->findOneBy(array(
                'namespace' => $namespace,
                'name'      => $name,
                'member'    => $member->getId(),
            ));

        return (string)$config;
    }

    public function getRoles()
    {
        return array();
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return '';
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
    }

    public function equals(UserInterface $user)
    {
        return true;
    }
}
