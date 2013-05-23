<?php

namespace Jan\ZevirtBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jan\ZevirtBundle\Entity\VirtualMachine;
use Jan\ZevirtBundle\Form\VirtualMachineType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;

class RestController extends Controller {

    /**
     * @Rest\View
     */
    public function getMenuAction($node) {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return array();
        }

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('JanZevirtBundle:Cluster')->findAll();

        $clusters = array();
        foreach ($entities as $cluster) {

            $nodes = array();
            foreach ($cluster->getNodes() as $node) {

                $vms = array();
                foreach ($node->getVirtualMachines() as $vm) {
                    $vms[] = array('action' => 'vmedit', 'clusterId' => $cluster->getId(), 'vmId' => $vm->getId(), 'text' => $vm->getName(), 'iconCls' => 'icon-vm', 'leaf' => true);
                }
                $nodes[] = array('action' => 'nodelist', 'clusterId' => $cluster->getId(), 'nodeId' => $node->getId(), 'text' => $node->getName(), 'iconCls' => 'icon-node', 'children' => $vms, 'allowDrag' => false);
            }

            $clusters[] = array('action' => 'clusterlist', 'text' => $cluster->getName(), 'iconCls' => 'icon-cluster', 'children' => $nodes, 'expanded' => 'true', 'allowDrag' => false, 'allowDrop' => false);
        }

        $menu = array(
            array('text' => 'Configuration',
                'expanded' => 'true',
                'allowDrag' => false,
                'allowDrop' => false,
                'children' => array(
                    array('text' => 'User', 'leaf' => true, 'action' => 'userlist', 'iconCls' => 'icon-user', 'allowDrag' => false, 'allowDrop' => false),
                    array('text' => 'Clusters', 'leaf' => true, 'action' => 'clusterlist', 'iconCls' => 'icon-cluster', 'allowDrag' => false, 'allowDrop' => false),
                    array('text' => 'Nodes', 'leaf' => true, 'action' => 'nodelist', 'iconCls' => 'icon-node', 'allowDrag' => false, 'allowDrop' => false),
                    array('text' => 'Virtual Machines', 'leaf' => true, 'action' => 'vmlist', 'iconCls' => 'icon-vm', 'allowDrag' => false, 'allowDrop' => false),
                    array('text' => 'Storage', 'leaf' => true, 'action' => 'storagelist', 'iconCls' => 'icon-drive', 'allowDrag' => false, 'allowDrop' => false),
                    array('text' => 'Network', 'leaf' => true, 'action' => 'networklist', 'iconCls' => 'icon-network', 'allowDrag' => false, 'allowDrop' => false)
                )
            ),
            array('text' => 'Datacenter', 'allowDrop' => false, 'allowDrag' => false, 'expanded' => 'true', 'children' => $clusters,)
        );



        return $menu;
    }

    /**
     * @Rest\View
     */
    public function getEventsAction(Request $request) {
        $em = $this->getDoctrine()->getManager();



        $query = $em->createQuery(
                        'SELECT p FROM JanZevirtBundle:Event p WHERE p.id > :id'
                )->setParameter('id', $request->query->get('id'));
        $entities = $query->getResult();

        $ar = array();

        foreach ($entities as $entity) {
            $singleEntityArray = array('id' => $entity->getId(), 'action' => $entity->getAction(), 'entity' => $entity->getEntity(), 'entityId' => $entity->getEntityId());
            if ($entity->getEntityId()) {
                $repository = 'JanZevirtBundle:' . $entity->getEntity();

                $changedEntity = $em->getRepository($repository)->findOneById($entity->getEntityId());

                $singleEntityArray['data'] = $changedEntity->toArray();
            }

            $ar[] = $singleEntityArray;
        }

        return $ar;
    }

    /**
     * @Rest\View
     */
    public function getEventsLastAction() {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('JanZevirtBundle:Event')->findOneBy(array(), array('id' => 'desc'), 1);

        if (count($entity)) {
            return array('id' => $entity->getId());
        } else {
            return array('id' => 1);
        }
    }

}
