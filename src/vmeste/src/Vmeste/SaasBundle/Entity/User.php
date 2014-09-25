<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/13/14
 * Time: 8:45 PM
 */

namespace Vmeste\SaasBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="Vmeste\SaasBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user")
 */
class User implements UserInterface, \Serializable
{

    const ACTIVE = 1;
    const BLOCKED = 2;
    const DELETED = 3;

    /**
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $password;

    /**
     * @ORM\ManyToMany(targetEntity="Status", inversedBy="users")
     *
     */
    protected $statuses;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $created;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $changed;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     *
     */
    private $roles;

    /**
     * @ORM\Column(type="integer", name="created_by", options={"unsigned"=true})
     */
    private $createdBy;

    /**
     * @ORM\OneToMany(targetEntity="Campaign", mappedBy="user")
     **/
    private $campaigns;

    /**
     * @ORM\ManyToMany(targetEntity="Settings", inversedBy="users")
     */
    protected $settings;


    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->statuses = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }

    public function setRole(Role $role = null)
    {
        $this->roles->add($role);
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
//        $this->created = time();
//        $this->changed = time();
        $this->setCreated();
        $this->setChanged();
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
//        $this->changed = time();
        $this->setChanged();
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * Add roles
     *
     * @param \Vmeste\SaasBundle\Entity\Role $roles
     * @return User
     */
    public function addRole(\Vmeste\SaasBundle\Entity\Role $roles)
    {
        $this->roles[] = $roles;

        return $this;
    }

    public function getRole() {

        return $this->roles[0]->getRole();
    }

    /**
     * Remove roles
     *
     * @param \Vmeste\SaasBundle\Entity\Role $roles
     */
    public function removeRole(\Vmeste\SaasBundle\Entity\Role $roles)
    {
        $this->roles->removeElement($roles);
    }



    /**
     * Add statuses
     *
     * @param \Vmeste\SaasBundle\Entity\Status $statuses
     * @return User
     */
    public function addStatus(\Vmeste\SaasBundle\Entity\Status $statuses)
    {
        $this->statuses[] = $statuses;

        return $this;
    }

    /**
     * Remove statuses
     *
     * @param \Vmeste\SaasBundle\Entity\Status $statuses
     */
    public function removeStatus(\Vmeste\SaasBundle\Entity\Status $statuses)
    {
        $this->statuses->removeElement($statuses);
    }

    /**
     * Get statuses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * Add campaigns
     *
     * @param \Vmeste\SaasBundle\Entity\Campaign $campaigns
     * @return User
     */
    public function addCampaign(\Vmeste\SaasBundle\Entity\Campaign $campaigns)
    {
        $this->campaigns[] = $campaigns;

        return $this;
    }

    /**
     * Remove campaigns
     *
     * @param \Vmeste\SaasBundle\Entity\Campaign $campaigns
     */
    public function removeCampaign(\Vmeste\SaasBundle\Entity\Campaign $campaigns)
    {
        $this->campaigns->removeElement($campaigns);
    }

    /**
     * Get campaigns
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCampaigns()
    {
        return $this->campaigns;
    }


    /**
     * Add settings
     *
     * @param \Vmeste\SaasBundle\Entity\Settings $settings
     * @return User
     */
    public function addSetting(\Vmeste\SaasBundle\Entity\Settings $settings)
    {
        $this->settings[] = $settings;

        return $this;
    }

    /**
     * Remove settings
     *
     * @param \Vmeste\SaasBundle\Entity\Settings $settings
     */
    public function removeSetting(\Vmeste\SaasBundle\Entity\Settings $settings)
    {
        $this->settings->removeElement($settings);
    }

    /**
     * Get settings
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
