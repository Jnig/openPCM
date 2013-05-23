<?php

namespace Jan\ZevirtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jan\ZevirtBundle\Entity\Node;
use Jan\ZevirtBundle\Entity\Disk;

class DiskCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand {

    private $out;

    protected function configure() {


        $this
                ->setName('zevirt:disk')
                ->setDescription('persist/remove disk')
                ->addArgument(
                        'action', InputArgument::REQUIRED, 'persist/remove'
                )
                ->addArgument(
                        'diskId', InputArgument::REQUIRED, 'disk ID'
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->out = $output;
        $em = $this->getContainer()->get('doctrine')->getManager();
        $action = strtolower($input->getArgument('action'));
        $id = $input->getArgument('diskId');


        $disk = $em->getRepository('JanZevirtBundle:Disk')->findOneById($id);
        $this->getContainer()->get('control.service')->setCli(true);

        if (count($disk)) {
            switch ($action) {
                case 'persist':
                    return $this->persist($disk);
                    break;
                case 'remove':
                    return $this->remove($disk);
                    break;

                default:
                    throw new \Exception('Action ' . $action . ' doesn\'t exist');
                    break;
            }
        } else {
            throw new \Exception('DRBD with ID ' . $id . ' not found');
        }
    }

    private function persist(Disk $disk) {

        $this->getContainer()->get('disk.service')->persist($disk);
    }

    private function remove(Disk $disk) {
        $this->getContainer()->get('disk.service')->remove($disk);
    }

}
