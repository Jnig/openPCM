<?php

namespace Jan\ZevirtBundle\Entity\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class NetworkInterfaceRepository extends EntityRepository {

    public function getRandomUnusedMac() {
        while (1) {
            $mac = $this->getRandomMac();

            $result = $this->findByMacAddress($mac);
            if (!count($result)) {
                return $mac;
            }
        }
    }

    private function getRandomMac() {
        return '52:54:00:' . dechex(mt_rand(0, 255)) . ':' . dechex(mt_rand(0, 255)) . ':' . dechex(mt_rand(0, 255));
    }

}