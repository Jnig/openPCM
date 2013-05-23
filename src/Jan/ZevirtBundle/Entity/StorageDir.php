<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jan\ZevirtBundle\Entity\Storage;

/**
 * Jan\ZevirtBundle\Entity\Storage
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class StorageDir extends Storage {

    private $diskEntity = 'DiskFile';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * Set path
     *
     * @param string $path
     * @return StorageDir
     */
    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath() {
        return $this->path;
    }

    public function toXml() {
        $ret = "       
        <pool type='dir'>
            <uuid>{$this->getUuid()}</uuid>        
            <name>{$this->getRealName()}</name>
            <target>
              <path>{$this->getPath()}</path>
            </target>
        </pool>";

        return $ret;
    }

    public function getDiskEntity() {
        return new \Jan\ZevirtBundle\Entity\DiskFile;
    }

    public function create($entity, $size) {
        $entity->setPath($this->getPath() . '/' . $entity->getId());
        $entity->getNode()->getConnection()->exec('qemu-img create -f qcow2 ' . escapeshellarg($entity->getPath()) . ' 1G');

        return $entity;
    }

}