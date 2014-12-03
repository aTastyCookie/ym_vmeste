<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/23/14
 * Time: 10:45 PM
 */

namespace Vmeste\SaasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="Vmeste\SaasBundle\Entity\YandexKassaRepository")
 * @ORM\Table(name="yandex_kassa", uniqueConstraints={@ORM\UniqueConstraint(name="shopId",columns={"shopId"})})
 */
class YandexKassa
{

    const SANDBOX_ENABLED = 1;

    const SANDBOX_DISABLED = 0;

    /**
     * @ORM\Column(type="integer", name="id", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $shopId = '';

    /**
     * @ORM\Column(type="string")
     */
    protected $scid = '';

    /**
     * @ORM\Column(type="string")
     */
    protected $shoppw = '';

    /**
     * @ORM\Column(type="smallint")
     */
    protected $pc = 0;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $ac = 0;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $wm = 0;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $mc = 0;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $gp = 0;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $sandbox = 1;

    /**
     * @var UploadedFile
     */
    protected $certFile;

    /**
     * @ORM\Column(type="string", nullable=true, name="cert_file_path")
     */
    protected $certFilePath;

    /**
     * @var UploadedFile
     */
    protected $certKeyFile;

    /**
     * @ORM\Column(type="string", nullable=true, name="cert_key_file_path")
     */
    protected $certKeyFilePath;

    /**
     * @ORM\Column(type="string", nullable=true, name="cert_pass")
     */
    protected $certPass;


    /**
     * @var string
     */
    protected $uploadDir;

    public function upload()
    {
        if (null !== $this->getCertFile()) {
            $previousCertFilePath = $this->getCertFilePath();
            if (!is_null($previousCertFilePath) && !empty($previousCertFilePath)) {
                $fullPreviousCertFilePath = $this->getUploadRootDir() . $previousCertFilePath;
                if (file_exists($fullPreviousCertFilePath)) {
                    unlink($fullPreviousCertFilePath);
                }
            }

            $certFileTitle = sha1(uniqid(mt_rand(), true)) . "." . $this->getCertFile()->getClientOriginalExtension();
            $this->getCertFile()->move($this->getUploadRootDir(), $certFileTitle);
            $this->certFilePath = $certFileTitle;
            $this->certFile = null;
        }

        if (null !== $this->getCertKeyFile()) {
            $previousCertKeyFilePath = $this->getCertKeyFilePath();
            if (!is_null($previousCertKeyFilePath) && !empty($previousCertKeyFilePath)) {
                $fullPreviousCertKeyFilePath = $this->getUploadRootDir() . $previousCertKeyFilePath;
                if (file_exists($fullPreviousCertKeyFilePath)) {
                    unlink($fullPreviousCertKeyFilePath);
                }
            }

            $certKeyFileTitle = sha1(uniqid(mt_rand(), true)) . "." . $this->getCertKeyFile()->getClientOriginalExtension();
            $this->getCertKeyFile()->move($this->getUploadRootDir(), $certKeyFileTitle);
            $this->certKeyFilePath = $certKeyFileTitle;
            $this->certKeyFile = null;
        }
    }

    public function getUploadRootDir()
    {
        return __DIR__ . '/../../../../data/';
    }

    /**
     * @return UploadedFile
     */
    public function getCertFile()
    {
        return $this->certFile;
    }

    /**
     * @param mixed $certFile
     */
    public function setCertFile(UploadedFile $certFile = NULL)
    {
        $this->certFile = $certFile;
    }

    /**
     * @return UploadedFile
     */
    public function getCertKeyFile()
    {
        return $this->certKeyFile;
    }

    /**
     * @param mixed $certKeyFile
     */
    public function setCertKeyFile(UploadedFile $certKeyFile = NULL)
    {
        $this->certKeyFile = $certKeyFile;
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
        if (!is_null($shoppw))
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

    /**
     * Set certFilePath
     *
     * @param string $certFilePath
     * @return YandexKassa
     */
    public function setCertFilePath($certFilePath)
    {
        $this->certFilePath = $certFilePath;

        return $this;
    }

    /**
     * Get certFilePath
     *
     * @return string
     */
    public function getCertFilePath()
    {
        return $this->certFilePath;
    }

    /**
     * Set certKeyFilePath
     *
     * @param string $certKeyFilePath
     * @return YandexKassa
     */
    public function setCertKeyFilePath($certKeyFilePath)
    {
        $this->certKeyFilePath = $certKeyFilePath;

        return $this;
    }

    /**
     * Get certKeyFilePath
     *
     * @return string
     */
    public function getCertKeyFilePath()
    {
        return $this->certKeyFilePath;
    }

    /**
     * Set certPass
     *
     * @param string $certPass
     * @return YandexKassa
     */
    public function setCertPass($certPass)
    {
        if (!is_null($certPass))
            $this->certPass = $certPass;

        return $this;
    }

    /**
     * Get certPass
     *
     * @return string
     */
    public function getCertPass()
    {
        return $this->certPass;
    }
}
