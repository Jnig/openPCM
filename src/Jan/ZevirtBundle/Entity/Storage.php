<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jan\ZevirtBundle\Model\iStorage;

/**
 * Jan\ZevirtBundle\Entity\Storage
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"StorageDir" = "StorageDir",
 *  "StorageLogical" = "StorageLogical",
 *  "StorageIscsi" = "StorageIscsi",
 *  "StorageNetfs" = "StorageNetfs",
 *  "StorageRbd" = "StorageRbd"
 * }) 
 */
abstract class Storage implements iStorage {

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
     * @var string $uuid
     *
     * @ORM\Column(name="uuid", type="string", length=255, nullable=true)
     */
    private $uuid;

    /**
     * @var string $state
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=true)
     */
    private $state;

    /**
     * @ORM\OneToMany(
     *                targetEntity="NodeStorage",
     *                mappedBy="storage",
     *                cascade={"persist", "remove"},
     *                orphanRemoval=true
     *                )
     */
    protected $nodes;

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
     * @return Storage
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

    /**
     * Set type
     *
     * @param string $type
     * @return Storage
     */
    public function setType($type) {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set uuid
     *
     * @param string $uuid
     * @return Storage
     */
    public function setUuid($uuid) {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string 
     */
    public function getUuid() {
        return $this->uuid;
    }

    /**
     * Set targetPath
     *
     * @param string $targetPath
     * @return Storage
     */
    public function setTargetPath($targetPath) {
        $this->targetPath = $targetPath;

        return $this;
    }

    /**
     * Get targetPath
     *
     * @return string 
     */
    public function getTargetPath() {
        return $this->targetPath;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Storage
     */
    public function setState($state) {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState() {
        return $this->state;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Storage
     */
    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath() {
        return $this->path;
    }

    public function __toString() {
        return $this->getName();
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->nodes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add nodes
     *
     * @param Jan\ZevirtBundle\Entity\Node $nodes
     * @return Storage
     */
    public function addNode(\Jan\ZevirtBundle\Entity\Node $nodes) {
        $rel = new NodeStorage($nodes, $this);
        $this->nodes[] = $rel;

        return $this;
    }

    /**
     * Remove nodes
     *
     * @param Jan\ZevirtBundle\Entity\Node $nodes
     */
    public function removeNode(\Jan\ZevirtBundle\Entity\Node $nodes) {

        foreach ($this->nodes as $rel) {
            if ($rel->getNode() === $nodes) {
                $this->nodes->removeElement($rel);
                $rel->getNode()->removeStorage($this);
            }
        }
    }

    /**
     * Get nodes
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getNodes() {
        $collection = new \Doctrine\Common\Collections\ArrayCollection();

        foreach ($this->nodes as $rel) {
            $collection->add($rel->getNode());
        }

        return $collection;
    }

    public function toArray() {

        $nodes = array();
        foreach ($this->getNodes() as $node) {
            $nodes[] = $node->getId();
        }

        return array('id' => $this->getId(),
            'name' => $this->getName(),
            'nodes' => $nodes,
            'uuid' => $this->getUuid(),
            'entity' => preg_replace('#^.*\\\#', '', get_class($this))
        );


        return $array;
    }

    public function getRealName() {
        return 'storage_' . $this->getId();
    }

}