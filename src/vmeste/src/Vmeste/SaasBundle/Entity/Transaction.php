<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/23/14
 * Time: 1:09 AM
 */

namespace Vmeste\SaasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Vmeste\SaasBundle\Entity\CampaignRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="transaction")
 */
class Transaction
{

    /**
     * @ORM\Column(type="integer", name="id", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="transactions")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id")
     **/
    protected $campaign;

    /**
     * @ORM\Column(type="integer", name="donor_id", options={"unsigned"=true})
     **/
    protected $donor_id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Donor", inversedBy="transactions")
     * @ORM\JoinColumn(name="donor_id", referencedColumnName="id")
     **/
    protected $donor;

    /**
     * @ORM\Column(type="bigint", name="invoiceId")
     */
    protected $invoiceId;

    /**
     * @ORM\Column(type="decimal", name="gross", scale=2, precision=8)
     */
    protected $gross; //gross float NOT NULL,

    /**
     * @ORM\Column(type="string", length=8, options={"fixed" = true})
     */
    protected $currency; //currency varchar(15) COLLATE utf8_unicode_ci NOT NULL,

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $paymentStatus; //payment_status varchar(63) COLLATE utf8_unicode_ci NOT NULL,

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $transactionType; //transaction_type varchar(63) COLLATE utf8_unicode_ci NOT NULL,


    /**
     * @ORM\Column(type="text")
     */
    protected $details;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $created;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $changed;


    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @internal param mixed $created
     */
    public function setCreated()
    {
        $this->created = time();
    }

    /**
     * @return mixed
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * @internal param mixed $changed
     */
    public function setChanged()
    {
        $this->changed = time();
    }

    /**
     * @ORM\PrePersist
     */
    public function setDates()
    {
        $this->setCreated();
        $this->setChanged();
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
        $this->setChanged();
    }





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
     * Set invoiceId
     *
     * @param integer $invoiceId
     * @return Transaction
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;

        return $this;
    }

    /**
     * Get invoiceId
     *
     * @return integer 
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * Set gross
     *
     * @param string $gross
     * @return Transaction
     */
    public function setGross($gross)
    {
        $this->gross = $gross;

        return $this;
    }

    /**
     * Get gross
     *
     * @return string 
     */
    public function getGross()
    {
        return $this->gross;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Transaction
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string 
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set paymentStatus
     *
     * @param string $paymentStatus
     * @return Transaction
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * Get paymentStatus
     *
     * @return string 
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * Set transactionType
     *
     * @param string $transactionType
     * @return Transaction
     */
    public function setTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    /**
     * Get transactionType
     *
     * @return string 
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }


    /**
     * Set details
     *
     * @param string $details
     * @return Transaction
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get details
     *
     * @return string 
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set campaign
     *
     * @param \Vmeste\SaasBundle\Entity\Campaign $campaign
     * @return Transaction
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

    /**
     * Set donor
     *
     * @param \Vmeste\SaasBundle\Entity\Donor $donor
     * @return Transaction
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

    /**
     * Set donor_id
     *
     * @param integer $donorId
     * @return Transaction
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
}
