<?php

/**
 * SocietoAuthenticationBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\AuthenticationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Societo\AuthenticationBundle\RegistrationEvents;
use Societo\AuthenticationBundle\Event\ValidateUsernameEvent;
use Societo\AuthenticationBundle\Event\BuildRegistrationFormEvent;
use Societo\AuthenticationBundle\RegistrationHandler;

/**
 * RegistrationController provides common interfaces to process registraion.
 *
 * @author Kousuke Ebihara <ebihara@php.net>
 */
class RegistrationController extends Controller
{
    /**
     * Handle some of registraion processes.
     *
     * End of signup is save of form inputted values.
     * If you want to need to do somethings before it, you should extend some process of this action by using event dispatcher.
     *
     * Registraion has the following steps:
     *
     *     0. (before this action) forward to this action with reserved username
     *     1. validating reserved username (notify RegistrationEvents::USERNAME_CHECK)
     *         1a. if the notified event has response, render it and halt this action process
     *     2. create a signup information from the previous username validation
     *     3. build form (notify RegistrationEvents::FORM_BUILD)
     *     4. (user fill the form and submit)
     *     5. repeat 1. - 3.
     *     6. validating inputted user information and redirecting home
     *
     * Registraion may need to activate by administrator or a user itself.
     *
     * @param string  $namespace  The name of the auth plugin name
     *
     * @return Response A Response instance
     */
    public function handleAction($namespace)
    {
        $this->checkRegistration();
        $this->checkAuthPlugin($namespace);

        $dispatcher = $this->get('event_dispatcher');
        $request = $this->get('request');
        $em = $this->get('doctrine.orm.entity_manager');

        $event = $this->onUsernameCheck();
        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        if ($event->hasSignUp()) {
            $signUp = $event->getSignUp();
        } else {
            $signUp = new \Societo\BaseBundle\Entity\SignUp($namespace, null);
        }

        $form = $this->onFormBuild();
        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $handler = new RegistrationHandler($em, $dispatcher, $this->container->getParameter('kernel.secret'));
                $handler->setSignUp($signUp);
                $handler->setData($form->getClientData());

                $config = $this->get('societo.site_config');
                if ('free' === $config['self_registration']) {
                    $this->get('session')->setFlash('success', 'Your registration has been successful.');

                    $handler->register();
                } elseif ('admin_activation' === $config['self_registration']) {
                    $this->get('session')->setFlash('notice', 'Your signup was successful. Please wait until administrator approves your signup.');

                    $em->persist($signUp);
                    $em->flush();
                } else {
                    throw $this->createNotFoundException('unknown activation method');
                }

                return $this->redirect($this->generateUrl('_root'));
            }
        }

        return $this->render('SocietoAuthenticationBundle:Registration:handle.html.twig', array(
            'form'      => $form->createView(),
            'namespace' => $namespace,
        ));
    }

    /**
     * Checks self registration configuration.
     */
    protected function checkRegistration()
    {
        $config = $this->get('societo.site_config');
        if (!$config['self_registration'] || 'disable' === $config['self_registration']) {
            throw $this->createNotFoundException('Self registration is disabled');
        }
    }

    /**
     * Checks if the specified namespace is a valid auth plugin.
     */
    protected function checkAuthPlugin($namespace)
    {
        try {
            $plugin = $this->get('kernel')->getBundle($namespace);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException('Unknown plugin name is specified');
        }

        if (!($plugin instanceof \Societo\PluginBundle\Plugin\SocietoAuthPlugin)) {
            throw $this->createNotFoundException('You must specify auth plugin name');
        }
    }

    /**
     * Dispatches RegistrationEvents::USERNAME_CHECK event for username validation.
     *
     * @return ValidateUsernameEvent
     */
    protected function onUsernameCheck()
    {
        $dispatcher = $this->get('event_dispatcher');
        $request = $this->get('request');
        $username = $request->request->get('username');

        $event = new ValidateUsernameEvent($this, $request, $username);
        $response = $dispatcher->dispatch(RegistrationEvents::USERNAME_CHECK, $event);

        return $event;
    }

    /**
     * Build registration form.
     *
     * In default, the registraiont form has embedded member profile form if any profiles are available.
     * And this method dispatches RegistrationEvents::FORM_BUILD to you extend that form.
     *
     * @return Form
     */
    protected function onFormBuild()
    {
        $dispatcher = $this->get('event_dispatcher');
        $em = $this->get('doctrine.orm.entity_manager');
        $profiles = $em->getRepository('SocietoBaseBundle:Profile')->findAll();

        $builder = $this->get('form.factory')->createBuilder('form');

        $event = new BuildRegistrationFormEvent($builder);
        $dispatcher->dispatch(RegistrationEvents::FORM_BUILD, $event);

        if ($profiles) {
            $builder->add('profile', new \Societo\BaseBundle\Form\MemberProfileType($em, $profiles, $this->get('validator')));
        }

        return $event->getBuilder()->getForm();
    }
}
