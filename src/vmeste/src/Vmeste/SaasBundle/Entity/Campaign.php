<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/21/14
 * Time: 6:41 PM
 */

namespace Vmeste\SaasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Vmeste\SaasBundle\Entity\CampaignRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="campaign")
 */
class Campaign
{

    /**
     * @ORM\Column(type="integer", name="id", options={"unsigned"=true})
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
     * @ORM\Column(type="string", name="sub_title")
     */
    protected $subTitle;

    /**
     * @Assert\File(
     *     maxSize = "5M",
     *     mimeTypes = {"image/jpeg", "image/png"},
     *     mimeTypesMessage = "Пожалуйста загрузите изображение корректного формата"
     * )
     */
    protected $bigPic;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $bigPicPath;

    /**
     * @ORM\Column(type="text", name="form_intro",)
     */
    protected $formIntro;

    /**
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="campaign")
     **/
    protected $transactions;

    /**
     * @ORM\Column(type="decimal", name="min_amount", scale=2, precision=8)
     */
    protected $minAmount;

    /**
     * @ORM\Column(type="string", length=8, options={"fixed" = true})
     */
    protected $currency;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     **/
    private $status;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $created;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $changed;

    /**
     * @ORM\Column(type="text", name="url",)
     */
    protected $url;

    protected $uploadDir;

    public function __construct()
    {
        $this->transactions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    protected function getUploadRootDir()
    {
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    public function getUploadDir()
    {
        return $this->uploadDir;
    }

    public function setUploadDir($dir)
    {
        $this->uploadDir = $dir;
    }

    public function upload()
    {
        if (null !== $this->getBigPic()) {
            $bigPicName = sha1(uniqid(mt_rand(), true)) . "." . $this->getBigPic()->guessClientExtension();
            $this->getBigPic()->move($this->getUploadRootDir(), $bigPicName);
            $this->bigPicPath = $bigPicName;
            $this->bigPic = null;
        }
    }


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
     * Add transactions
     *
     * @param \Vmeste\SaasBundle\Entity\Transaction $transactions
     * @return Campaign
     */
    public function addTransaction(\Vmeste\SaasBundle\Entity\Transaction $transactions)
    {
        $this->transactions[] = $transactions;

        return $this;
    }

    /**
     * Remove transactions
     *
     * @param \Vmeste\SaasBundle\Entity\Transaction $transactions
     */
    public function removeTransaction(\Vmeste\SaasBundle\Entity\Transaction $transactions)
    {
        $this->transactions->removeElement($transactions);
    }

    /**
     * Get transactions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Set status
     *
     * @param \Vmeste\SaasBundle\Entity\Status $status
     * @return Campaign
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
     * Set subTitle
     *
     * @param string $subTitle
     * @return Campaign
     */
    public function setSubTitle($subTitle)
    {
        $this->subTitle = $subTitle;

        return $this;
    }

    /**
     * Get subTitle
     *
     * @return string
     */
    public function getSubTitle()
    {
        return $this->subTitle;
    }

    /**
     * Set bigPicPath
     *
     * @param string $bigPicPath
     * @return Campaign
     */
    public function setBigPicPath($bigPicPath)
    {
        $this->bigPicPath = $bigPicPath;

        return $this;
    }

    /**
     * Get bigPicPath
     *
     * @return string
     */
    public function getBigPicPath()
    {
        return $this->bigPicPath;
    }

    /**
     * @return mixed
     */
    public function getBigPic()
    {
        return $this->bigPic;
    }

    /**
     * @param mixed $bigPic
     */
    public function setBigPic(UploadedFile $bigPic)
    {
        $this->bigPic = $bigPic;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Campaign
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
}
