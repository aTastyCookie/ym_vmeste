<?php
/**
 * Created by PhpStorm.
 * User: Eugene
 * Date: 03.11.2014
 * Time: 6:10
 */

namespace Vmeste\SaasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Vmeste\SaasBundle\Entity\SysEventRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="sys_event")
 */
class SysEvent {

    /**
     * System Events
     */
    const CUSTOMER_LOGIN = 'CUSTOMER_LOGIN';
    const CUSTOMER_LOGOUT = 'CUSTOMER_LOGOUT';
    const ADMIN_LOGIN = 'ADMIN_LOGIN';
    const ADMIN_LOGOUT = 'ADMIN_LOGOUT';
    const RECOVER_PASSWORD = 'RECOVER_PASSWORD';
    const RECOVER_TOKEN_CLEANING = 'RECOVER_TOKEN_CLEANING';
    const CHANGE_PASSWORD = 'CHANGE_PASSWORD';

    const CREATE_CAMPAIGN = 'CREATE_CAMPAIGN';
    const UPDATE_CAMPAIGN = 'UPDATE_CAMPAIGN';
    const DELETE_CAMPAIGN = 'DELETE_CAMPAIGN';
    const BLOCK_CAMPAIGN = 'BLOCK_CAMPAIGN';
    const ACTIVATE_CAMPAIGN = 'ACTIVATE_CAMPAIGN';

    const CREATE_USER = 'CREATE_USER';
    const UPDATE_USER = 'UPDATE_USER';
    const BLOCK_USER = 'BLOCK_USER';
    const ACTIVATE_USER = 'ACTIVATE_USER';

    const UPDATE_EMAIL_SETTINGS = 'UPDATE_EMAIL_SETTINGS';
    const UPDATE_YK_SETTINGS = 'UPDATE_YK_SETTINGS';
    const UPDATE_PASSWORD = 'UPDATE_PASSWORD';

    const CREATE_ADMIN = 'CREATE_ADMIN';
    const UPDATE_ADMIN = 'UPDATE_ADMIN';

    const CREATE_TRANSACTION = 'CREATE_TRANSACTION';
    const UPDATE_TRANSACTION = 'UPDATE_TRANSACTION';
    const SEARCH_TRANSACTION = 'SEARCH_TRANSACTION';
    const CHANGE_TRANSACTION_PAYMENT_STATUS = 'PAYMENT_STATUS';
    const REPORT_ALL_TRANSACTIONS = 'REPORT_ALL_TRANSACTIONS';
    const REPORT_TRANSACTIONS_BY_DATE = 'REPORT_TRANSACTIONS_BY_DATE';

    const NEW_RECURRENT = 'NEW_RECURRENT';
    const UPDATE_RECURRENT = 'UPDATE_RECURRENT';
    const UNSUBSCRIBE_RECURRENT = 'UNSUBSCRIBE_RECURRENT';
    const SUBSCRIBE_RECURRENT = 'SUBSCRIBE_RECURRENT';





    /**
     * @ORM\Column(type="bigint", name="id", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $event;

    /**
     * @ORM\Column(type="integer",  options={"unsigned"=true})
     */
    protected $userId;

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $ip;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $datetime;



   //id, event (text), user (user_id), datetime, ip

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
     * Set event
     *
     * @param string $event
     * @return SysEvent
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return string 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return SysEvent
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return SysEvent
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
     * Set datetime
     *
     * @internal param \DateTime $datetime
     * @return SysEvent
     */
    private function setDatetime()
    {
        $this->datetime = new \DateTime('@'.time());
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setDate()
    {
        $this->setDatetime();
    }

    /**
     * Get datetime
     *
     * @return \DateTime 
     */
    public function getDatetime()
    {
        $currentTimezone  = new \DateTimeZone(date_default_timezone_get());
        $this->datetime->setTimezone($currentTimezone);
        return $this->datetime;
    }
}
