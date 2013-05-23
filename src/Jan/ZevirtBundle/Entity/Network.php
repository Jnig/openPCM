<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Jan\ZevirtBundle\Entity\Network
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Network {

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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $forwardMode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bridgeName;

    /**
     * @ORM\ManyToMany(targetEntity="Node")
     */
    private $node;

    /**
     * 
     * @ORM\ManyToMany(targetEntity="Node", mappedBy="networks")
     */
    private $nodes;

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
     * @return Network
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
     * Constructor
     */
    public function __construct() {
        $this->node = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set forwardMode
     *
     * @param string $forwardMode
     * @return Network
     */
    public function setForwardMode($forwardMode) {
        $this->forwardMode = $forwardMode;

        return $this;
    }

    /**
     * Get forwardMode
     *
     * @return string 
     */
    public function getForwardMode() {
        return $this->forwardMode;
    }

    /**
     * Set bridgeName
     *
     * @param string $bridgeName
     * @return Network
     */
    public function setBridgeName($bridgeName) {
        $this->bridgeName = $bridgeName;

        return $this;
    }

    /**
     * Get bridgeName
     *
     * @return string 
     */
    public function getBridgeName() {
        return $this->bridgeName;
    }

    /**
     * Add node
     *
     * @param Jan\ZevirtBundle\Entity\Node $node
     * @return Network
     */
    public function addNode(\Jan\ZevirtBundle\Entity\Node $node) {
        $this->node[] = $node;

        return $this;
    }

    /**
     * Remove node
     *
     * @param Jan\ZevirtBundle\Entity\Node $node
     */
    public function removeNode(\Jan\ZevirtBundle\Entity\Node $node) {
        $this->node->removeElement($node);
    }

    /**
     * Get node
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getNode() {
        return $this->node;
    }

    public function toArray() {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'bridgeName' => $this->getBridgeName(),
            'forwardMode' => $this->getForwardMode()
        );
    }

    public function __toString() {
        return $this->getName();
    }

    /**
     * Get nodes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNodes() {
        return $this->nodes;
    }

}