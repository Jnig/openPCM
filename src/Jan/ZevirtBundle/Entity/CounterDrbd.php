<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Jan\ZevirtBundle\Entity\Disk;

/**

 * @ORM\Table()
 * @ORM\HasLifecycleCallbacks() 
 * @ORM\Entity
 */
class CounterDrbd extends Counter {

    /**
     * @ORM\ManyToOne(targetEntity="Cluster")
     * */
    private $cluster;

    /**
     * @ORM\Column(type="integer")
     */
    private $value;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set value
     *
     * @param integer $value
     * @return CounterDrbd
     */
    public function setValue($value) {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer 
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Set cluster
     *
     * @param \Jan\ZevirtBundle\Entity\Cluster $cluster
     * @return CounterDrbd
     */
    public function setCluster(\Jan\ZevirtBundle\Entity\Cluster $cluster = null) {
        $this->cluster = $cluster;

        return $this;
    }

    /**
     * Get cluster
     *
     * @return \Jan\ZevirtBundle\Entity\Cluster 
     */
    public function getCluster() {
        return $this->cluster;
    }

}