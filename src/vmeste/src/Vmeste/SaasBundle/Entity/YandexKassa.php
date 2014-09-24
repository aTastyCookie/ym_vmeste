<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/23/14
 * Time: 10:45 PM
 */

namespace Vmeste\SaasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Vmeste\SaasBundle\Entity\YandexKassaRepository")
 * @ORM\Table(name="yandex_kassa")
 */
class YandexKassa
{

    /**
     * @ORM\Column(type="integer", name="id", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $shopId;

    /**
     * @ORM\Column(type="string")
     */
    protected $scid;

    /**
     * @ORM\Column(type="string")
     */
    protected $shoppw;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $pc;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $ac;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $wm;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $mc;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $gp;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $sandbox;



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
     * Set shopId
     *
     * @param string $shopId
     * @return YandexKassa
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * Get shopId
     *
     * @return string 
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * Set scid
     *
     * @param string $scid
     * @return YandexKassa
     */
    public function setScid($scid)
    {
        $this->scid = $scid;

        return $this;
    }

    /**
     * Get scid
     *
     * @return string 
     */
    public function getScid()
    {
        return $this->scid;
    }

    /**
     * Set shoppw
     *
     * @param string $shoppw
     * @return YandexKassa
     */
    public function setShoppw($shoppw)
    {
        $this->shoppw = $shoppw;

        return $this;
    }

    /**
     * Get shoppw
     *
     * @return string 
     */
    public function getShoppw()
    {
        return $this->shoppw;
    }

    /**
     * Set pc
     *
     * @param integer $pc
     * @return YandexKassa
     */
    public function setPc($pc)
    {
        $this->pc = $pc;

        return $this;
    }

    /**
     * Get pc
     *
     * @return integer 
     */
    public function getPc()
    {
        return $this->pc;
    }

    /**
     * Set ac
     *
     * @param integer $ac
     * @return YandexKassa
     */
    public function setAc($ac)
    {
        $this->ac = $ac;

        return $this;
    }

    /**
     * Get ac
     *
     * @return integer 
     */
    public function getAc()
    {
        return $this->ac;
    }

    /**
     * Set wm
     *
     * @param integer $wm
     * @return YandexKassa
     */
    public function setWm($wm)
    {
        $this->wm = $wm;

        return $this;
    }

    /**
     * Get wm
     *
     * @return integer 
     */
    public function getWm()
    {
        return $this->wm;
    }

    /**
     * Set mc
     *
     * @param integer $mc
     * @return YandexKassa
     */
    public function setMc($mc)
    {
        $this->mc = $mc;

        return $this;
    }

    /**
     * Get mc
     *
     * @return integer 
     */
    public function getMc()
    {
        return $this->mc;
    }

    /**
     * Set gp
     *
     * @param integer $gp
     * @return YandexKassa
     */
    public function setGp($gp)
    {
        $this->gp = $gp;

        return $this;
    }

    /**
     * Get gp
     *
     * @return integer 
     */
    public function getGp()
    {
        return $this->gp;
    }

    /**
     * Set sandbox
     *
     * @param integer $sandbox
     * @return YandexKassa
     */
    public function setSandbox($sandbox)
    {
        $this->sandbox = $sandbox;

        return $this;
    }

    /**
     * Get sandbox
     *
     * @return integer 
     */
    public function getSandbox()
    {
        return $this->sandbox;
    }
}
