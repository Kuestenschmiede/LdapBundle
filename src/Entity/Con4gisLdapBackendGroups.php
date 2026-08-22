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

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Con4gisLdapBackendGroups
 *
 * @ORM\Entity
 * @ORM\Table(name="tl_c4g_ldap_be_groups")
 * @package con4gis\LdapBundle\Entity
 */
#[ORM\Entity]
#[ORM\Table(name: 'tl_c4g_ldap_be_groups')]
class Con4gisLdapBackendGroups
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    protected $id = 0;

    /**
     * @var int
     * @ORM\Column(name="tstamp", type="integer", options={"default": "0"})
     */
    #[ORM\Column(name: 'tstamp', type: 'integer', options: ['default' => 0])]
    protected $tstamp = 0;

    /**
     * @var string
     * @ORM\Column(name="filter", type="string", options={"default": ""})
     */
    #[ORM\Column(name: 'filter', type: 'string', options: ['default' => ''])]
    protected $filter = '';

    /**
     * @var array
     * @ORM\Column(name="groups", type="array")
     */
    #[ORM\Column(name: 'groups', type: 'array', nullable: true)]
    protected $groups = [];

    /**
     * @var array
     * @ORM\Column(name="adminGroup", type="array")
     */
    #[ORM\Column(name: 'adminGroup', type: 'array', nullable: true)]
    protected $adminGroup = [];

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
    public function getFilter(): string
    {
        return $this->filter ? (string) $this->filter : '';
    }

    /**
     * @param string $filter
     */
    public function setFilter(string $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return int
     */
    public function getTstamp(): int
    {
        return (int) $this->tstamp;
    }

    /**
     * @param int $tstamp
     */
    public function setTstamp(int $tstamp)
    {
        $this->tstamp = $tstamp;
    }

    /**
     * @return array|string
     */
    public function getGroups(): array|string
    {
        return $this->groups ?: [];
    }

    /**
     * @param array|string $groups
     */
    public function setGroups(array|string $groups)
    {
        $this->groups = $groups;
    }

    /**
     * @return array|string
     */
    public function getAdminGroup(): array|string
    {
        return $this->adminGroup ?: [];
    }

    /**
     * @param array|string $adminGroup
     */
    public function setAdminGroup(array|string $adminGroup)
    {
        $this->adminGroup = $adminGroup;
    }
}