<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/16/14
 * Time: 9:43 PM
 */

namespace Vmeste\SaasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Vmeste\SaasBundle\Entity\RecoverTokenRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="recover_token")
 */
class RecoverToken
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true}, name="user_id")
     */
    protected $userId;

    /**
     * @ORM\Column(type="string", length=255, options={"unsigned"=true})
     */
    protected $token;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $created;

    /**
     * @ORM\Column(type="smallint", options={"unsigned"=true})
     */
    protected $active = 1;


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
     * Set userId
     *
     * @param integer $userId
     * @return RecoverToken
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
     * Set token
     *
     * @param string $token
     * @return RecoverToken
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set created
     * @internal param int $created
     * @return RecoverToken
     */
    public function setCreated()
    {
        $this->created = time();

        return $this;
    }

    /**
     * Get created
     *
     * @return integer 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set active
     *
     * @param integer $active
     * @return RecoverToken
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return integer 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @ORM\PrePersist
     */
    public  function setDate()
    {
        $this->setCreated();
    }
}
