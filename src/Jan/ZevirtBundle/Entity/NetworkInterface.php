<?php

namespace Jan\ZevirtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\SerializerBundle\Annotation\SerializedName;

/**
 * Jan\ZevirtBundle\Entity\NetworkInterface
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Jan\ZevirtBundle\Entity\Repository\NetworkInterfaceRepository")
 */
class NetworkInterface {

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
     * @ORM\Column(type="string", length=255)
     */
    private $macAddress;

    /**
     *   
     * @ORM\Column(type="string", length=255)
     */
    private $modelType;

    /**
     * 
     * @ORM\ManyToOne(targetEntity="Network")
     */
    private $network;

    /**
     * @ORM\ManyToOne(targetEntity="VirtualMachine", inversedBy="networkInterfaces")
     * */
    private $virtualMachine;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set macAddress
     *
     * @param string $macAddress
     * @return NetworkInterface
     */
    public function setMacAddress($macAddress) {
        $this->macAddress = $macAddress;

        return $this;
    }

    /**
     * Get macAddress
     *
     * @return string 
     */
    public function getMacAddress() {
        return $this->macAddress;
    }

    /**
     * Set sourceBridge
     *
     * @param string $sourceBridge
     * @return NetworkInterface
     */
    public function setSourceBridge($sourceBridge) {
        $this->sourceBridge = $sourceBridge;

        return $this;
    }

    /**
     * Get sourceBridge
     *
     * @return string 
     */
    public function getSourceBridge() {
        return $this->sourceBridge;
    }

    /**
     * Set modelType
     *
     * @param string $modelType
     * @return NetworkInterface
     */
    public function setModelType($modelType) {
        $this->modelType = $modelType;

        return $this;
    }

    /**
     * Get modelType
     *
     * @return string 
     */
    public function getModelType() {
        return $this->modelType;
    }

    /**
     * Set network
     *
     * @param \Jan\ZevirtBundle\Entity\Network $network
     * @return NetworkInterface
     */
    public function setNetwork(\Jan\ZevirtBundle\Entity\Network $network = null) {
        $this->network = $network;

        return $this;
    }

    /**
     * Get network
     *
     * @return \Jan\ZevirtBundle\Entity\Network 
     */
    public function getNetwork() {
        return $this->network;
    }

    /**
     * Set virtualMachine
     *
     * @param \Jan\ZevirtBundle\Entity\VirtualMachine $virtualMachine
     * @return NetworkInterface
     */
    public function setVirtualMachine(\Jan\ZevirtBundle\Entity\VirtualMachine $virtualMachine = null) {
        $this->virtualMachine = $virtualMachine;

        return $this;
    }

    /**
     * Get virtualMachine
     *
     * @return \Jan\ZevirtBundle\Entity\VirtualMachine 
     */
    public function getVirtualMachine() {
        return $this->virtualMachine;
    }

    public function toArray() {
        return array(
            'id' => $this->getId(),
            'macAddress' => $this->getMacAddress(),
            'modelType' => $this->getModelType(),
            'network' => $this->getNetwork()->getId(),
            'name' => $this->getNetwork()->getName()
        );
    }

    public function toXml() {
        $ret = "       
    <interface type='bridge'>
      <source bridge='{$this->getNetwork()->getBridgeName()}'/>
      <mac address='{$this->getMacAddress()}'/>
      <model type='{$this->getModelType()}'/>
    </interface>
    ";

        return $ret;
    }

}