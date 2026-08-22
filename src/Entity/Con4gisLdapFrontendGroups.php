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
 * Class Con4gisLdapFrontendGroups
 *
 * @ORM\Entity
 * @ORM\Table(name="tl_c4g_ldap_fe_groups")
 * @package con4gis\LdapBundle\Entity
 */
#[ORM\Entity]
#[ORM\Table(name: 'tl_c4g_ldap_fe_groups')]
class Con4gisLdapFrontendGroups
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
     * @var array
     * @ORM\Column(name="fieldMapping", type="array")
     */
    #[ORM\Column(name: 'fieldMapping', type: 'array', nullable: true)]
    protected $fieldMapping = [];

    /**
     * @var string
     * @ORM\Column(name="filter", type="string", options={"default": ""})
     */
    #[ORM\Column(name: 'filter', type: 'string', options: ['default' => ''])]
    protected $filter = '';

    /**
     * @var string
     * @ORM\Column(name="groups", type="blob", options={"default": ""})
     */
    #[ORM\Column(name: 'groups', type: 'blob', nullable: true)]
    protected $groups = '';

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
     * @return array
     */
    public function getFieldMapping(): array
    {
        return $this->fieldMapping ? $this->fieldMapping : [];
    }

    /**
     * @param array $fieldMapping
     */
    public function setFieldMapping(array $fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * @return string
     */
    public function getFilter(): string
    {
        return $this->filter ? $this->filter : '';
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
     * @return string
     */
    public function getGroups(): string
    {
        return $this->groups ? (string) $this->groups : '';
    }

    /**
     * @param string $groups
     */
    public function setGroups(string $groups)
    {
        $this->groups = $groups;
    }
}