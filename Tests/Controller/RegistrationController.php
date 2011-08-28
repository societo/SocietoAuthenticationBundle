<?php

/**
 * This file is applied CC0 <http://creativecommons.org/publicdomain/zero/1.0/>
 */

namespace Societo\AuthenticationBundle\Tests\Controller;

use Societo\BaseBundle\Test\WebTestCase;
use Societo\AuthenticationBundle\RegistrationEvents;

class RegistrationController extends WebTestCase
{
    const UNKNOWN_AUTH_PLUGIN_NAME = 'UNKNOWN_AUTH_PLUGIN_NAME';

    // TODO: change plugin name to in fixture
    const SKIN_PLUGIN_NAME = 'SocietoDefaultSkinPlugin';

    // TODO: change plugin name to in fixture
    const AUTH_PLUGIN_NAME = 'SocietoUsernameAuthPlugin';

    public function setUp()
    {
        $this->loadFixtures();
    }

    public function testHandleActionFree()
    {
        $client = static::createClient();
        $config = $client->getContainer()->get('societo.site_config');
        $config['self_registration'] = 'free';
        $config->flush();

        $crawler = $client->request('GET', '/registration/'.self::UNKNOWN_AUTH_PLUGIN_NAME);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/registration/'.self::SKIN_PLUGIN_NAME);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/registration/'.self::AUTH_PLUGIN_NAME);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testHandleActionCheckUsernameEvent()
    {
        $client = static::createClient();
        $config = $client->getContainer()->get('societo.site_config');
        $config['self_registration'] = 'free';
        $config->flush();

        $dispatcher = $client->getContainer()->get('event_dispatcher');

        $func = function ($event) {
            $controller = $event->getController();
            $event->setResponse($controller->redirect($controller->generateUrl('_root')));
        };
        $dispatcher->addListener(RegistrationEvents::USERNAME_CHECK, $func);
        $crawler = $client->request('GET', '/registration/'.self::AUTH_PLUGIN_NAME);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $dispatcher->removeListener(RegistrationEvents::USERNAME_CHECK, $func);

        // TODO: add test for hasSignUp
    }

    public function testHandleActionOnFormBuild()
    {
        $client = static::createClient();
        $config = $client->getContainer()->get('societo.site_config');
        $config['self_registration'] = 'free';
        $config->flush();

        $crawler = $client->request('GET', '/registration/'.self::AUTH_PLUGIN_NAME);
        $this->assertEquals(0, $crawler->filter('#form_profile')->count());

        $this->loadFixtures(array('Societo\AuthenticationBundle\Tests\Fixtures\LoadProfileData'), false);

        $crawler = $client->request('GET', '/registration/'.self::AUTH_PLUGIN_NAME);
        $this->assertEquals(1, $crawler->filter('#form_profile')->count());
        $this->assertEquals(1, $crawler->filter('#form_profile_hobby')->count());
    }

    public function testHandleActionFormBuildEvent()
    {
        $client = static::createClient();
        $config = $client->getContainer()->get('societo.site_config');
        $config['self_registration'] = 'free';
        $config->flush();

        $dispatcher = $client->getContainer()->get('event_dispatcher');

        $func = function ($event) {
            $event->getBuilder()
                ->add('_input_test_registration_controller', 'hidden', array(
                    'required' => false,
                ))
            ;
        };
        $dispatcher->addListener(RegistrationEvents::FORM_BUILD, $func);
        $crawler = $client->request('GET', '/registration/'.self::AUTH_PLUGIN_NAME);
        $this->assertEquals(1, $crawler->filter('#form__input_test_registration_controller')->count());
        $dispatcher->removeListener(RegistrationEvents::FORM_BUILD, $func);
    }

    public function testHandleActionFormValidation()
    {
        $client = static::createClient();
        $config = $client->getContainer()->get('societo.site_config');
        $config['self_registration'] = 'free';
        $config->flush();

        $this->loadFixtures(array('Societo\AuthenticationBundle\Tests\Fixtures\LoadProfileData'), false);

        $crawler = $client->request('GET', '/registration/'.self::AUTH_PLUGIN_NAME);
        $form = $crawler->selectButton('Register')->form(array(
            'form[username]' => 'co3k',  // TODO: REMOVE
            'form[password]' => 'co3k',  // TODO: REMOVE
        ));
        $crawler = $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertRegExp('/This value should not be blank/', $client->getResponse()->getContent());

        $form = $crawler->selectButton('Register')->form(array(
            'form[username]' => 'co3k',  // TODO: REMOVE
            'form[password]' => 'co3k',  // TODO: REMOVE
            'form[profile][hobby]' => 'co3k',
        ));
        $crawler = $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('Your registration has been successful.', $client->getContainer()->get('session')->getFlash('success'));
    }

    public function testHandleActionAdminActivation()
    {
        $client = static::createClient();
        $config = $client->getContainer()->get('societo.site_config');
        $config['self_registration'] = 'admin_activation';
        $config->flush();

        $crawler = $client->request('GET', '/registration/'.self::AUTH_PLUGIN_NAME);
        $form = $crawler->selectButton('Register')->form(array(
            'form[username]' => 'co3k',  // TODO: REMOVE
            'form[password]' => 'co3k',  // TODO: REMOVE
        ));
        $crawler = $client->submit($form);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('Your signup was successful. Please wait until administrator approves your signup.', $client->getContainer()->get('session')->getFlash('notice'));
    }

    public function testHandleActionUnknown()
    {
        $client = static::createClient();
        $config = $client->getContainer()->get('societo.site_config');
        $config['self_registration'] = 'unknown';
        $config->flush();

        $crawler = $client->request('GET', '/registration/'.self::AUTH_PLUGIN_NAME);
        $form = $crawler->selectButton('Register')->form(array(
            'form[username]' => 'co3k',  // TODO: REMOVE
            'form[password]' => 'co3k',  // TODO: REMOVE
        ));
        $crawler = $client->submit($form);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testHandleActionDisable()
    {
        $client = static::createClient();
        $config = $client->getContainer()->get('societo.site_config');
        $config['self_registration'] = 'disable';
        $config->flush();

        $crawler = $client->request('GET', '/registration/'.self::AUTH_PLUGIN_NAME);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
