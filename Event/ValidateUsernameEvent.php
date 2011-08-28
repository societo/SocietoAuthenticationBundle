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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateUsernameEvent extends Event
{
    private $controller;

    /**
     * The response object
     * @var Symfony\Component\HttpFoundation\Response
     */
    private $response;

    /**
     * The request the kernel is currently processing
     * @var Symfony\Component\HttpFoundation\Request
     */
    private $request;

    private $username;

    public function __construct($controller, Request $request, $username = null)
    {
        $this->controller = $controller;
        $this->request = $request;
        $this->username = $username;
    }

    public function getController()
    {
        return $this->controller;
    }

    /**
     * Returns the request the kernel is currently processing
     *
     * @return Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the response object
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets a response and stops event propagation
     *
     * @param Symfony\Component\HttpFoundation\Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        $this->stopPropagation();
    }

    /**
     * Returns whether a response was set
     *
     * @return Boolean Whether a response was set
     */
    public function hasResponse()
    {
        return null !== $this->response;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function hasSignUp()
    {
        return false;
    }
}
