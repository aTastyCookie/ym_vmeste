<?php
/**
 * Created by PhpStorm.
 * User: Eugene
 * Date: 07.11.2014
 * Time: 21:17
 */

namespace Vmeste\SaasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Vmeste\SaasBundle\Entity\IpRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="ip")
 */
class Ip
{

    const BLOCKED = 'BLOCKED';
    const PENDING = 'PENDING';

    /**
     * @ORM\Column(type="bigint", name="id", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $ip;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $attempt;

    /**
     * @ORM\Column(type="string", length=8)
     */
    protected $state;

    /**
     * @ORM\Column(type="time")
     */
    protected $time;

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function setTimePrePersist()
    {
        $this->setTime(new \DateTime());
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
     * Set ip
     *
     * @param string $ip
     * @return Ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Ip
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Get time
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set time
     *
     * @param \DateTime $time
     * @return Ip
     */
    protected function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Set attempt
     *
     * @param integer $attempt
     * @return Ip
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
}
