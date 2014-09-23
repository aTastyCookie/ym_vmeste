<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/21/14
 * Time: 6:41 PM
 */

namespace Vmeste\SaasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Vmeste\SaasBundle\Entity\CampaignRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="campaign")
 */
class Campaign {

    /**
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="campaigns")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @ORM\Column(type="text")
     */
    protected $image;

    /**
     * @ORM\Column(type="text", name="form_intro",)
     */
    protected $formIntro;

    /**
     * @ORM\Column(type="text", name="form_terms",)
     */
    protected $formTerms;

    /**
     * @ORM\Column(type="text", name="top_intro",)
     */
    protected $topIntro;

    /**
     * @ORM\Column(type="text", name="recent_intro",)
     */
    protected $recentIntro;

    /**
     * @ORM\Column(type="decimal", name="min_amount", scale=2, precision=8)
     */
    protected $minAmount;

    /**
     * @ORM\Column(type="string", length=2, options={"fixed" = true})
     */
    protected $currency;

    /**
     * @ORM\OneToMany(targetEntity="EmailTemplate", mappedBy="campaign")
     **/
    protected $successEmailTemplate;

    /**
     * @ORM\OneToMany(targetEntity="EmailTemplate", mappedBy="campaign")
     **/
    protected $failEmailTemplate;

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
     * Constructor
     */
    public function __construct()
    {
        $this->successEmailTemplate = new \Doctrine\Common\Collections\ArrayCollection();
        $this->failEmailTemplate = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Campaign
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Campaign
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set formIntro
     *
     * @param string $formIntro
     * @return Campaign
     */
    public function setFormIntro($formIntro)
    {
        $this->formIntro = $formIntro;

        return $this;
    }

    /**
     * Get formIntro
     *
     * @return string 
     */
    public function getFormIntro()
    {
        return $this->formIntro;
    }

    /**
     * Set formTerms
     *
     * @param string $formTerms
     * @return Campaign
     */
    public function setFormTerms($formTerms)
    {
        $this->formTerms = $formTerms;

        return $this;
    }

    /**
     * Get formTerms
     *
     * @return string 
     */
    public function getFormTerms()
    {
        return $this->formTerms;
    }

    /**
     * Set topIntro
     *
     * @param string $topIntro
     * @return Campaign
     */
    public function setTopIntro($topIntro)
    {
        $this->topIntro = $topIntro;

        return $this;
    }

    /**
     * Get topIntro
     *
     * @return string 
     */
    public function getTopIntro()
    {
        return $this->topIntro;
    }

    /**
     * Set recentIntro
     *
     * @param string $recentIntro
     * @return Campaign
     */
    public function setRecentIntro($recentIntro)
    {
        $this->recentIntro = $recentIntro;

        return $this;
    }

    /**
     * Get recentIntro
     *
     * @return string 
     */
    public function getRecentIntro()
    {
        return $this->recentIntro;
    }

    /**
     * Set minAmount
     *
     * @param string $minAmount
     * @return Campaign
     */
    public function setMinAmount($minAmount)
    {
        $this->minAmount = $minAmount;

        return $this;
    }

    /**
     * Get minAmount
     *
     * @return string 
     */
    public function getMinAmount()
    {
        return $this->minAmount;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Campaign
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
     * Set user
     *
     * @param \Vmeste\SaasBundle\Entity\User $user
     * @return Campaign
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
     * Add successEmailTemplate
     *
     * @param \Vmeste\SaasBundle\Entity\EmailTemplate $successEmailTemplate
     * @return Campaign
     */
    public function addSuccessEmailTemplate(\Vmeste\SaasBundle\Entity\EmailTemplate $successEmailTemplate)
    {
        $this->successEmailTemplate[] = $successEmailTemplate;

        return $this;
    }

    /**
     * Remove successEmailTemplate
     *
     * @param \Vmeste\SaasBundle\Entity\EmailTemplate $successEmailTemplate
     */
    public function removeSuccessEmailTemplate(\Vmeste\SaasBundle\Entity\EmailTemplate $successEmailTemplate)
    {
        $this->successEmailTemplate->removeElement($successEmailTemplate);
    }

    /**
     * Get successEmailTemplate
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSuccessEmailTemplate()
    {
        return $this->successEmailTemplate;
    }

    /**
     * Add failEmailTemplate
     *
     * @param \Vmeste\SaasBundle\Entity\EmailTemplate $failEmailTemplate
     * @return Campaign
     */
    public function addFailEmailTemplate(\Vmeste\SaasBundle\Entity\EmailTemplate $failEmailTemplate)
    {
        $this->failEmailTemplate[] = $failEmailTemplate;

        return $this;
    }

    /**
     * Remove failEmailTemplate
     *
     * @param \Vmeste\SaasBundle\Entity\EmailTemplate $failEmailTemplate
     */
    public function removeFailEmailTemplate(\Vmeste\SaasBundle\Entity\EmailTemplate $failEmailTemplate)
    {
        $this->failEmailTemplate->removeElement($failEmailTemplate);
    }

    /**
     * Get failEmailTemplate
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFailEmailTemplate()
    {
        return $this->failEmailTemplate;
    }
}
