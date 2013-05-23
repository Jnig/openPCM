<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;

/**
 * Jan\ZevirtBundle\Entity\User
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Job {

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
     * @ORM\OneToOne(targetEntity="JMS\JobQueueBundle\Entity\Job", cascade={"persist"})
     */
    private $job;

    /** @ORM\Column(type = "datetime") */
    private $createdAt;

    /**
     * 
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    public function toArray() {
        $ar = array(
            'id' => $this->getId(),
            'state' => $this->getJob()->getState(),
            'command' => $this->getJob()->getCommand() . ' ' . implode(' ', $this->getJob()->getArgs()),
            'createdAt' => $this->getJob()->getCreatedAt()->format('Y-m-d H:i:s'),
            'name' => $this->getName()
        );

        if (count($this->getUser())) {
            $ar['owner'] = $this->getUser()->getFirstName() . ' ' . $this->getUser()->getSecondName();
            $ar['userId'] = $this->getUser()->getId();
        }

        return $ar;
    }

    public function __construct($command, array $args = array()) {
        $this->createdAt = new \DateTime();
        $this->setJob(new \JMS\JobQueueBundle\Entity\Job($command, $args));
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
     * Set job
     *
     * @param \JMS\JobQueueBundle\Entity\Job $job
     * @return Job
     */
    public function setJob(\JMS\JobQueueBundle\Entity\Job $job = null) {
        $this->job = $job;

        return $this;
    }

    /**
     * Get job
     *
     * @return \JMS\JobQueueBundle\Entity\Job 
     */
    public function getJob() {
        return $this->job;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Job
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set user
     *
     * @param \Jan\ZevirtBundle\Entity\User $user
     * @return Job
     */
    public function setUser(\Jan\ZevirtBundle\Entity\User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Jan\ZevirtBundle\Entity\User 
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Job
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

}