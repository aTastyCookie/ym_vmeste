<?php
/**
 * Authors: Andrey Glyatsevich <paction@bk.ru>
 * Date: 9/22/14
 * Time: 00:52 PM
 */

namespace Vmeste\SaasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Vmeste\SaasBundle\Entity\RecurrentRepository")
 * @ORM\Table(name="recurrent")
 */
class Recurrent
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="hash", type="string", length=32)
     */
    private $hash;
    
    /**
     * @ORM\OneToOne(targetEntity="Donor", inversedBy="recurrent")
     * @ORM\JoinColumn(name="donor_id", referencedColumnName="id")
     **/
    private $donor;

    /**
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="recurrents")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id")
     **/
    protected $campaign;

    
    /**
     * @ORM\Column(name="clientOrderId", type="string", length=24)
     */
    private $client_order_id;
    
    /**
     * @ORM\Column(name="invoiceId", type="bigint", options={"unsigned"=true})
     */
    private $invoice_id;
    
    /**
     * @ORM\Column(name="amount", type="decimal", scale=2, precision=7, options={"unsigned"=true})
     */
    private $amount;
    
    /**
     * @ORM\Column(name="orderNumber", type="string", length=255)
     */
    private $order_number;

    /**
     * @ORM\Column(name="pan", type="string")
     */
    private $pan;

    /**
     * @ORM\Column(name="cvv", type="string", length=10)
     */
    private $cvv;
    
    /**
     * @ORM\Column(name="last_operation_time", type="integer")
     */
    private $last_operation_time;
    
    /**
     * @ORM\Column(name="last_status", type="integer")
     */
    private $last_status;
    
    /**
     * @ORM\Column(name="last_techMessage", type="string", length=255)
     */
    private $last_techmessage;

    /**
     * @ORM\Column(name="last_error", type="integer")
     */
    private $last_error;
   
    
    /**
     * @ORM\Column(name="subscription_date", type="integer", options={"unsigned"=true})
     */
    private $subscription_date;

    
    /**
     * @ORM\Column(name="success_date", type="integer")
     */
    private $success_date;

    /**
     * @ORM\Column(name="next_date", type="integer")
     */
    private $next_date;

	/**
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     **/
    protected $status;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
    /**
     * Set hash
     *
     * @param string $hash
     * @return Recurrent
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Set campaign_id
     *
     * @param integer $campaignId
     * @return Recurrent
     */
    public function setCampaignId($campaignId)
    {
        $this->campaign_id = $campaignId;
    
        return $this;
    }

    /**
     * Get campaign_id
     *
     * @return integer 
     */
    public function getCampaignId()
    {
        return $this->campaign_id;
    }

    /**
     * Set client_order_id
     *
     * @param integer $clientOrderId
     * @return Recurrent
     */
    public function setClientOrderId($clientOrderId)
    {
        $this->client_order_id = $clientOrderId;
    
        return $this;
    }

    /**
     * Get client_order_id
     *
     * @return integer 
     */
    public function getClientOrderId()
    {
        return $this->client_order_id;
    }

    /**
     * Set invoice_id
     *
     * @param integer $invoiceId
     * @return Recurrent
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoice_id = $invoiceId;
    
        return $this;
    }

    /**
     * Get invoice_id
     *
     * @return integer 
     */
    public function getInvoiceId()
    {
        return $this->invoice_id;
    }

    /**
     * Set amount
     *
     * @param \decimal(7,2) $amount
     * @return Recurrent
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    
        return $this;
    }

    /**
     * Get amount
     *
     * @return \decimal(7,2) 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set order_number
     *
     * @param string $orderNumber
     * @return Recurrent
     */
    public function setOrderNumber($orderNumber)
    {
        $this->order_number = $orderNumber;
    
        return $this;
    }

    /**
     * Get order_number
     *
     * @return string 
     */
    public function getOrderNumber()
    {
        return $this->order_number;
    }

    /**
     * Set cvv
     *
     * @param string $cvv
     * @return Recurrent
     */
    public function setCvv($cvv)
    {
        $this->cvv = $cvv;
    
        return $this;
    }

    /**
     * Get cvv
     *
     * @return string 
     */
    public function getCvv()
    {
        return $this->cvv;
    }

    /**
     * Set last_operation_time
     *
     * @param integer $lastOperationTime
     * @return Recurrent
     */
    public function setLastOperationTime($lastOperationTime)
    {
        $this->last_operation_time = $lastOperationTime;
    
        return $this;
    }

    /**
     * Get last_operation_time
     *
     * @return integer 
     */
    public function getLastOperationTime()
    {
        return $this->last_operation_time;
    }

    /**
     * Set last_status
     *
     * @param integer $lastStatus
     * @return Recurrent
     */
    public function setLastStatus($lastStatus)
    {
        $this->last_status = $lastStatus;
    
        return $this;
    }

    /**
     * Get last_status
     *
     * @return integer 
     */
    public function getLastStatus()
    {
        return $this->last_status;
    }

    /**
     * Set last_techmessage
     *
     * @param string $lastTechmessage
     * @return Recurrent
     */
    public function setLastTechmessage($lastTechmessage)
    {
        $this->last_techmessage = $lastTechmessage;
    
        return $this;
    }

    /**
     * Get last_techmessage
     *
     * @return string 
     */
    public function getLastTechmessage()
    {
        return $this->last_techmessage;
    }

    /**
     * Set subscription_date
     *
     * @param integer $subscriptionDate
     * @return Recurrent
     */
    public function setSubscriptionDate($subscriptionDate)
    {
        $this->subscription_date = $subscriptionDate;
    
        return $this;
    }

    /**
     * Get subscription_date
     *
     * @return integer 
     */
    public function getSubscriptionDate()
    {
        return $this->subscription_date;
    }


    /**
     * Set success_date
     *
     * @param integer $successDate
     * @return Recurrent
     */
    public function setSuccessDate($successDate)
    {
        $this->success_date = $successDate;
    
        return $this;
    }

    /**
     * Get success_date
     *
     * @return integer 
     */
    public function getSuccessDate()
    {
        return $this->success_date;
    }

    /**
     * Set donor_id
     *
     * @param integer $donorId
     * @return Recurrent
     */
    public function setDonorId($donorId)
    {
        $this->donor_id = $donorId;
    
        return $this;
    }

    /**
     * Get donor_id
     *
     * @return integer 
     */
    public function getDonorId()
    {
        return $this->donor_id;
    }

    /**
     * Set donor
     *
     * @param \Vmeste\SaasBundle\Entity\Donor $donor
     * @return Recurrent
     */
    public function setDonor(\Vmeste\SaasBundle\Entity\Donor $donor = null)
    {
        $this->donor = $donor;
    
        return $this;
    }

    /**
     * Get donor
     * @param boolean $checkBlocked
     * @return \Vmeste\SaasBundle\Entity\Donor 
     */
    public function getDonor($checkBlocked = false)
    {
        if($checkBlocked && $this->donor->getStatus()->getStatus() === 'BLOCKED') {
            return null;
        }
        return $this->donor;
    }

    /**
     * Set status
     *
     * @param \Vmeste\SaasBundle\Entity\Status $status
     * @return Recurrent
     */
    public function setStatus(\Vmeste\SaasBundle\Entity\Status $status = null)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return \Vmeste\SaasBundle\Entity\Status 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set last_error
     *
     * @param integer $lastError
     * @return Recurrent
     */
    public function setLastError($lastError)
    {
        $this->last_error = $lastError;
    
        return $this;
    }

    /**
     * Get last_error
     *
     * @return integer 
     */
    public function getLastError()
    {
        return $this->last_error;
    }

    /**
     * Set pan
     *
     * @param string $pan
     * @return Recurrent
     */
    public function setPan($pan)
    {
        $this->pan = $pan;
    
        return $this;
    }

    /**
     * Get pan
     *
     * @return string 
     */
    public function getPan()
    {
        return $this->pan;
    }

    /**
     * Set next_date
     *
     * @param integer $nextDate
     * @return Recurrent
     */
    public function setNextDate($nextDate)
    {
        $this->next_date = $nextDate;
    
        return $this;
    }

    /**
     * Get next_date
     *
     * @return integer 
     */
    public function getNextDate()
    {
        return $this->next_date;
    }

    /**
     * Set campaign
     *
     * @param \Vmeste\SaasBundle\Entity\Campaign $campaign
     * @return Recurrent
     */
    public function setCampaign(\Vmeste\SaasBundle\Entity\Campaign $campaign = null)
    {
        $this->campaign = $campaign;
    
        return $this;
    }

    /**
     * Get campaign
     *
     * @return \Vmeste\SaasBundle\Entity\Campaign 
     */
    public function getCampaign()
    {
        return $this->campaign;
    }
}
