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
    protected $id; //user_id int(11) UNSIGNED NOT NULL,

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="transactions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Donor", inversedBy="transactions")
     * @ORM\JoinColumn(name="donor_id", referencedColumnName="id")
     **/
    protected $donor; //donor_id int(11) NOT NULL,

    /**
     * @ORM\Column(type="string")
     */
    protected $payerName; //payer_name varchar(255) COLLATE utf8_unicode_ci NOT NULL,

    /**
     * @ORM\Column(type="string")
     */
    protected $payerEmail;

    /**
     * @ORM\Column(type="decimal", name="min_amount", scale=2, precision=8)
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
     * @ORM\Column(type="string", length=64)
     */
    protected $txnId = ''; //txn_id varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',

    /**
     * @ORM\Column(type="text")
     */
    protected $details;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $created;


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
     * @ORM\PrePersist
     */
    public function setDates()
    {
        $this->setCreated();
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
     * Set user
     *
     * @param \Vmeste\SaasBundle\Entity\User $user
     * @return Transaction
     */
    public function setUser(\Vmeste\SaasBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Vmeste\SaasBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set donor
     *
     * @param \Vmeste\SaasBundle\Entity\User $donor
     * @return Transaction
     */
    public function setDonor(\Vmeste\SaasBundle\Entity\User $donor = null)
    {
        $this->donor = $donor;

        return $this;
    }

    /**
     * Get donor
     *
     * @return \Vmeste\SaasBundle\Entity\User
     */
    public function getDonor()
    {
        return $this->donor;
    }

    /**
     * Set payerName
     *
     * @param string $payerName
     * @return Transaction
     */
    public function setPayerName($payerName)
    {
        $this->payerName = $payerName;

        return $this;
    }

    /**
     * Get payerName
     *
     * @return string 
     */
    public function getPayerName()
    {
        return $this->payerName;
    }

    /**
     * Set payerEmail
     *
     * @param string $payerEmail
     * @return Transaction
     */
    public function setPayerEmail($payerEmail)
    {
        $this->payerEmail = $payerEmail;

        return $this;
    }

    /**
     * Get payerEmail
     *
     * @return string 
     */
    public function getPayerEmail()
    {
        return $this->payerEmail;
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
     * Set txnId
     *
     * @param string $txnId
     * @return Transaction
     */
    public function setTxnId($txnId)
    {
        $this->txnId = $txnId;

        return $this;
    }

    /**
     * Get txnId
     *
     * @return string 
     */
    public function getTxnId()
    {
        return $this->txnId;
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
}
