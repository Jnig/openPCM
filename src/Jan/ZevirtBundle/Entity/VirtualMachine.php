<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Jan\ZevirtBundle\Entity\VirtualMachine
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class VirtualMachine {

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
     * @var integer $vcpu
     *
     * @ORM\Column(name="vcpu", type="integer")
     */
    private $vcpu;

    /**
     * @var integer $memory
     *
     * @ORM\Column(name="memory", type="integer")
     */
    private $memory;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $bootDev = 'hd';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $state = '';

    /**
     * @ORM\ManyToMany(targetEntity="Disk", inversedBy="virtualmachine")
     */
    private $disks;

    /**
     * @ORM\ManyToOne(targetEntity="Node")
     */
    private $nodeDefault;

    /**
     * 
     * @ORM\ManyToOne(targetEntity="Node", inversedBy="virtualmachines")
     */
    private $node;

    /**
     * 
     * @ORM\OneToMany(targetEntity="NetworkInterface", mappedBy="virtualMachine", cascade={"remove"})
     */
    private $networkInterfaces;

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
     * @return VirtualMachine
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
     * Set vcpu
     *
     * @param integer $vcpu
     * @return VirtualMachine
     */
    public function setVcpu($vcpu) {
        $this->vcpu = $vcpu;

        return $this;
    }

    /**
     * Get vcpu
     *
     * @return integer 
     */
    public function getVcpu() {
        return $this->vcpu;
    }

    /**
     * Set memory
     *
     * @param integer $memory
     * @return VirtualMachine
     */
    public function setMemory($memory) {
        $this->memory = $memory;

        return $this;
    }

    /**
     * Get memory
     *
     * @return integer 
     */
    public function getMemory() {
        return $this->memory;
    }

    /**
     * Set uuid
     *
     * @param string $uuid
     * @return VirtualMachine
     */
    public function setUuid($uuid) {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string 
     */
    public function getUuid() {
        return $this->uuid;
    }

    public function __toString() {
        return $this->getName();
    }

    public function toArray() {


        $array = array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'vcpu' => $this->getVcpu(),
            'memory' => $this->getMemory(),
            'node' => $this->getNode()->getId(),
            'nodeName' => $this->getNode()->getName(),
            'bootDev' => $this->getBootDev(),
            'state' => $this->getState()
        );

        if (count($this->getNode())) {
            $array['nodeDefaultName'] = $this->getNodeDefault()->getName();
        }

        return $array;
    }

    /**
     * Add disks
     *
     * @param Jan\ZevirtBundle\Entity\Disk $disks
     * @return VirtualMachine
     */
    public function addDisk(\Jan\ZevirtBundle\Entity\Disk $disks) {

        $this->disks[] = $disks;

        return $this;
    }

    /**
     * Remove disks
     *
     * @param Jan\ZevirtBundle\Entity\Disk $disks
     */
    public function removeDisk(\Jan\ZevirtBundle\Entity\Disk $disks) {
        $this->disks->removeElement($disks);
    }

    /**
     * Get disks
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getDisks() {
        return $this->disks;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->disks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->networkInterfaces = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add nodesDefined
     *
     * @param \Jan\ZevirtBundle\Entity\Node $nodesDefined
     * @return VirtualMachine
     */
    public function addNodesDefined(\Jan\ZevirtBundle\Entity\Node $nodesDefined) {
        $this->nodesDefined[] = $nodesDefined;

        return $this;
    }

    /**
     * Remove nodesDefined
     *
     * @param \Jan\ZevirtBundle\Entity\Node $nodesDefined
     */
    public function removeNodesDefined(\Jan\ZevirtBundle\Entity\Node $nodesDefined) {
        $this->nodesDefined->removeElement($nodesDefined);
    }

    /**
     * Get nodesDefined
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNodesDefined() {
        return $this->nodesDefined;
    }

    /**
     * Set node
     *
     * @param \Jan\ZevirtBundle\Entity\Node $node
     * @return VirtualMachine
     */
    public function setNode(\Jan\ZevirtBundle\Entity\Node $node = null) {
        $this->node = $node;

        return $this;
    }

    /**
     * Get node
     *
     * @return \Jan\ZevirtBundle\Entity\Node 
     */
    public function getNode() {
        return $this->node;
    }

    /**
     * Add networkInterfaces
     *
     * @param \Jan\ZevirtBundle\Entity\NetworkInterface $networkInterfaces
     * @return VirtualMachine
     */
    public function addNetworkInterface(\Jan\ZevirtBundle\Entity\NetworkInterface $networkInterfaces) {
        $networkInterfaces->setVirtualMachine($this);
        $this->networkInterfaces[] = $networkInterfaces;

        return $this;
    }

    /**
     * Remove networkInterfaces
     *
     * @param \Jan\ZevirtBundle\Entity\NetworkInterface $networkInterfaces
     */
    public function removeNetworkInterface(\Jan\ZevirtBundle\Entity\NetworkInterface $networkInterfaces) {
        $this->networkInterfaces->removeElement($networkInterfaces);
    }

    /**
     * Get networkInterfaces
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNetworkInterfaces() {
        return $this->networkInterfaces;
    }

    /**
     * Set bootDev
     *
     * @param string $bootDev
     * @return VirtualMachine
     */
    public function setBootDev($bootDev) {
        $this->bootDev = $bootDev;

        return $this;
    }

    /**
     * Get bootDev
     *
     * @return string 
     */
    public function getBootDev() {
        return $this->bootDev;
    }

    public function getRealName() {
        return 'vm_' . $this->getId();
    }

    public function isHa() {
        return $this->getNode()->getCluster()->isHa();
    }

    public function getDrbdDisks() {
        $ret = array();
        foreach ($this->getDisks() as $entity) {
            if (get_class($entity) == 'Jan\ZevirtBundle\Entity\DiskDrbd') {
                $ret[] = $entity;
            }
        }

        return $ret;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return VirtualMachine
     */
    public function setState($state) {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState() {
        return $this->state;
    }

    /**
     * Set nodeDefault
     *
     * @param \Jan\ZevirtBundle\Entity\Node $nodeDefault
     * @return VirtualMachine
     */
    public function setNodeDefault(\Jan\ZevirtBundle\Entity\Node $nodeDefault = null) {
        $this->nodeDefault = $nodeDefault;

        return $this;
    }

    /**
     * Get nodeDefault
     *
     * @return \Jan\ZevirtBundle\Entity\Node 
     */
    public function getNodeDefault() {
        return $this->nodeDefault;
    }

}