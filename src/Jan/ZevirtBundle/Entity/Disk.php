<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * 
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"DiskFile" = "DiskFile",
 *  "DiskLogical" = "DiskLogical",
 *  "DiskIscsi" = "DiskIscsi",
 *  "DiskRbd" = "DiskRbd",
 *  "DiskDrbd" = "DiskDrbd",
 * }) 
 */
abstract class Disk {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $capacity;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $allocation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $diskDevice;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $driverType;

    /**
     * @ORM\ManyToOne(targetEntity="Storage")
     */
    private $storage;

    /**
     * @ORM\ManyToOne(targetEntity="Node", inversedBy="disks")
     */
    private $node;

    /**
     * @ORM\ManyToMany(targetEntity="VirtualMachine", mappedBy="disks")
     */
    private $virtualmachine;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $child;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $created = true;
    private $targetDev;

    public function setTargetDev($name) {
        $this->targetDev = $name;
    }

    public function getTargetDev() {
        return $this->targetDev;
    }

    public function __toString() {
        return $this->getName();
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
     * @return Disk
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
     * Set diskDevice
     *
     * @param string $diskDevice
     * @return Disk
     */
    public function setDiskDevice($diskDevice) {
        $this->diskDevice = $diskDevice;

        return $this;
    }

    /**
     * Get diskDevice
     *
     * @return string 
     */
    public function getDiskDevice() {
        return $this->diskDevice;
    }

    /**
     * Set driverType
     *
     * @param string $driverType
     * @return Disk
     */
    public function setDriverType($driverType) {
        $this->driverType = $driverType;

        return $this;
    }

    /**
     * Get driverType
     *
     * @return string 
     */
    public function getDriverType() {
        return $this->driverType;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Disk
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

    /**
     * Set storage
     *
     * @param Jan\ZevirtBundle\Entity\Storage $storage
     * @return Disk
     */
    public function setStorage(\Jan\ZevirtBundle\Entity\Storage $storage = null) {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Get storage
     *
     * @return Jan\ZevirtBundle\Entity\Storage 
     */
    public function getStorage() {
        return $this->storage;
    }

    /**
     * Set node
     *
     * @param Jan\ZevirtBundle\Entity\Node $node
     * @return Disk
     */
    public function setNode(\Jan\ZevirtBundle\Entity\Node $node = null) {
        $this->node = $node;

        return $this;
    }

    /**
     * Get node
     *
     * @return Jan\ZevirtBundle\Entity\Node 
     */
    public function getNode() {
        return $this->node;
    }

    public function toArray() {
        if (count($this->getNode())) {
            $node = $this->getNode()->getName();
        } else {
            $node = '';
        }


        if (count($this->getStorage())) {
            $storage = $this->getStorage()->getId();
            $storageName = $this->getStorage()->getName();
        } else {
            $storage = '';
            $storageName = '';
        }
        $array = array(
            'id' => $this->getId(),
            'diskDevice' => $this->getDiskDevice(),
            'driverType' => $this->getDriverType(),
            'storage' => $storage,
            'storageName' => $storageName,
            'capacity' => round($this->getCapacity() / 1024 / 1024 / 1024, 2),
            'allocation' => round($this->getAllocation() / 1024 / 1024 / 1024, 2),
            'path' => $this->getPath(),
            'node' => $node,
            'entity' => preg_replace('#^.*\\\#', '', get_class($this)) //  Replace Jan\\ZevirtBundle\\Disk to get disk
        );

        if (count($this->getVirtualmachine())) {
            $vm = $this->getVirtualmachine();
            $array['virtualmachineId'] = $vm[0]->getId();
        }


        return $array;
    }

    /**
     * Add virtualmachine
     *
     * @param Jan\ZevirtBundle\Entity\VirtualMachine $virtualmachine
     * @return Disk
     */
    public function addVirtualmachine(\Jan\ZevirtBundle\Entity\VirtualMachine $virtualmachine) {
        $this->virtualmachine[] = $virtualmachine;
        $virtualmachine->addDisk($this);
        return $this;
    }

    /**
     * Remove virtualmachine
     *
     * @param Jan\ZevirtBundle\Entity\VirtualMachine $virtualmachine
     */
    public function removeVirtualmachine(\Jan\ZevirtBundle\Entity\VirtualMachine $virtualmachine) {
        $this->virtualmachine->removeElement($virtualmachine);
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->virtualmachine = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set capacity
     *
     * @param float $capacity
     * @return Disk
     */
    public function setCapacity($capacity) {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * Get capacity
     *
     * @return float 
     */
    public function getCapacity() {
        return $this->capacity;
    }

    /**
     * Set allocation
     *
     * @param float $allocation
     * @return Disk
     */
    public function setAllocation($allocation) {
        $this->allocation = $allocation;

        return $this;
    }

    /**
     * Get allocation
     *
     * @return float 
     */
    public function getAllocation() {
        return $this->allocation;
    }

    /**
     * Get virtualmachine
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getVirtualmachine() {
        return $this->virtualmachine;
    }

    /**
     * Set child
     *
     * @param boolean $child
     * @return Disk
     */
    public function setChild($child) {
        $this->child = $child;

        return $this;
    }

    /**
     * Get child
     *
     * @return boolean 
     */
    public function getChild() {
        return $this->child;
    }

    /**
     * Set created
     *
     * @param boolean $created
     * @return Disk
     */
    public function setCreated($created) {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return boolean 
     */
    public function getCreated() {
        return $this->created;
    }

}