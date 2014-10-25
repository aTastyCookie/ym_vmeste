<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/24/14
 * Time: 12:04 AM
 */

namespace Vmeste\SaasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Vmeste\SaasBundle\Entity\DonorRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="donor", indexes={ @ORM\Index( name="search_index", columns={ "name", "email"})})
 */
class Donor
{

    /**
     * @ORM\Column(type="integer", name="id", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id; // int(11) UNSIGNED NOT NULL  AUTO_INCREMENT,

    /**
     * @ORM\Column(name="campaign_id", type="integer", options={"unsigned"=true})
     */
    private $campaign_id;


    /**
     * @ORM\OneToOne(targetEntity="Recurrent", mappedBy="donor")
     **/
    private $recurrent;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $name; //name varchar(255) COLLATE utf8_unicode_ci NOT NULL,

    /**
     * @ORM\Column(type="string")
     */
    protected $email; //email varchar(255) COLLATE utf8_unicode_ci NOT NULL,

    /**
     * @ORM\Column(type="decimal", name="amount", scale=2, precision=8)
     */
    protected $amount;

    /**
     * @ORM\Column(type="string", length=8, options={"fixed" = true})
     */
    protected $currency; // currency varchar(15) COLLATE utf8_unicode_ci NOT NULL,

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     **/
    protected $status;


    /**
     * @ORM\Column(type="text")
     */
    protected $details;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $created; //registered int(11) NOT NULL,

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $updated;


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
     * Set name
     *
     * @param string $name
     * @return Donor
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Donor
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Donor
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set amount
     *
     * @param string $amount
     * @return Donor
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Donor
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
     * Set details
     *
     * @param string $details
     * @return Donor
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
     * Set updated
     *
     * @param integer $updated
     * @return Donor
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return integer
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set status
     *
     * @param \Vmeste\SaasBundle\Entity\Status $status
     * @return Donor
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
     * Set transaction
     *
     * @param \Vmeste\SaasBundle\Entity\Transaction $transaction
     * @return Donor
     */
    public function setTransaction(\Vmeste\SaasBundle\Entity\Transaction $transaction = null)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Get transaction
     *
     * @return \Vmeste\SaasBundle\Entity\Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Set campaign_id
     *
     * @param integer $campaignId
     * @return Donor
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
     * Set recurrent
     *
     * @param \Vmeste\SaasBundle\Entity\Recurrent $recurrent
     * @return Donor
     */
    public function setRecurrent(\Vmeste\SaasBundle\Entity\Recurrent $recurrent = null)
    {
        $this->recurrent = $recurrent;

        return $this;
    }

    /**
     * Get recurrent
     *
     * @return \Vmeste\SaasBundle\Entity\Recurrent
     */
    public function getRecurrent()
    {
        return $this->recurrent;
    }
}
