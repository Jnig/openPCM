<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jan\ZevirtBundle\Model\Connection;

/**
 * Jan\ZevirtBundle\Entity\Node
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Node {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $hostname
     *
     * @ORM\Column(name="hostname", type="string", length=255)
     */
    private $hostname;

    /**
     * @var string $sshPublicKey
     *
     * @ORM\Column(name="sshPublicKey", type="string", length=500,nullable=true)
     */
    private $sshPublicKey;

    /**
     * @ORM\ManyToMany(targetEntity="Network", inversedBy="nodes")
     * */
    private $networks;

    /**
     * 
     *
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="VirtualMachine", mappedBy="node")
     * */
    private $virtualmachines;

    /**
     * @ORM\ManyToOne(targetEntity="Cluster", inversedBy="nodes")
     * */
    private $cluster;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * */
    private $corosyncIp1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * */
    private $corosyncIp2;

    /**
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stonith;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $stonithParameters;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $publicKey;

    /**
     * @ORM\OneToMany(targetEntity="Disk", mappedBy="node", cascade={"persist", "remove"} )
     */
    private $disks;

    /**
     * @ORM\OneToMany(
     *                targetEntity="NodeStorage",
     *                mappedBy="node",
     *                cascade={"persist", "remove"},
     *                orphanRemoval=true
     *                )
     */
    protected $storages = array();

    /**
     * used to mark an node dirty, if node is offline while saving new virtualmachine config to cluster member
     * @ORM\Column(type="boolean")
     */
    private $dirty = false;

    /**
     * used to mark an node dirty, if node is offline while saving new virtualmachine config to cluster member
     * @ORM\Column(type="boolean")
     */
    private $standby = false;

    /**
     * Add storages
     *
     * @param Jan\ZevirtBundle\Entity\Storage $storages
     * @return Node
     */
    public function addStorage(\Jan\ZevirtBundle\Entity\Storage $storages) {
        $rel = new NodeStorage($this, $storages);
        $this->storages[] = $rel;

        return $this;
    }

    /**
     * Remove storages
     *
     * @param Jan\ZevirtBundle\Entity\Storage $storages
     */
    public function removeStorage(\Jan\ZevirtBundle\Entity\Storage $storages) {


        foreach ($this->storages as $rel) {
            if ($rel->getStorage() === $storages) {
                $this->storages->removeElement($rel);
                $rel->getStorage()->removeNode($this);
            }
        }
    }

    /**
     * Get storages
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getStorages() {
        $collection = new \Doctrine\Common\Collections\ArrayCollection();

        foreach ($this->storages as $rel) {
            $collection->add($rel->getStorage());
        }

        return $collection;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Node
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set hostname
     *
     * @param string $hostname
     * @return Node
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
     * Set sshPublicKey
     *
     * @param string $sshPublicKey
     * @return Node
     */
    public function setSshPublicKey($sshPublicKey) {
        $this->sshPublicKey = $sshPublicKey;

        return $this;
    }

    /**
     * Get sshPublicKey
     *
     * @return string 
     */
    public function getSshPublicKey() {
        return $this->sshPublicKey;
    }

    public function toArray() {


        if (count($this->getCluster())) {
            $clusterId = $this->getCluster()->getId();
            $clusterName = $this->getCluster()->getName();
            $ha = $this->getCluster()->isHa();
        } else {
            $clusterId = '';
            $clusterName = '';
            $ha = 0;
        }

        $array = array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'hostname' => $this->getHostname(),
            'storages' => $this->storagesToArray(),
            'cluster' => $clusterId,
            'clusterName' => $clusterName,
            'networks' => $this->networksToArray(),
            'ha' => $ha,
            'corosyncIp1' => $this->getCorosyncIp1(),
            'corosyncIp2' => $this->getCorosyncIp2(),
            'stonith' => $this->getStonith(),
            'stonithParameters' => $this->getStonithParameters(),
            'standby' => $this->getStandby()
        );

        return $array;
    }

    public function networksToArray() {
        $networks = array();
        if (count($this->getNetworks())) {
            foreach ($this->getNetworks() as $network) {
                $networks[] = $network->getId();
            }
        }

        return $networks;
    }

    public function storagesToArray() {
        $storages = array();
        if (count($this->getStorages())) {
            foreach ($this->getStorages() as $storage) {
                $storages[] = $storage->getId();
            }
        }

        return $storages;
        ;
    }

    public function __toString() {
        return $this->getName();
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Node
     */
    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Add virtualmachines
     *
     * @param \Jan\ZevirtBundle\Entity\VirtualMachine $virtualmachines
     * @return Node
     */
    public function addVirtualmachine(\Jan\ZevirtBundle\Entity\VirtualMachine $virtualmachines) {
        $this->virtualmachines[] = $virtualmachines;

        return $this;
    }

    /**
     * Remove virtualmachines
     *
     * @param \Jan\ZevirtBundle\Entity\VirtualMachine $virtualmachines
     */
    public function removeVirtualmachine(\Jan\ZevirtBundle\Entity\VirtualMachine $virtualmachines) {
        $this->virtualmachines->removeElement($virtualmachines);
    }

    /**
     * Get virtualmachines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVirtualmachines() {
        return $this->virtualmachines;
    }

    /**
     * Add networks
     *
     * @param \Jan\ZevirtBundle\Entity\Network $networks
     * @return Node
     */
    public function addNetwork(\Jan\ZevirtBundle\Entity\Network $networks) {
        $this->networks[] = $networks;

        return $this;
    }

    /**
     * Remove networks
     *
     * @param \Jan\ZevirtBundle\Entity\Network $networks
     */
    public function removeNetwork(\Jan\ZevirtBundle\Entity\Network $networks) {
        $this->networks->removeElement($networks);
    }

    /**
     * Get networks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNetworks() {
        return $this->networks;
    }

    /**
     * Set cluster
     *
     * @param \Jan\ZevirtBundle\Entity\Cluster $cluster
     * @return Node
     */
    public function setCluster(\Jan\ZevirtBundle\Entity\Cluster $cluster = null) {
        $cluster->removeNode($this);
        $cluster->addNode($this);
        $this->cluster = $cluster;

        return $this;
    }

    /**
     * Get cluster
     *
     * @return \Jan\ZevirtBundle\Entity\Cluster 
     */
    public function getCluster() {
        return $this->cluster;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->virtualmachinesDefined = new \Doctrine\Common\Collections\ArrayCollection();
        $this->virtualmachines = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set corosyncIp1
     *
     * @param string $corosyncIp1
     * @return Node
     */
    public function setCorosyncIp1($corosyncIp1) {
        $this->corosyncIp1 = $corosyncIp1;

        return $this;
    }

    /**
     * Get corosyncIp1
     *
     * @return string 
     */
    public function getCorosyncIp1() {
        return $this->corosyncIp1;
    }

    /**
     * Set corosyncIp2
     *
     * @param string $corosyncIp2
     * @return Node
     */
    public function setCorosyncIp2($corosyncIp2) {
        $this->corosyncIp2 = $corosyncIp2;

        return $this;
    }

    /**
     * Get corosyncIp2
     *
     * @return string 
     */
    public function getCorosyncIp2() {
        return $this->corosyncIp2;
    }

    public function safve() {
        foreach ($this->getStorages() as $storage) {
            $this->saveStorage($storage);
        }
        foreach ($this->getCluster()->getNodes() as $node) {
            $node->saveCorosync();
        }
    }

    public function isHa() {
        return $this->getCluster()->isHa();
    }

    /**
     * Set stonith
     *
     * @param string $stonith
     * @return Node
     */
    public function setStonith($stonith) {
        $this->stonith = $stonith;

        return $this;
    }

    /**
     * Get stonith
     *
     * @return string 
     */
    public function getStonith() {
        return $this->stonith;
    }

    /**
     * Set stonithParameters
     *
     * @param string $stonithParameters
     * @return Node
     */
    public function setStonithParameters($stonithParameters) {
        $this->stonithParameters = $stonithParameters;

        return $this;
    }

    /**
     * Get stonithParameters
     *
     * @return string 
     */
    public function getStonithParameters() {
        return $this->stonithParameters;
    }

    /**
     * Set publicKey
     *
     * @param string $publicKey
     * @return Node
     */
    public function setPublicKey($publicKey) {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * Get publicKey
     *
     * @return string 
     */
    public function getPublicKey() {
        return $this->publicKey;
    }

    public function getRealName() {
        return 'node_' . $this->getId();
    }

    /**
     * Add disks
     *
     * @param \Jan\ZevirtBundle\Entity\Disk $disks
     * @return Node
     */
    public function addDisk(\Jan\ZevirtBundle\Entity\Disk $disks) {
        $this->disks[] = $disks;

        return $this;
    }

    /**
     * Remove disks
     *
     * @param \Jan\ZevirtBundle\Entity\Disk $disks
     */
    public function removeDisk(\Jan\ZevirtBundle\Entity\Disk $disks) {
        $this->disks->removeElement($disks);
    }

    /**
     * Get disks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDisks() {
        return $this->disks;
    }

    /**
     * Set dirty
     *
     * @param boolean $dirty
     * @return Node
     */
    public function setDirty($dirty) {
        $this->dirty = $dirty;

        return $this;
    }

    /**
     * Get dirty
     *
     * @return boolean 
     */
    public function getDirty() {
        return $this->dirty;
    }

    /**
     * Set standby
     *
     * @param boolean $standby
     * @return Node
     */
    public function setStandby($standby) {
        $this->standby = $standby;

        return $this;
    }

    /**
     * Get standby
     *
     * @return boolean 
     */
    public function getStandby() {
        return $this->standby;
    }

}