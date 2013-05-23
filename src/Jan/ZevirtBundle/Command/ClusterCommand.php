<?php

namespace Jan\ZevirtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jan\ZevirtBundle\Entity\Cluster;

class ClusterCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand {

    private $out;

    protected function configure() {


        $this
                ->setName('zevirt:cluster')
                ->setDescription('Setup or deletes a cluster')
                ->addArgument(
                        'action', InputArgument::REQUIRED, 'persist/remove'
                )
                ->addArgument(
                        'id', InputArgument::REQUIRED, 'Cluster ID'
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getContainer()->get('control.service')->setCli(true);
        $this->out = $output;

        $em = $this->getContainer()->get('doctrine')->getManager();

        $action = strtolower($input->getArgument('action'));
        $id = $input->getArgument('id');


        $cluster = $em->getRepository('JanZevirtBundle:Cluster')->findOneById($id);

        if (count($cluster)) {
            switch ($action) {
                case 'persist':
                    return $this->persist($cluster);
                    break;
                case 'remove':
                    return $this->remove($cluster);
                    break;

                default:
                    throw new \Exception('Action ' . $action . ' doesn\'t exist');
                    break;
            }
        } else {
            throw new \Exception("Entity with this ID doesn't exist");
        }
    }

    private function persist(Cluster $cluster) {
        $this->getContainer()->get('cluster.service')->persist($cluster);
    }

    private function remove(Cluster $cluster) {
        $this->getContainer()->get('cluster.service')->remove($cluster);
    }

}
