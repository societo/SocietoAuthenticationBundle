<?php

/**
 * This file is applied CC0 <http://creativecommons.org/publicdomain/zero/1.0/>
 */

namespace Societo\AuthenticationBundle\Tests\Fixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Societo\BaseBundle\Entity\Profile;

class LoadProfileData implements FixtureInterface
{
    public function load($manager)
    {
        $profile = new Profile();
        $profile->setName('hobby');
        $profile->setIsRequired(true);

        $manager->persist($profile);
        $manager->flush();
    }
}
