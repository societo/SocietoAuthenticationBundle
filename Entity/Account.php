<?php

/**
 * SocietoAuthenticationBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\AuthenticationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Societo\BaseBundle\Entity\BaseEntity;

/**
 * Account
 *
 * @ORM\Entity
 * @ORM\Table(name="account",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="namespace_username",columns={"namespace", "username"})}
 * )
 * @author Kousuke Ebihara <ebihara@php.net>
 */
class Account extends BaseEntity implements UserInterface
{
    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @ORM\Column(name="namespace", type="string")
     */
    private $namespace;

    /**
     * @ORM\Column(name="username", type="string")
     */
    private $username;

    /**
     * @ORM\ManyToOne(targetEntity="Societo\BaseBundle\Entity\Member")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id")
     */
    private $member;

    private $secret;

    private $user;

    /**
     * Constructor.
     *
     * @param string $namespace
     * @param string $username
     * @param Societo\BaseBundle\Entity\Member $member
     * @param UserInterface $user
     */
    public function __construct($namespace, $username, $member, $user = null)
    {
        $this->namespace = $namespace;
        $this->username = $username;
        $this->member = $member;

        $this->user = $user;
    }

    /**
     * This method doesn't make sense.
     */
    public function getId()
    {
        throw new \LogicException('Account::getId() is unavailable because its mean is an ambiguous. Please use Account::getMemberId() or Account::getAccountId() instead.');
    }

    /**
     * Gets the identifier of this account.
     *
     * @return int
     */
    public function getAccountId()
    {
        return $this->id;
    }

    /**
     * Gets the identifier of the member.
     *
     * @return int
     */
    public function getMemberId()
    {
        return $this->member->getId();
    }

    /**
     * Sets the user.
     *
     * @param UserInterface $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Gets the user.
     *
     * @return UserInterface
     */
    public function getUser()
    {
        if (!$this->user) {
            // search from provider
            // ...

            // not found
            if (!$user) {
                throw new \Exception();
            }
        }

        return $this->user;
    }

    /**
     * Gets hashed password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->getUser()->getPassword();
    }

    /**
     * Sets secret to use for generating salt.
     *
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * Gets salt to hash password.
     *
     * @return string
     */
    public function getSalt()
    {
        return sha1($this->getMember()->getId().$this->secret);
    }

    /**
     * Gets username of this account.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Gets an instance of member of this account.
     *
     * @return User
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $result = array(self::ROLE_USER);
        if ($this->member->isAdmin()) {
            $result[] = self::ROLE_ADMIN;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function equals(UserInterface $user)
    {
        return $this->getUsername() == $user->getUsername();
    }
}
