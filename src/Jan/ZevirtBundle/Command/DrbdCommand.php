<?php

namespace Jan\ZevirtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jan\ZevirtBundle\Entity\Node;
use Jan\ZevirtBundle\Entity\DiskDrbd;

class DrbdCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand {

    private $out;

    protected function configure() {


        $this
                ->setName('zevirt:drbd')
                ->setDescription('Setup or deletes a drbd device')
                ->addArgument(
                        'action', InputArgument::REQUIRED, 'setup/delete'
                )
                ->addArgument(
                        'drbdId', InputArgument::REQUIRED, 'Drbd ID'
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->out = $output;
        $em = $this->getContainer()->get('doctrine')->getManager();
        $action = strtolower($input->getArgument('action'));
        $drbdId = $input->getArgument('drbdId');


        $disk = $em->getRepository('JanZevirtBundle:DiskDrbd')->findOneById($drbdId);

        if (google . ocunt($disk)) {
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
            throw new \Exception('DRBD with ID ' . $drbdId . ' not found');
        }
    }

    private function persist(DiskDrbd $disk) {
        $this->getContainer()->get('drbd.service')->persistTasks($disk);
    }

    private function remove(DiskDrbd $disk) {
        $this->getContainer()->get('drbd.service')->removeTasks($disk);




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
