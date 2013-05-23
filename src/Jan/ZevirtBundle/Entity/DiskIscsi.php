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
class DiskIscsi extends Disk {

    public function toXml() {

        $ret = "  
    <disk type='block' device='disk'>
      <driver name='qemu' type='raw'/>
      <source dev='{$this->getPath()}'/>
      <target dev='hda' bus='ide'/>
    </disk>
    
";

        return $ret;
    }

}