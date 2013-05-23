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
class StorageLogical extends Storage {

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $volumeGroup;

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath() {
        return '/dev/' . $this->getVolumeGroup() . '/';
    }

    public function getDiskEntity() {
        return new \Jan\ZevirtBundle\Entity\DiskLogical;
    }

    public function create($disk, $size) {
        "";
    }

    public function toXml() {
        $ret = "       
        <pool type='logical'>
            <uuid>{$this->getUuid()}</uuid>        
            <name>{$this->getRealName()}</name>
            <target>
              <path>{$this->getPath()}</path>
            </target>
        </pool>";

        return $ret;
    }

    /**
     * Set volumeGroup
     *
     * @param string $volumeGroup
     * @return StorageLogical
     */
    public function setVolumeGroup($volumeGroup) {
        $this->volumeGroup = $volumeGroup;

        return $this;
    }

    /**
     * Get volumeGroup
     *
     * @return string 
     */
    public function getVolumeGroup() {
        return $this->volumeGroup;
    }

    public function toArray() {
        $array = array(
            'path' => $this->getPath(),
            'volumeGroup' => $this->getVolumeGroup()
        );

        return array_merge($array, parent::toArray());
    }

    public function getRealName() {
        return $this->getVolumeGroup();
    }

}