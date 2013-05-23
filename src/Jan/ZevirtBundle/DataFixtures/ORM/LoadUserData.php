<?php

namespace Jan\ZevirtBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Jan\ZevirtBundle\Entity\Node;
use Jan\ZevirtBundle\Entity\User;

class LoadUserData implements FixtureInterface {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
        $userAdmin = new User();
        $userAdmin->setUsername('admin');
        $userAdmin->setPlainPassword('admin');
        $userAdmin->setEmail('info@jakobniggel.de');
        $userAdmin->setEnabled(true);
        $userAdmin->setLdap(false);
        $userAdmin->addRole('ROLE_SUPER_ADMIN');

        $manager->persist($userAdmin);

        $node = new \Jan\ZevirtBundle\Entity\Node;
        $node->setName('test-node');
        $node->setHostname('test');
        $node->setPassword('foo');
        $manager->persist($node);

        $storage = new \Jan\ZevirtBundle\Entity\StorageLogical;
        $storage->setName('test-lvm-storage');
        $storage->addNode($node);
        $manager->persist($storage);


        $manager->flush();
    }

}