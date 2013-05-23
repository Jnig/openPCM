<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jan\ZevirtBundle\Entity\Disk;

/**
 * Jan\ZevirtBundle\Entity\Storage
 *
 * @ORM\Table()
 * @ORM\HasLifecycleCallbacks() 
 * @ORM\Entity
 */
class DiskDrbd extends Disk {

    /**
     * @ORM\ManyToOne(targetEntity="DiskLogical")
     * */
    private $diskA;

    /**
     * @ORM\ManyToOne(targetEntity="DiskLogical")
     * */
    private $diskB;

    /**
     * @ORM\ManyToOne(targetEntity="CounterDrbd", cascade={"remove"})
     * */
    private $counter;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ipA;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ipB;

    /**
     * @ORM\ManyToOne(targetEntity="Node")
     * */
    private $nodeA;

    /**
     * @ORM\ManyToOne(targetEntity="Node")
     * */
    private $nodeB;

    /**
     * Set ipA
     *
     * @param string $ipA
     * @return DiskDrbd
     */
    public function setIpA($ipA) {
        $this->ipA = $ipA;

        return $this;
    }

    /**
     * Get ipA
     *
     * @return string 
     */
    public function getIpA() {
        return $this->ipA;
    }

    /**
     * Set ipB
     *
     * @param string $ipB
     * @return DiskDrbd
     */
    public function setIpB($ipB) {
        $this->ipB = $ipB;

        return $this;
    }

    /**
     * Get ipB
     *
     * @return string 
     */
    public function getIpB() {
        return $this->ipB;
    }

    /**
     * Set diskA
     *
     * @param \Jan\ZevirtBundle\Entity\DiskLogical $diskA
     * @return DiskDrbd
     */
    public function setDiskA(\Jan\ZevirtBundle\Entity\DiskLogical $diskA = null) {
        $this->diskA = $diskA;

        return $this;
    }

    /**
     * Get diskA
     *
     * @return \Jan\ZevirtBundle\Entity\DiskLogical 
     */
    public function getDiskA() {
        return $this->diskA;
    }

    /**
     * Set diskB
     *
     * @param \Jan\ZevirtBundle\Entity\DiskLogical $diskB
     * @return DiskDrbd
     */
    public function setDiskB(\Jan\ZevirtBundle\Entity\DiskLogical $diskB = null) {
        $this->diskB = $diskB;

        return $this;
    }

    /**
     * Get diskB
     *
     * @return \Jan\ZevirtBundle\Entity\DiskLogical 
     */
    public function getDiskB() {
        return $this->diskB;
    }

    public function __toString() {
        return '';
    }

    public function toXml() {
        $ret = "  
    <disk type='block' device='{$this->getDiskDevice()}'>
      <driver name='qemu' type='{$this->getDriverType()}' cache='none' />
      <source dev='{$this->getPath()}'/>
      <target dev='{$this->getTargetDev()}' bus='ide'/>
    </disk>
    
";

        return $ret;
    }

    /**
     * Set counter
     *
     * @param \Jan\ZevirtBundle\Entity\CounterDrbd $counter
     * @return DiskDrbd
     */
    public function setCounter(\Jan\ZevirtBundle\Entity\CounterDrbd $counter = null) {
        $this->counter = $counter;

        return $this;
    }

    /**
     * Get counter
     *
     * @return \Jan\ZevirtBundle\Entity\CounterDrbd 
     */
    public function getCounter() {
        return $this->counter;
    }

    public function getResourceName() {
        return 'r' . $this->getCounter()->getValue();
    }

    public function getPort() {
        return 7000 + $this->getCounter()->getValue();
    }

    public function getDevice() {
        return '/dev/drbd' . $this->getCounter()->getValue();
    }

    /**
     * Set nodeB
     *
     * @param \Jan\ZevirtBundle\Entity\Node $nodeB
     * @return DiskDrbd
     */
    public function setNodeB(\Jan\ZevirtBundle\Entity\Node $nodeB = null) {
        $this->nodeB = $nodeB;

        return $this;
    }

    /**
     * Get nodeB
     *
     * @return \Jan\ZevirtBundle\Entity\Node 
     */
    public function getNodeB() {
        return $this->nodeB;
    }

    /**
     * Set nodeA
     *
     * @param \Jan\ZevirtBundle\Entity\Node $nodeA
     * @return DiskDrbd
     */
    public function setNodeA(\Jan\ZevirtBundle\Entity\Node $nodeA = null) {
        $this->nodeA = $nodeA;

        return $this;
    }

    /**
     * Get nodeA
     *
     * @return \Jan\ZevirtBundle\Entity\Node 
     */
    public function getNodeA() {
        return $this->nodeA;
    }

    public function getRealName() {
        return 'drbd_' . $this->getCounter()->getValue();
    }

}