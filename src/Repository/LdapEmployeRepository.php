<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 10/12/18
 * Time: 14:55.
 */

namespace AcMarche\Presse\Repository;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Ldap\Adapter\CollectionInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Ldap;

class LdapEmployeRepository
{
    public Ldap $ldap;

    public function __construct(
        #[Autowire(env: 'ACLDAP_URL'), \SensitiveParameter]
        readonly string $host,
        #[Autowire(env: 'ACLDAP_DN'), \SensitiveParameter]
        private readonly string $ldapDn,
        #[Autowire(env: 'ACLDAP_USER'), \SensitiveParameter]
        private readonly string $ldapUser,
        #[Autowire(env: 'ACLDAP_PASSWORD'), \SensitiveParameter]
        private readonly string $ldapPassword,
    ) {
        $options = [
            'host' => $host,
            'port' => 636,
            'encryption' => 'ssl',
        ];

        $this->ldap = Ldap::create(
            'ext_ldap',
            $options,
        );
    }

    public function bind(): void
    {
        $this->ldap->bind($this->ldapUser, $this->ldapPassword);
    }

    /**
     * @return CollectionInterface|Entry[]
     */
    public function getAll(): CollectionInterface
    {
        $this->bind();
        $filter = '(&(objectClass=person)(!(uid=acmarche)))';

        $query = $this->ldap->query($this->ldapDn, $filter, [
            'maxItems' => 3000,
        ]);

        return $query->execute();
    }

    public function getEntry(string $uid): ?Entry
    {
        $this->bind();
        $filter = "(&(|(sAMAccountName=$uid))(objectClass=person))";
        $query = $this->ldap->query($this->ldapDn, $filter, [
            'maxItems' => 1,
        ]);
        $results = $query->execute();

        if ($results->count() > 0) {
            return $results[0];
        }

        return null;
    }

    public function getEmail(?string $username): ?string
    {
        if (!$username) {
            return null;
        }
        $entry = $this->getEntry($username);
        if ($entry instanceof Entry) {
            $emails = $entry->getAttribute('mail');
            if (is_array($emails) && $emails !== []) {
                return $emails[0];
            }
        }

        return null;
    }

    /**
     * @return array|string[]
     * @throws InvalidArgumentException
     */
    public function getEntries(): array
    {
        $all = $this->getAll();
        $entries = [];
        foreach ($all as $entry) {
            $attributes = $entry->getAttributes();
            $nom = '';
            if (isset($attributes['givenName'])) {
                //  $nom .= $attributes['givenName'][0];
            }
            if (isset($attributes['sn'])) {
                $nom .= mb_strtoupper((string)$attributes['sn'][0]).' ';
            }
            if (isset($attributes['givenName'])) {
                $nom .= $attributes['givenName'][0];
            }
            if ('' == $nom && isset($attributes['name'])) {
                // $nom = $attributes['name'][0];
            }
            $entries[$attributes['sAMAccountName'][0]] = $nom;
        }

        asort($entries);

        return $entries;
    }
}
