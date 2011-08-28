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
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * SocietoUserProvider
 *
 * @author Kousuke Ebihara <ebihara@php.net>
 */
class SocietoUserProvider implements UserProviderInterface
{
    private $providers = array(), $currentProvider, $em, $secret;

    public function __construct($em, $secret)
    {
        $this->em = $em;
        $this->secret = $secret;
    }

    public function addProvider($name, $provider)
    {
        $this->providers[$name] = $provider;
    }

    public function setCurrentProvider($currentProvider)
    {
        $this->currentProvider = $currentProvider;
    }

    public function getCurrentProviderName()
    {
        if (!$this->currentProvider) {
            throw new \Exception('no current provider');
        }

        return $this->currentProvider;
    }

    public function getCurrentProvider()
    {
        if (!isset($this->providers[$this->getCurrentProviderName()])) {
            throw new \Exception('Unknown provider');
        }

        return $this->providers[$this->getCurrentProviderName()];
    }

    public function hasCurrentProvider()
    {
        return (bool)$this->currentProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $originalUser = $this->getCurrentProvider()->loadUserByUsername($username);
        if (!$originalUser) {
            throw new UsernameNotFoundException('Not found');
        }

        $account = $this->getAccountByUser($originalUser);

        $member = $account->getMember();
        $member->modifyLastLogin();
        $this->em->persist($member);
        $this->em->flush();

        return $account;
    }

    public function getAccountByUser($user)
    {
        $sql = 'SELECT a FROM SocietoAuthenticationBundle:Account a WHERE a.username = :username';
        $query = $this->em->createQuery($sql);
        $query->setParameter('username', $user->getUsername());

        $result = $query->getResult();
        if (!$result) {
            throw new UsernameNotFoundException('Not found');
        }

        $account = $result[0];
        if ($account->getMember()->getLocked()) {
            throw new LockedException('Your account is locked');
        }

        $account->setUser($user);
        $account->setSecret($this->secret);

        return $account;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->getAccountByUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supportsClass($class)) {
                return true;
            }
        }

        return false;
    }
}
