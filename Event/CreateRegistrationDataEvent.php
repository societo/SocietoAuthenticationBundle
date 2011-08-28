<?php

/**
 * SocietoAuthenticationBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\AuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class CreateRegistrationDataEvent extends Event
{
    private $em, $signUp, $member, $account;

    public function __construct($em, $signUp, $member = null, $account = null)
    {
        $this->em = $em;
        $this->signUp = $signUp;
        $this->member = $member;
        $this->account = $account;
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    public function getSignUp()
    {
        return $this->signUp;
    }

    public function getMember()
    {
        return $this->member;
    }

    public function getAccount()
    {
        return $this->account;
    }
}
