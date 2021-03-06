<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Jan\ZevirtBundle\Entity\Cluster
 *
 * @ORM\Table()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity
 */
class Cluster {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Node", mappedBy="cluster")
     * */
    private $nodes;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ha = false;

    /**
     * @ORM\ManyToMany(targetEntity="Storage")
     * */
    protected $storages;

    /**
     * @ORM\ManyToMany(targetEntity="Network")
     * */
    private $networks;

    /**
     * Add storages
     *
     * @param Jan\ZevirtBundle\Entity\Storage $storages
     * @return Node
     */
    public function addStorage(\Jan\ZevirtBundle\Entity\Storage $storages) {

        $this->storages[] = $storages;

        foreach ($this->getNodes() as $node) {
            $node->addStorage($storages);
        }

        return $this;
    }

    /**
     * Remove storages
     *
     * @param Jan\ZevirtBundle\Entity\Storage $storages
     */
    public function removeStorage(\Jan\ZevirtBundle\Entity\Storage $storages) {
        $this->storages->removeElement($storages);

        foreach ($this->getNodes() as $node) {
            $node->removeStorage($storages);
        }
    }

    /**
     * Get storages
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getStorages() {

        return $this->storages;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Cluster
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    public function toArray() {





        $array = array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'ha' => $this->getHa(),
            'storages' => $this->storagesToArray(),
            'networks' => $this->networksToArray()
        );

        return $array;
    }

    public function networksToArray() {
        $networks = array();
        if (count($this->getNetworks())) {
            foreach ($this->getNetworks() as $network) {
                $networks[] = $network->getId();
            }
        }
        return $networks;
    }

    public function storagesToArray() {
        $storages = array();
        if (count($this->getStorages())) {
            foreach ($this->getStorages() as $storage) {
                $storages[] = $storage->getId();
            }
        }

        return $storages;
    }

    public function __toString() {
        return $this->getName();
    }

    /**
     * Add nodes
     *
     * @param \Jan\ZevirtBundle\Entity\Node $nodes
     * @return Cluster
     */
    public function addNode(\Jan\ZevirtBundle\Entity\Node $nodes) {
        $this->nodes[] = $nodes;

        return $this;
    }

    /**
     * Remove nodes
     *
     * @param \Jan\ZevirtBundle\Entity\Node $nodes
     */
    public function removeNode(\Jan\ZevirtBundle\Entity\Node $nodes) {
        $this->nodes->removeElement($nodes);
    }

    /**
     * Get nodes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNodes() {
        return $this->nodes;
    }

    /**
     * Set ha
     *
     * @param boolean $ha
     * @return Cluster
     */
    public function setHa($ha) {


        $this->ha = $ha;



        return $this;
    }

    /**
     * Get ha
     *
     * @return boolean 
     */
    public function getHa() {

        return $this->ha;
    }

    public function isHa() {
        return $this->ha;
    }

    /**
     * Add networks
     *
     * @param \Jan\ZevirtBundle\Entity\Network $networks
     * @return Cluster
     */
    public function addNetwork(\Jan\ZevirtBundle\Entity\Network $networks) {
        $this->networks[] = $networks;

        return $this;
    }

    /**
     * Remove networks
     *
     * @param \Jan\ZevirtBundle\Entity\Network $networks
     */
    public function removeNetwork(\Jan\ZevirtBundle\Entity\Network $networks) {
        $this->networks->removeElement($networks);
    }

    /**
     * Get networks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNetworks() {
        return $this->networks;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->nodes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->storages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->networks = new \Doctrine\Common\Collections\ArrayCollection();
    }

}