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
class StorageRbd extends Storage {

    private $entity = 'DiskRbd';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $secret;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hosts;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pool;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $secretUuid;

    /**
     * Set username
     *
     * @param string $username
     * @return StorageRbd
     */
    public function setUsername($username) {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Set secret
     *
     * @param string $secret
     * @return StorageRbd
     */
    public function setSecret($secret) {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get secret
     *
     * @return string 
     */
    public function getSecret() {
        return $this->secret;
    }

    /**
     * Set hosts
     *
     * @param string $hosts
     * @return StorageRbd
     */
    public function setHosts($hosts) {
        $this->hosts = $hosts;

        return $this;
    }

    /**
     * Get hosts
     *
     * @return string 
     */
    public function getHosts() {
        return $this->hosts;
    }

    public function getDiskEntity() {
        return new \Jan\ZevirtBundle\Entity\DiskRbd;
    }

    public function create($disk, $size) {
        
    }

    public function getHostsXml() {
        $hostsXml = '';
        $hosts = explode("\n", $this->getHosts());

        foreach ($hosts as $host) {
            $host = trim($host);

            $hostsXml .= "<host name='$host' port='6789'/>";
        }

        return $hostsXml;
    }

    public function toXml() {
        $hostsXml = $this->getHostsXml();

        $ret = "       
        <pool type='rbd'>
            <uuid>{$this->getUuid()}</uuid>        
            <name>{$this->getRealName()}</name>
            <source>
              $hostsXml
            </source>
        </pool>";

        //rbd pool not supported for ubuntu/rhel

        return '';
    }

    public function toArray() {
        $array = array(
            'pool' => $this->getPool(),
            'hosts' => $this->getHosts(),
            'username' => $this->getUsername(),
            'secret' => $this->getSecret()
        );

        return array_merge($array, parent::toArray());
    }

    /**
     * Set pool
     *
     * @param string $pool
     * @return StorageRbd
     */
    public function setPool($pool) {
        $this->pool = $pool;

        return $this;
    }

    /**
     * Get pool
     *
     * @return string 
     */
    public function getPool() {
        return $this->pool;
    }

    /**
     * Set secretUuid
     *
     * @param string $secretUuid
     * @return StorageRbd
     */
    public function setSecretUuid($secretUuid) {
        $this->secretUuid = $secretUuid;

        return $this;
    }

    /**
     * Get secretUuid
     *
     * @return string 
     */
    public function getSecretUuid() {
        return $this->secretUuid;
    }

    public function toSecretDefineXml() {
        if ($this->getUsername() != '' && $this->getSecret() != '') {
            return "<secret ephemeral='no' private='no'>
                        <uuid>{$this->getSecretUuid()}</uuid>
                        <usage type='ceph'>
                                <name>{$this->getUsername()} secret</name>
                        </usage>
                    </secret>";
        } else {
            return '';
        }
    }

    public function getAuthXml() {
        if ($this->getUsername() != '' && $this->getSecret() != '') {
            $username = str_replace('client.', '', $this->getUsername());

            return "<auth username='$username'>
                        <secret type='ceph' uuid='{$this->getSecretUuid()}'/>
                    </auth>";
        } else {
            return '';
        }
    }

}