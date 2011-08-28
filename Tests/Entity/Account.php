<?php

/**
 * This file is applied CC0 <http://creativecommons.org/publicdomain/zero/1.0/>
 */

namespace Societo\AuthenticationBundle\Tests\Entity;

use Societo\AuthenticationBundle\Entity\Account as AccountEntity;
use Societo\BaseBundle\Entity\Member;
use Societo\BaseBundle\Test\EntityTestCase;

class Account extends EntityTestCase
{
    public function createTestEntityManager($entityPaths = array())
    {
        if (!$entityPaths) {
            $entityPaths = $this->createClassFileDirectoryPaths(array(
                'Societo\BaseBundle\Entity\Member',
                'Societo\AuthenticationBundle\Entity\Account',
            ));
        }

        return parent::createTestEntityManager($entityPaths);
    }

    public function testConstructor()
    {
        $em = $this->createTestEntityManager();
        $this->rebuildDatabase($em);

        $member = new Member();
        $member->setDisplayName('Kousuke Ebihara');
        $em->persist($member);

        $namespace = 'test_namespace';
        $username = 'co3k';

        $account = new AccountEntity('test_namespace', $username, $member);
        $em->persist($account);
        $em->flush();

        $accounts = $em->createQuery('SELECT a FROM Societo\AuthenticationBundle\Entity\Account a WHERE a.namespace = :namespace')
            ->setParameter('namespace', $namespace)
            ->getResult();

        $this->assertEquals(1, count($accounts));
        $this->assertEquals($username, $accounts[0]->getUsername());
        $this->assertEquals($member->getId(), $accounts[0]->getMember()->getId());
        $this->assertEquals(1, $accounts[0]->getAccountId());
        $this->assertEquals($member->getId(), $accounts[0]->getMemberId());

        try {
            $accounts[0]->getId();

            $this->fail();
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \LogicException);
        }
    }

    public function testUser()
    {
        // setUser
        // getUser
        // var_dump($accounts[0]->getPassword());
        self::markTestIncomplete();
    }

    public function testSalt()
    {
        $em = $this->createTestEntityManager();
        $this->rebuildDatabase($em);

        $namespace = 'test_namespace';
        $username = 'co3k';

        $member = new Member();
        $member->setDisplayName('Kousuke Ebihara');
        $em->persist($member);
        $member2 = new Member();
        $member2->setDisplayName('Kousuke Ebihara');
        $em->persist($member2);
        $em->flush();

        $account = new AccountEntity('test_namespace', $username, $member);
        $this->assertEquals(sha1('1'), $account->getSalt());

        $account = new AccountEntity('test_namespace', $username, $member2);
        $this->assertEquals(sha1('2'), $account->getSalt());
    }

    public function testSecret()
    {
        $em = $this->createTestEntityManager();
        $this->rebuildDatabase($em);

        $member = new Member();
        $member->setDisplayName('Kousuke Ebihara');
        $em->persist($member);
        $em->flush();

        $secret = 'test_secret';

        $account = new AccountEntity('test_namespace', 'co3k', $member);
        $account->setSecret($secret);
        $this->assertEquals(sha1('1'.$secret), $account->getSalt());
    }

    public function testRoles()
    {
        $member = new Member();
        $member->setDisplayName('Kousuke Ebihara');

        $account = new AccountEntity('test_namespace', 'co3k', $member);
        $this->assertEquals(array(AccountEntity::ROLE_USER), $account->getRoles());
        $member->setIsAdmin(true);
        $this->assertEquals(array(AccountEntity::ROLE_USER, AccountEntity::ROLE_ADMIN), $account->getRoles());
    }

    public function testEraseCredentials()
    {
        self::markTestIncomplete();
    }

    public function testEquals()
    {
        self::markTestIncomplete();
    }

    /**
     * @expectedException Exception
     */
    public function testDenyDuplicateAccount()
    {
        $em = $this->createTestEntityManager();
        $this->rebuildDatabase($em);

        $member = new Member();
        $member->setDisplayName('Kousuke Ebihara');
        $em->persist($member);

        $namespace = 'test_namespace';
        $username = 'co3k';

        $account = new AccountEntity('test_namespace', $username, $member);
        $em->persist($account);
        $em->flush();

        $account = new AccountEntity('test_namespace', $username, $member);
        $em->persist($account);
        $em->flush();
    }
}
