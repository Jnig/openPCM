<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Event {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $action;

    /**
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $entity;

    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $entityId;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return Changed
     */
    public function setAction($action) {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Set entity
     *
     * @param string $entity
     * @return Changed
     */
    public function setEntity($entity) {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity
     *
     * @return string 
     */
    public function getEntity() {
        return $this->entity;
    }

    /**
     * Set entityId
     *
     * @param integer $entityId
     * @return Changed
     */
    public function setEntityId($entityId) {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entityId
     *
     * @return integer 
     */
    public function getEntityId() {
        return $this->entityId;
    }

}