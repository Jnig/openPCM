<?php

namespace Jan\ZevirtBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Jan\ZevirtBundle\Entity\Node;
use Jan\ZevirtBundle\Form\NodeType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;

class NodeController extends Controller {

    public function postNodesAction() {
        $entity = new Node;
        return $this->processForm($entity);
    }

    private function processForm(Node $entity) {
        $form = $this->createForm(new NodeType, $entity);

        $form->bind($this->getRequest());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();

            $this->get('node.service')->getConnection($entity);


            $entity->setPassword('');


            $this->get('node.service')->persist($entity);



            $view = View::create();
            return $view->setData($entity->toArray());
        } else {
            return View::create($form, 400);
        }
    }

    public function putNodesAction(Node $entity) {
        return $this->processForm($entity);
    }

    /**
     * @Rest\View
     */
    public function getNodesAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('JanZevirtBundle:Node')->findAll();

        $array = array();
        foreach ($entities as $entity) {
            $array[] = $entity->toArray();
        }

        return $array;
    }

    /**
     * @Rest\View()
     */
    public function deleteNodesAction(Node $entity) {
        $em = $this->getDoctrine()->getManager();
        if (count($entity->getVirtualmachines()) == 0) {
            $em->remove($entity);
            $em->flush();
        } else {
            return array('error' => 'Node has Virtualmachines associated to it');
        }
    }

    /**
     * @Rest\View
     */
    public function getNodesFilesAction(Node $node, $path) {
        /*
         * 
         *         if(isset($_GET['id']) && !empty($_GET['id'])) {
          $path = $_GET['id'];
          } else {
          $path = '/';
          }
          $path = escapeshellarg($path);

          $model = new Default_Model_Vms;
          $row = $model->findById($this->_getParam('id'))->current();


          $tree = $row->getSsh()->exec('cd '.$path.' && ls -F --file-type');
          $treeArray = explode("\n", $tree);

          $path = str_replace("'", "", $path);

          $jsonArray = array();
          foreach ($treeArray as $piece) {
          if ($piece == '..' || $piece == '..' || empty($piece)) break;
          $file = array(
          'data' => $piece,
          'attr' => array('id' => $path.$piece,
          'title' => $path.$piece,
          'class' => 'treeFile'),

          );

          if ($piece[strlen($piece)-1] == '/') {
          $file['state'] = 'closed';
          }


          $jsonArray[] = $file;
          }

         * 
         * 
         */


        if ($path == 'root') {
            $path = '/';
            $add = '';
        } else {
            $add = '/';
            $path = str_replace('|', '/', $path);
        }




        $tree = $node->getConnection()->exec('ls -F --file-type /' . escapeshellarg($path));
        $treeArray = explode("\n", $tree);

        $path = str_replace("'", "", $path);

        $jsonArray = array();
        foreach ($treeArray as $piece) {
            if ($piece == '..' || $piece == '..' || empty($piece))
                break;
            $file = array(
                'text' => $piece,
                'id' => str_replace('/', '|', '/' . $path . '/' . $piece),
                'path' => str_replace('@', '', realpath('/' . $path . '/') . $add . $piece)
            );

            if ($piece[strlen($piece) - 1] != '/') {
                $file['leaf'] = true;
            }


            $jsonArray[] = $file;
        }

        return $jsonArray;

        $menu = array(
            array('text' => 'Configuration',
                'expanded' => 'true',
                'children' => array(
                    array('text' => 'User', 'leaf' => true, 'id' => 'userList'),
                    array('text' => 'Virtual Machines', 'leaf' => true, 'id' => 'vmlist'),
                    array('text' => 'Clusters', 'leaf' => true, 'id' => 'clusterlist'),
                    array('text' => 'Nodes', 'leaf' => true, 'id' => 'nodelist'),
                    array('text' => 'Storage', 'leaf' => true, 'id' => 'storagelist'),
                    array('text' => 'Network', 'leaf' => true, 'id' => 'networklist'),
                )
            ),
            array('text' => 'Clusters', 'expanded' => 'false', 'children' => $clusters)
        );
    }

    /**
     * @Rest\View
     */
    public function getNodesDrbdAction(\Jan\ZevirtBundle\Entity\VirtualMachine $vm) {
        $entities = $vm->getNode()->getCluster()->getNodes();

        $ret = array();
        foreach ($entities as $entity) {
            $ret[] = $entity->toArray();
        }

        return $ret;
    }

    /**
     * @Rest\View
     */
    public function getNodesInterfacesAction(Node $node) {
        return $this->get('node.service')->getNodeInterfaces($node);
    }

    /**
     * @Rest\View
     */
    public function getNodesStonithsAction(Node $node) {
        return $this->get('node.service')->getStonithDevices($node);
    }

    /**
     * @Rest\View
     */
    public function getNodesStonithsMetasAction(Node $node, $stonithDevice) {
        $stonithDevice = str_replace('|', '/', $stonithDevice);
        return $this->get('node.service')->getStonithDevicesMetadata($node, $stonithDevice);
    }

    /**
     * @Rest\View
     */
    public function getNodesStandbyOnAction(Node $node) {
        $this->get('job.service')->nodeStandbyOn($node);
        return array();
    }

    /**
     * @Rest\View
     */
    public function getNodesStandbyOffAction(Node $node) {
        $this->get('job.service')->nodeStandbyOff($node);
        return array();
    }

}
