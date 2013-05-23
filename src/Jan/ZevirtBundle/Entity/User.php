<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use FR3D\LdapBundle\Model\LdapUserInterface;

/**
 * Jan\ZevirtBundle\Entity\User
 *
 * @ORM\Table(name="fos_user_user")
 * @ORM\Entity
 */
class User extends BaseUser implements LdapUserInterface {

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
     * @ORM\Column(type="string", length=255)
     */
    private $firstName = '';

    /**
     *
     * @ORM\Column(type="string", length=255)
     */
    private $secondName = '';

    /**
     *
     * @ORM\Column(type="boolean")
     */
    private $ldap = true;

    /**
     * 
     * @ORM\OneToMany(targetEntity="Job", mappedBy="user", cascade={"remove"})
     */
    private $jobs;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    public function toArray() {
        return array(
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'firstName' => $this->getFirstName(),
            'secondName' => $this->getSecondName(),
            'lastLogin' => $this->getLastLogin(),
            'roles' => $this->getRoles(),
            'ldap' => $this->getLdap(),
            'username' => $this->getUsername()
        );
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * Ldap Object Distinguished Name
     * @var string $dn
     */
    private $dn;

    /**
     * {@inheritDoc}
     */
    public function setDn($dn) {
        $this->dn = $dn;
    }

    /**
     * {@inheritDoc}
     */
    public function getDn() {
        return $this->dn;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->jobs = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct();
    }

    /**
     * Set ldap
     *
     * @param boolean $ldap
     * @return User
     */
    public function setLdap($ldap) {
        $this->ldap = $ldap;

        return $this;
    }

    /**
     * Get ldap
     *
     * @return boolean 
     */
    public function getLdap() {
        return $this->ldap;
    }

    /**
     * Add jobs
     *
     * @param \Jan\ZevirtBundle\Entity\Job $jobs
     * @return User
     */
    public function addJob(\Jan\ZevirtBundle\Entity\Job $jobs) {
        $this->jobs[] = $jobs;

        return $this;
    }

    /**
     * Remove jobs
     *
     * @param \Jan\ZevirtBundle\Entity\Job $jobs
     */
    public function removeJob(\Jan\ZevirtBundle\Entity\Job $jobs) {
        $this->jobs->removeElement($jobs);
    }

    /**
     * Get jobs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getJobs() {
        return $this->jobs;
    }

    /**
     * Set secondName
     *
     * @param string $secondName
     * @return User
     */
    public function setSecondName($secondName) {
        $this->secondName = $secondName;

        return $this;
    }

    /**
     * Get secondName
     *
     * @return string 
     */
    public function getSecondName() {
        return $this->secondName;
    }

}