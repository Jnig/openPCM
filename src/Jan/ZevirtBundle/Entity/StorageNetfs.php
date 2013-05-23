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
class StorageNetfs extends Storage {

    private $diskEntity = 'DiskFile';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hostname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $exportPath;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * Set hostname
     *
     * @param string $hostname
     * @return StorageNetfs
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
     * Set exportPath
     *
     * @param string $exportPath
     * @return StorageNetfs
     */
    public function setExportPath($exportPath) {
        $this->exportPath = $exportPath;

        return $this;
    }

    /**
     * Get exportPath
     *
     * @return string 
     */
    public function getExportPath() {
        return $this->exportPath;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return StorageNetfs
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
        return "       
      <pool type='netfs'>
            <name>{$this->getRealName()}</name>
        <source>
          <host name='{$this->getHostname()}' />
          <dir path='{$this->getExportPath()}'/>
          <format type='nfs'/>
        </source>
        <target>
          <path>{$this->getPath()}</path>
        </target>
      </pool> ";
    }

    public function getDiskEntity() {
        return new \Jan\ZevirtBundle\Entity\DiskFile;
    }

    public function create($disk, $size) {
        
    }

}