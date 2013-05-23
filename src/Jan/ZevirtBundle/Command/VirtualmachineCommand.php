<?php

namespace Jan\ZevirtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jan\ZevirtBundle\Entity\Node;
use Jan\ZevirtBundle\Entity\VirtualMachine;

class VirtualmachineCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand {

    private $out;

    protected function configure() {


        $this
                ->setName('zevirt:vm')
                ->setDescription('Start/Stop/Reset/Destroy/persist/update/remove Virtualmachines')
                ->addArgument(
                        'action', InputArgument::REQUIRED, 'start/shutown/reset/destroy/migrate'
                )
                ->addArgument(
                        'virtualmachineId', InputArgument::REQUIRED, 'Virtualmachine ID'
                )
                ->addArgument(
                        'nodeId', InputArgument::OPTIONAL, 'Node Id'
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getContainer()->get('control.service')->setCli(true);
        $this->out = $output;
        $em = $this->getContainer()->get('doctrine')->getManager();
        $action = strtolower($input->getArgument('action'));
        $vmId = $input->getArgument('virtualmachineId');
        $nodeId = $input->getArgument('nodeId');

        $vm = $em->getRepository('JanZevirtBundle:VirtualMachine')->findOneById($vmId);

        if ($nodeId) {
            $node = $em->getRepository('JanZevirtBundle:Node')->findOneById($nodeId);
        }




        if (count($vm)) {
            switch ($action) {
                case 'start':
                    return $this->start($vm);
                    break;
                case 'shutdown':
                    return $this->shutdown($vm);
                    break;
                case 'reset':
                    return $this->reset($vm);
                    break;
                case 'destroy':
                    return $this->destroy($vm);
                    break;
                case 'update':
                    return $this->update($vm);
                    break;
                case 'remove':
                    return $this->remove($vm);
                    break;
                case 'persist':
                    return $this->persist($vm);
                    break;
                case 'migrate';
                    return $this->migrate($vm, $node);
                default:
                    throw new \Exception('Action ' . $action . ' doesn\'t exist');
                    break;
            }
        } else {
            throw new \Exception('Virtualmachine with ID ' . $vmId . ' not found');
        }
    }

    private function start(VirtualMachine $vm) {
        $vmService = $this->getContainer()->get('virtualmachine.service');

        $vmService->actionStart($vm);

        $this->out->writeln('Waiting up to 2 minutes for virtualmachine to start');
        for ($i = 0; $i <= (2 * 60); $i++) {
            if (!$vmService->isRunning($vm)) {
                $this->out->write('.');
            } else {
                $this->out->writeln('');
                $this->out->writeln('Virtualmachine ' . $vm->getName() . ' started successful');
                return 0;
            }
            sleep(1);
        }
        $this->out->writeln('Virtualmachine ' . $vm->getName() . ' is not started, but start command didn\'t return an error');

        return 1;
    }

    private function shutdown(VirtualMachine $vm) {
        $vmService = $this->getContainer()->get('virtualmachine.service');

        $vmService->actionShutdown($vm);

        $this->out->writeln('Waiting up to 5 minutes for virtualmachine to shutdown');
        for ($i = 0; $i <= (5 * 60); $i++) {
            if ($vmService->isRunning($vm)) {
                $this->out->write('.');
            } else {
                $this->out->writeln('');
                $this->out->writeln('Shutdown successful');
                return 0;
            }
            sleep(1);
        }

        $this->out->writeln('Virtualmachine didn\'t shutdown');
        return 1;
    }

    private function reset(VirtualMachine $vm) {
        if ($this->destroy($vm)) {
            $this->out->writeln('Virtualmachine stop failed');
            return 1;
        }

        if ($this->start($vm)) {
            $this->out->writeln('Virtualmachine start failed');
            return 1;
        }

        return 0;
    }

    private function destroy(VirtualMachine $vm) {

        $vmService = $this->getContainer()->get('virtualmachine.service');

        $vmService->actionDestroy($vm);


        $this->out->writeln('Waiting up to 2 minutes for virtualmachine to stop');
        for ($i = 0; $i <= (1 * 60); $i++) {
            if ($vmService->isRunning($vm)) {
                $this->out->write('.');
            } else {
                $this->out->writeln('');
                $this->out->writeln('Stop successful');
                return 0;
            }
            sleep(1);
        }

        $this->out->writeln('Virtualmachine didn\'t stop');
        return 1;
    }

    private function remove(VirtualMachine $vm) {
        $this->getContainer()->get('virtualmachine.service')->removeTasks($vm);
    }

    private function persist(VirtualMachine $vm) {
        $this->getContainer()->get('virtualmachine.service')->persistTasks($vm);
    }

    private function migrate(VirtualMachine $vm, Node $node) {
        $this->getContainer()->get('virtualmachine.service')->actionMigrate($vm, $node);
    }

}
