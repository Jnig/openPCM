<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**

 * @ORM\Table()
 * @ORM\Entity
 */
class NodeStorage {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Node",inversedBy="storages",cascade={"persist"})
     * @ORM\JoinColumn(name="node_id", referencedColumnName="id", nullable=false)
     */
    private $node;

    /**
     * @ORM\ManyToOne(targetEntity="Storage",inversedBy="nodes",cascade={"persist"})
     * @ORM\JoinColumn(name="storage_id", referencedColumnName="id", nullable=false)
     */
    private $storage;

    /**
     *
     *
     * @ORM\Column(type="string", length=255)
     */
    private $state = "";

    public function __construct(Node $node, Storage $storage) {
        $this->node = $node;
        $this->storage = $storage;
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
     * Set state
     *
     * @param string $state
     * @return NodeStorage
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
     * Set node
     *
     * @param \Jan\ZevirtBundle\Entity\Node $node
     * @return NodeStorage
     */
    public function setNode(\Jan\ZevirtBundle\Entity\Node $node) {
        $this->node = $node;

        return $this;
    }

    /**
     * Get node
     *
     * @return \Jan\ZevirtBundle\Entity\Node
     */
    public function getNode() {
        return $this->node;
    }

    /**
     * Set storage
     *
     * @param \Jan\ZevirtBundle\Entity\Storage $storage
     * @return NodeStorage
     */
    public function setStorage(\Jan\ZevirtBundle\Entity\Storage $storage) {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Get storage
     *
     * @return \Jan\ZevirtBundle\Entity\Storage
     */
    public function getStorage() {
        return $this->storage;
    }

    public function __toString() {
        return "{$this->getId()}";
    }

}