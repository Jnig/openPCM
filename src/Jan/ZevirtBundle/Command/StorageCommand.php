<?php

namespace Jan\ZevirtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jan\ZevirtBundle\Entity\Node;
use Jan\ZevirtBundle\Entity\Disk;

class StorageCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand {

    private $out;

    protected function configure() {


        $this
                ->setName('zevirt:storage')
                ->setDescription('persist/remove storages OR refresh nodes')
                ->addArgument(
                        'action', InputArgument::REQUIRED, 'persist/remove'
                )
                ->addArgument(
                        'id', InputArgument::REQUIRED, 'storage or node ID'
                )

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getContainer()->get('control.service')->setCli(true);
        $this->out = $output;

        $em = $this->getContainer()->get('doctrine')->getManager();

        $action = strtolower($input->getArgument('action'));
        $id = $input->getArgument('id');


        switch ($action) {
            case 'refresh':
                $node = $em->getRepository('JanZevirtBundle:Node')->findOneById($id);
                $this->refresh($node);
                break;
            case 'persist':
                return $this->persist($node);
                break;
            case 'remove':
                return $this->remove($node);
                break;

            default:
                throw new \Exception('Action ' . $action . ' doesn\'t exist');
                break;
        }
    }

    public function refresh(Node $node) {
        $this->getContainer()->get('storage.service')->refreshNode($node);
    }

}
