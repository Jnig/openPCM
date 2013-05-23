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
class DiskFile extends Disk {

    private $entity = 'storagedir';

    public function toXml() {

        $ret = "  
    <disk type='file' device='{$this->getDiskDevice()}'>
      <driver name='qemu' type='{$this->getDriverType()}'/>
      <source file='{$this->getPath()}'/>
      <target dev='{$this->getTargetDev()}' bus='ide'/>
    </disk>
    
";

        return $ret;
    }

}