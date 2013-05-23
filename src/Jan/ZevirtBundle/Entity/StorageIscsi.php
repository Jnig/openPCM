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
class StorageIscsi extends Storage {

    private $diskEntity = 'DiskIscsi';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hostname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $devicePath;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path = '/dev/disk/by-path';

    /**
     * Set hostname
     *
     * @param string $hostname
     * @return StorageIscsi
     */
    public function setHostname($hostname) {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Get hostname
     *
     * @return string 
     */
    public function getHostname() {
        return $this->hostname;
    }

    /**
     * Set devicePath
     *
     * @param string $devicePath
     * @return StorageIscsi
     */
    public function setDevicePath($devicePath) {
        $this->devicePath = $devicePath;

        return $this;
    }

    /**
     * Get devicePath
     *
     * @return string 
     */
    public function getDevicePath() {
        return $this->devicePath;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return StorageIscsi
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
        <pool type='iscsi'>
            <uuid>{$this->getUuid()}</uuid>        
            <name>{$this->getRealName()}</name>
       <source>
          <host name='{$this->getHostname()}'/>
        <device path='{$this->getDevicePath()}'/>
       </source>
            <target>
              <path>{$this->getPath()}</path>
            </target>
        </pool>";

        return $ret;
    }

    public function getDiskEntity() {
        return new \Jan\ZevirtBundle\Entity\DiskIscsi;
    }

    public function create($disk, $size) {
        
    }

}