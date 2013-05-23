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
class DiskRbd extends Disk {

    public function toXml() {
        $ret = "  
    <disk type='network' device='disk'>
      <driver name='qemu' type='{$this->getDriverType()}' cache='writeback' />
      <source protocol='rbd' name='{$this->getStorage()->getPool()}/{$this->getPath()}'>
          {$this->getStorage()->getHostsXml()}
      </source>
      {$this->getStorage()->getAuthXml()}
      <target dev='{$this->getTargetDev()}' bus='virtio'/>
    </disk>
    
";

        return $ret;
    }

}