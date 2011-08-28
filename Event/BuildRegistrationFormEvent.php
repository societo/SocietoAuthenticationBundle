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

class BuildRegistrationFormEvent extends Event
{
    private $builder;

    public function __construct($builder)
    {
        $this->builder = $builder;
    }

    public function getBuilder()
    {
        return $this->builder;
    }
}
