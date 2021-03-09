<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package     con4gis
 * @version     7
 * @author      con4gis contributors (see "authors.txt")
 * @license     LGPL-3.0-or-later
 * @copyright   Küstenschmiede GmbH Software & Design
 * @link        https://www.con4gis.org
 *
 */

namespace con4gis\LdapBundle\Entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * Class Service
 *
 * @ORM\Entity
 * @ORM\Table(name="tl_c4g_ldap_settings")
 * @package con4gis\LdapBundle\Entity
 */

class Con4gisLdapSettings
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    protected $id = 0;

    /**
     * @var int
     * @ORM\Column(name="tstamp", type="integer", options={"default": "0"})
     */
    protected $tstamp = '';

    /**
     * @var string
     * @ORM\Column(name="bindDn", type="string", options={"default": ""})
     */
    protected $bindDn = '';

    /**
     * @var string
     * @ORM\Column(name="baseDn", type="string", options={"default": ""})
     */
    protected $baseDn = '';

    /**
     * @var string
     * @ORM\Column(name="password", type="string", options={"default": ""})
     */
    protected $password = '';

    /**
     * @var string
     * @ORM\Column(name="encryption", type="string", options={"default": ""})
     */
    protected $encryption = '';

    /**
     * @var string
     * @ORM\Column(name="server", type="string", options={"default": ""})
     */
    protected $server = '';

    /**
     * @var string
     * @ORM\Column(name="port", type="string", options={"default": ""})
     */
    protected $port = '';

    /**
     * @var string
     * @ORM\Column(name="email", type="string", options={"default": ""})
     */
    protected $email = '';

    /**
     * @var string
     * @ORM\Column(name="lastname", type="string", options={"default": ""})
     */
    protected $lastname = '';

    /**
     * @var string
     * @ORM\Column(name="firstname", type="string", options={"default": ""})
     */
    protected $firstname = '';

    /**
     * @var string
     * @ORM\Column(name="userFilter", type="string", options={"default": ""})
     */
    protected $userFilter = '';

    /**
     * @var string
     * @ORM\Column(name="updateData", type="string", options={"default": ""})
     */
    protected $updateData = '';

    /**
     * @var string
     * @ORM\Column(name="updateFilter", type="string", options={"default": ""})
     */
    protected $updateFilter = '';

    /**
     * @var string
     * @ORM\Column(name="linkWithUserMail", type="string", options={"default": ""})
     */
    protected $linkWithUserMail = '';

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTstamp(): int
    {
        return $this->tstamp ? $this->tstamp : '';
    }

    /**
     * @param int $tstamp
     */
    public function setTstamp(int $tstamp)
    {
        $this->tstamp = $tstamp;
    }

    /**
     * @return string
     */
    public function getBindDn(): string
    {
        return $this->bindDn ? $this->bindDn : '';
    }

    /**
     * @param string $bindDn
     */
    public function setBindDn(string $bindDn)
    {
        $this->bindDn = $bindDn;
    }

    /**
     * @return string
     */
    public function getBaseDn(): string
    {
        return $this->baseDn ? $this->baseDn : '';
    }

    /**
     * @param string $baseDn
     */
    public function setBaseDn(string $baseDn)
    {
        $this->baseDn = $baseDn;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password ? $this->password : '';
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getEncryption(): string
    {
        return $this->encryption ? $this->encryption : '';
    }

    /**
     * @param string $encryption
     */
    public function setEncryption(string $encryption)
    {
        $this->encryption = $encryption;
    }

    /**
     * @return string
     */
    public function getServer(): string
    {
        return $this->server ? $this->server : '';
    }

    /**
     * @param int $server
     */
    public function setServer(int $server)
    {
        $this->server = $server;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port ? $this->port : '';
    }

    /**
     * @param int $port
     */
    public function setPort(int $port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email ? $this->email : '';
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname ? $this->lastname : '';
    }

    /**
     * @param string $lastname
     */
    public function setLastname(string $lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname ? $this->firstname : '';
    }

    /**
     * @param string $firstname
     */
    public function setFirstname(string $firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @param string $userFilter
     */
    public function setUserFilter(string $userFilter)
    {
        $this->userFilter = $userFilter;
    }

    /**
     * @return string
     */
    public function getUserFilter(): string
    {
        return $this->userFilter ? $this->userFilter : '';
    }

    /**
     * @param string $updateData
     */
    public function setUpdateData(string $updateData)
    {
        $this->updateData = $updateData;
    }

    /**
     * @return string
     */
    public function getUpdateData(): string
    {
        return $this->updateData ? $this->updateData : '';
    }

    /**
     * @param string $updateFilter
     */
    public function setUpdateFilter(string $updateFilter)
    {
        $this->updateFilter = $updateFilter;
    }

    /**
     * @return string
     */
    public function getUpdateFilter(): string
    {
        return $this->updateFilter ? $this->updateFilter : '';
    }

    /**
     * @return string
     */
    public function shouldLinkWithUserMail(): string
    {
        return $this->linkWithUserMail ? $this->linkWithUserMail : '';
    }

    /**
     * @param string $linkWithUserMail
     */
    public function setLinkWithUserMail(string $linkWithUserMail)
    {
        $this->linkWithUserMail = $linkWithUserMail;
    }
}