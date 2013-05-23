<?php

namespace Jan\ZevirtBundle\Service;

use Jan\ZevirtBundle\Entity\DiskLogical;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Jan\ZevirtBundle\Entity\DiskDrbd;
use Jan\ZevirtBundle\Entity\Node;
use Symfony\Component\Console\Output\ConsoleOutput;

class ControlService {

    private $em;
    protected $container;
    private $output;
    private $isCli = false;

    public function __construct(ContainerInterface $container, $em) {
        $this->em = $em;
        $this->container = $container;
    }

    public function isCli() {
        return $this->isCli;
    }

    public function setCli($value = true) {
        $this->isCli = $value;
    }

    public function getOutputWriter() {
        if ($this->output == null) {
            $this->output = new ConsoleOutput();
        }
        return $this->output;
    }

    public function setOutputWriter(\Symfony\Component\Console\Output\OutputInterface $out) {
        $this->output = $out;
    }

    public function writeln($node, $command = '', $output = '') {
        if ($node instanceof Node) {

            $this->getOutputWriter()->writeln("<b>{$node->getName()}\$</b> $command");
        } else {

            $this->getOutputWriter()->writeln($node);
        }

        if (!empty($output)) {
            $this->getOutputWriter()->writeln($output);
        }
    }

    public function write($out) {
        if (is_string($out)) {
            $this->getOutputWriter()->write($out);
        }
    }

}

?>
