<?php

namespace Jan\ZevirtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jan\ZevirtBundle\Entity\Node;

class NodeCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand {

    private $out;

    protected function configure() {


        $this
                ->setName('zevirt:node')
                ->setDescription('Setup or deletes as node')
                ->addArgument(
                        'action', InputArgument::REQUIRED, 'persist/remove'
                )
                ->addArgument(
                        'nodeId', InputArgument::REQUIRED, 'Node ID'
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getContainer()->get('control.service')->setCli(true);
        $this->out = $output;

        $em = $this->getContainer()->get('doctrine')->getManager();

        $action = strtolower($input->getArgument('action'));
        $id = $input->getArgument('nodeId');


        $node = $em->getRepository('JanZevirtBundle:Node')->findOneById($id);

        if (count($node)) {
            switch ($action) {
                case 'persist':
                    return $this->persist($node);
                    break;
                case 'remove':
                    return $this->remove($node);
                    break;
                case 'scan':
                    return $this->getContainer()->get('node.service')->scan($node);
                    break;
                case 'standbyon':
                    return $this->getContainer()->get('node.service')->setStandby($node, true);
                    break;
                case 'standbyoff':
                    return $this->getContainer()->get('node.service')->setStandby($node, false);
                    break;
                default:
                    throw new \Exception('Action ' . $action . ' doesn\'t exist');
                    break;
            }
        } else {
            throw new \Exception("Entity with this ID doesn't exist");
        }
    }

    private function persist(Node $node) {
        $this->getContainer()->get('node.service')->persist($node);
    }

    private function remove(Node $node) {
        $this->getContainer()->get('node.service')->remove($node);




        /* $connection = $this->getContainer()->get('node.service')->getConnection($vm->getNode());
          $vmService = $this->getContainer()->get('virtualmachine.service');

          if ($vm->isHa()) {
          $cmd = 'crm resource stop '.$vm->getRealName();
          } else {
          $cmd = 'virsh shutdown '.$vm->getRealName();
          }

          $this->out->writeln('Executing command '.$cmd);
          try {
          $ret = $connection->exec($cmd);
          } catch (\Exception $exc) {
          $this->out->writeln($exc->getMessage());
          return 1;
          }
          $this->out->writeln($ret);

          $this->out->writeln('Waiting up to 5 minutes for virtualmachine to shutdown');
          for($i = 0; $i <= (5*60); $i++) {
          if ($vmService->isRunning($vm)) {
          $this->out->write('.');
          } else {
          $this->out->writeln('');
          $this->out->writeln('Shutdown successful');
          return 0;
          }
          sleep (1);
          }

          $this->out->writeln('Virtualmachine didn\'t shutdown');
          return 1; */
    }

}
