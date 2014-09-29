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
     * @ORM\OneToOne(targetEntity="Donor", inversedBy="recurrent")
     * @ORM\JoinColumn(name="donor_id", referencedColumnName="id")
     **/
    private $donor;
    
    /**
     * @ORM\Column(name="campaign_id", type="integer")
     */
    private $campaign_id;
    
    /**
     * @ORM\Column(name="clientOrderId", type="integer", options={"unsigned"=true})
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
     * @ORM\Column(name="subscription_date", type="integer", options={"unsigned"=true})
     */
    private $subscription_date;
    
    /**
     * @ORM\Column(name="attempt", type="integer", length=1)
     */
    private $attempt;
    
    /**
     * @ORM\Column(name="success_date", type="integer")
     */
    private $success_date;

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
     * Set donator_id
     *
     * @param integer $donatorId
     * @return Recurrent
     */
    public function setDonatorId($donatorId)
    {
        $this->donator_id = $donatorId;
    
        return $this;
    }

    /**
     * Get donator_id
     *
     * @return integer 
     */
    public function getDonatorId()
    {
        return $this->donator_id;
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
     * Set attempt
     *
     * @param integer $attempt
     * @return Recurrent
     */
    public function setAttempt($attempt)
    {
        $this->attempt = $attempt;
    
        return $this;
    }

    /**
     * Get attempt
     *
     * @return integer 
     */
    public function getAttempt()
    {
        return $this->attempt;
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
     *
     * @return \Vmeste\SaasBundle\Entity\Donor 
     */
    public function getDonor()
    {
        return $this->donor;
    }
}
