<?php

namespace Jan\ZevirtBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Jan\ZevirtBundle\Entity\Disk;
use Jan\ZevirtBundle\Entity\DiskLogical;
use Jan\ZevirtBundle\Entity\DiskDrbd;
use Jan\ZevirtBundle\Entity\DiskRbd;

class DiskService {

    private $em;
    protected $container;

    public function __construct(ContainerInterface $container, $em) {
        $this->em = $em;
        $this->container = $container;
    }

    public function persist(Disk $disk) {
        $this->em->persist($disk);
        $this->em->flush();

        if ($this->container->get('control.service')->isCli()) {
            $this->persistTasks($disk);
        } else {
            $this->container->get('job.service')->diskPersist($disk);
        }
    }

    public function remove(Disk $disk) {
        if ($this->container->get('control.service')->isCli()) {
            $this->removeTasks($disk);
        } else {
            $this->container->get('job.service')->diskRemove($disk);
        }
    }

    public function persistTasks(Disk $disk) {
        $out = $this->container->get('control.service')->getOutputWriter();
        if (!$disk->getId()) {
            throw new \Exception('Persist entity before saving configuration to get an id');
        }

        if ($disk instanceof DiskLogical) {
            $this->persistLogical($disk);
        }

        if ($disk instanceof DiskDrbd) {
            $this->persistDrbd($disk);
        }

        if ($disk instanceof DiskRbd) {
            $this->persistRbd($disk);
        }

        foreach ($disk->getVirtualmachine() as $vm) {
            $this->container->get('virtualmachine.service')->persist($vm);
        }
    }

    public function removeTasks(Disk $disk) {
        if ($disk instanceof DiskLogical) {
            $this->removeLogical($disk);
        }

        if ($disk instanceof DiskDrbd) {
            $this->removeDrbd($disk);
        }

        if ($disk instanceof DiskRbd) {
            $this->removeRbd($disk);
        }

        $this->em->remove($disk);
        $this->em->flush();

        foreach ($disk->getVirtualmachine() as $vm) {
            $this->container->get('virtualmachine.service')->persist($vm);
        }
    }

    private function persistLogical(DiskLogical $disk) {
        $out = $this->container->get('control.service');

        if (!$disk->getCreated()) {
            $size = (int) $disk->getCapacity();
            try {
                $cmd = "virsh vol-create-as {$disk->getStorage()->getRealName()} vmdisk_" . $disk->getId() . " {$size}";
                $out->writeln($disk->getNode(), $cmd);
                $this->container->get('node.service')->getConnection($disk->getNode())->exec($cmd);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }


            $path = $disk->getStorage()->getPath() . "/vmdisk_{$disk->getId()}";
            $path = str_replace('//', '/', $path);

            $disk->setPath($path);
            $disk->setDriverType('raw');
            $disk->setCreated(1);
            $this->em->persist($disk);
            $this->em->flush();
        }
    }

    private function persistRbd(DiskRbd $disk) {
        $out = $this->container->get('control.service');

        if (!$disk->getCreated()) {
            $size = (int) $disk->getCapacity();
            try {
                $cmd = "qemu-img create -f rbd rbd:{$disk->getStorage()->getPool()}/vmdisk_{$disk->getId()} $size";

                $out->writeln($disk->getNode(), $cmd);
                $this->container->get('node.service')->getConnection($disk->getNode())->exec($cmd);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }




            $disk->setPath("vmdisk_{$disk->getId()}");
            $disk->setName("vmdisk_{$disk->getId()}");
            $disk->setDriverType('raw');
            $disk->setCreated(1);
            $this->em->persist($disk);
            $this->em->flush();
        }
    }

    private function removeRbd(DiskRbd $disk) {


        $out = $this->container->get('control.service');
        $cmd = "rbd rm {$disk->getName()} -p {$disk->getStorage()->getPool()}";

        try {
            $out->writeln($disk->getNode(), $cmd);
            $this->container->get('node.service')->getConnection($disk->getNode())->exec($cmd);
        } catch (\Exception $e) {
            
        }

        try {
            $ret = $this->container->get('node.service')->getConnection($disk->getNode())->exec("qemu-img info rbd:{$disk->getStorage()->getPool()}/vmdisk_{$disk->getId()}");
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'No such file or directory') === false) {
                throw new \Exception('Remove of Disk failed. File ' . $disk->getPath() . ' still exists');
            }
        }
    }

    private function removeLogical(DiskLogical $disk) {


        $out = $this->container->get('control.service');
        $cmd = "virsh vol-delete vmdisk_{$disk->getId()} --pool {$disk->getStorage()->getRealName()}";

        try {
            $out->writeln($disk->getNode(), $cmd);
            $this->container->get('node.service')->getConnection($disk->getNode())->exec($cmd);
        } catch (\Exception $e) {
            
        }

        if ($this->container->get('node.service')->getConnection($disk->getNode())->fileExists($disk->getPath())) {
            throw new \Exception('Remove of Disk failed. File ' . $disk->getPath() . ' still exists');
        }
    }

    private function persistDrbd(DiskDrbd $disk) {
        $this->container->get('drbd.service')->persist($disk);
    }

    private function removeDrbd(DiskDrbd $disk) {
        $this->container->get('drbd.service')->remove($disk);
    }

}

?>
