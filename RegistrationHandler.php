<?php

/**
 * SocietoAuthenticationBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\AuthenticationBundle;

use Societo\AuthenticationBundle\Event\CreateRegistrationDataEvent;

class RegistrationHandler
{
    private $em;

    private $signUp;

    private $dispatcher;

    private $secret;

    public function __construct($em, $dispatcher, $secret)
    {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->secret = $secret;
    }

    public function setSignUp($signUp)
    {
        $this->signUp = $signUp;
    }

    public function setData($data)
    {
        $data = array_merge((array)$this->signUp->getParameters(), $data);
        $this->signUp->setParameters($data);

        if (!$this->signUp->getUsername()) {
            $this->signUp->setUsername($data['username']);
        }
    }

    public function register()
    {
        $member = $this->createMember();
        $account = $this->createAccount($member);

        $event = new CreateRegistrationDataEvent($this->em, $this->signUp, $member, $account);
        $this->dispatcher->dispatch('onSocietoRegistrationDataCreate', $event);

        foreach ($this->createProfiles($member) as $key => $profile)
        {
            $this->em->persist($profile);
        }

        $member->setDisplayName($account->getUsername());

        $this->em->persist($member);
        $this->em->persist($account);

        $this->em->flush();

        $event = new CreateRegistrationDataEvent($this->em, $this->signUp, $member, $account);
        $this->dispatcher->dispatch('onSocietoRegistrationDataFlush', $event);

        return $member;
    }

    public function createMember()
    {
        $member = new \Societo\BaseBundle\Entity\Member();

        return $member;
    }

    public function createAccount($member)
    {
        $account = new \Societo\AuthenticationBundle\Entity\Account($this->signUp->getNamespace(), $this->signUp->getUsername(), $member);
        $account->setSecret($this->secret);

        return $account;
    }

    public function createProfiles($member)
    {
        $results = array();

        $parameters = $this->signUp->getParameters();
        if (!isset($parameters['profile'])) {
            return $results;
        }

        // TODO: deep profile may not be valid data
        if (isset($parameters['profile']['profile'])) {
            $parameters['profile'] = $parameters['profile']['profile'];
        }

        foreach ($parameters['profile'] as $key => $value) {
            $profile = $this->em->getRepository('SocietoBaseBundle:Profile')->findOneBy(array('name' => $key));
            $memberProfile = new \Societo\BaseBundle\Entity\MemberProfile($member, $profile, $value);

            $results[$key] = $memberProfile;
        }

        return $results;
    }
}
