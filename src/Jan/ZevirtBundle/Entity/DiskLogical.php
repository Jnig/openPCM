<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jan\ZevirtBundle\Entity\Disk;

/**
 * Jan\ZevirtBundle\Entity\Storage
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class DiskLogical extends Disk {

    private $entity = 'storagelogical';

    public function __toString() {
        return '';
    }

    public function toXml() {
        $ret = "  
    <disk type='block' device='{$this->getDiskDevice()}'>
      <driver name='qemu' type='{$this->getDriverType()}'/>
      <source dev='{$this->getPath()}'/>
      <target dev='{$this->getTargetDev()}' bus='ide'/>
    </disk>
    
";

        return $ret;
    }

}