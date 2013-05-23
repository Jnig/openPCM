<?php

namespace Jan\ZevirtBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Jan\ZevirtBundle\Entity\Disk;
use Jan\ZevirtBundle\Entity\DiskLogical;
use Jan\ZevirtBundle\Entity\Node;
use Jan\ZevirtBundle\Entity\Storage;
use Jan\ZevirtBundle\Entity\StorageDir;
use Jan\ZevirtBundle\Entity\StorageIscsi;
use Jan\ZevirtBundle\Entity\StorageLogical;
use Jan\ZevirtBundle\Entity\StorageNetfs;
use Jan\ZevirtBundle\Entity\StorageRbd;

class StorageService {

    private $em;
    protected $container;

    public function __construct(ContainerInterface $container, $em) {
        $this->em = $em;
        $this->container = $container;
    }

    /*  public function parseVirshVolInfo($text) {

      $return = array();

      $text = preg_replace('/[ ]+/', ' ', $text);
      $array = explode("\n", $text);
      if (count($array) > 4) {
      throw new Exception('unsupported virsh vol-info output');
      } else {
      $row = explode(" ", $array[0]);

      $return['name'] = $row[1];

      $row = explode(" ", $array[2], 2);

      $return['capacity'] = $this->convertSizesToGb($row[1]);

      $row = explode(" ", $array[3], 2);
      $return['allocation'] = $this->convertSizesToGb($row[1]);
      }

      return $return;
      }

      public function convertSizesToGb($size) {
      list($size, $unit) = explode(" ", $size);
      if ($unit == 'GB') {
      return $size;
      } elseif ($unit == 'MB') {
      return round($size/1024, 2);
      } elseif ($unit == 'KB') {
      return round($size/1024/1024, 2);
      } else {
      throw new Exception('Unknown Unit '.$unit.' in converting volume size to GB');
      }
      } */

    public function persist(Disk $disk) {
        $this->em->persist($disk);
        $this->em->flush($disk);

        if ($this->container->get('control.service')->isCli()) {
            $this->persistTasks($disk);
        } else {
            $this->container->get('job.service')->drbdPersist($disk);
        }
    }

    public function remove(Disk $disk) {
        if ($this->container->get('control.service')->isCli()) {
            $this->removeTasks($disk);
        } else {
            $this->container->get('job.service')->drbdRemove($disk);
        }
    }

    private function persistTasks(Disk $disk) {
        $out = $this->container->get('control.service')->getOutputWriter();
        if (!$disk->getId()) {
            throw new \Exception('Persist entity before saving configuration to get an id');
        }

        if ($disk instanceof DiskLogical) {
            $this->persistLogical($disk);
        }

        foreach ($disk->getVirtualmachine() as $vm) {
            $this->container->get('virtualmachine.service')->persist($vm);
        }
    }

    public function getStorageVolumes(Storage $storage, Node $node) {
        if ($storage instanceof StorageRbd) {
            return $this->getStorageVolumesRbd($storage, $node);
        }
        $em = $this->em;
        $return = array();
        $nodes = $storage->getNodes();

        $connection = $this->container->get('node.service')->getConnection($node);

        $connection->exec('virsh pool-refresh ' . escapeshellarg($storage->getRealName()) . ' | sed "1,2d"');
        $out = $connection->exec('virsh vol-list ' . escapeshellarg($storage->getRealName()) . ' | sed "1,2d"');

        $out = preg_replace('/[ ]+/', ' ', $out);

        $array = explode("\n", $out);

        foreach ($array as $row) {
            list($name, $path) = explode(' ', $row);
            $out = $connection->exec('virsh vol-dumpxml ' . escapeshellarg($name) . ' --pool ' . escapeshellarg($storage->getName()));


            $disk = $em->getRepository('JanZevirtBundle:Disk')->findOneBy(array('path' => $path, 'node' => $node));

            if (!count($disk)) {
                $disk = $storage->getDiskEntity();
            }

            $disk = $this->volumeXmlToDiskEntity($out, $disk);

            $out = $connection->exec('qemu-img info ' . escapeshellarg($path));

            preg_match('/file format: (.*)/', $out, $matches);

            $disk->setDriverType($matches[1]);

            $disk->setStorage($storage);
            $disk->setNode($node);

            $em->persist($disk);
        }


        $em->flush();
    }

    public function getStorageVolumesRbd(StorageRbd $storage, Node $node) {
        $em = $this->em;
        $connection = $this->container->get('node.service')->getConnection($node);
        $out = $connection->exec('rbd -p ' . escapeshellarg($storage->getPool()) . ' ls');
        $array = explode("\n", $out);

        foreach ($array as $row) {
            $disk = $em->getRepository('JanZevirtBundle:Disk')->findOneBy(array('name' => $row));
            if (!count($disk)) {
                $disk = $storage->getDiskEntity();
            }

            $disk->setName($row);
            $disk->setPath($row);
            $disk->setNode($node);
            $out = $connection->exec('qemu-img info rbd:' . escapeshellarg($storage->getPool()) . '/' . escapeshellarg($row));
            preg_match('/file format: (.*)/', $out, $matches);

            $disk->setDriverType($matches[1]);
            preg_match('/virtual size:.*\((\d+) bytes\)/', $out, $matches);
            $disk->setCapacity($matches[1]);
            $disk->setAllocation($matches[1]);

            $disk->setStorage($storage);


            $em->persist($disk);
        }

        $em->flush();
    }

    public function refreshNode(Node $node) {
        foreach ($node->getStorages() as $storage) {
            $this->getStorageVolumes($storage, $node);
        }
    }

    private function volumeXmlToDiskEntity($out, $disk) {
        $xml = new \SimpleXMLElement($out);

        $disk->setPath((string) $xml->target->path);

        $disk->setCapacity($xml->capacity);
        $disk->setAllocation($xml->allocation);

        $disk->setDriverType((string) $xml->target->format['type']);


        return $disk;
    }

}

?>
