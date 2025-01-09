<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 16/01/19
 * Time: 9:31.
 */

namespace AcMarche\Presse\Service;

use AcMarche\Presse\Entity\Destinataire;
use AcMarche\Presse\Repository\DestinataireRepository;
use AcMarche\Presse\Repository\LdapEmployeRepository;
use Symfony\Component\Ldap\Entry;

class SyncService
{
    public function __construct(
        private readonly DestinataireRepository $destinataireRepository,
        private readonly LdapEmployeRepository $ldapEmployeRepository,
    ) {}

    public function syncAll(): void
    {
        foreach ($this->ldapEmployeRepository->getAll() as $entry) {
            $attributes = $entry->getAttributes();

            if (!$this->isActif($entry)) {
                continue;
            }

            $username = $attributes['sAMAccountName'][0];

            if (!isset($attributes['mail'])) {
                continue;
            }

            $nom = isset($attributes['sn']) ? $attributes['sn'][0] : 'no name';
            $prenom = isset($attributes['givenName']) ? $attributes['givenName'][0] : 'Pas de prenom';

            $email = $attributes['mail'][0];
            $destinataire = $this->destinataireRepository->findOneBy([
                'username' => $username,
            ]);
            if (!$destinataire instanceof Destinataire) {
                $destinataire = new Destinataire();
                $destinataire->username = $username;
                $this->destinataireRepository->persist($destinataire);
            }

            $destinataire->email = $email;
            $destinataire->nom = $nom;
            $destinataire->prenom = $prenom;
        }

        $this->destinataireRepository->flush();
    }

    public function removeOld(): void
    {
        $destinatairesLocaux = $this->destinataireRepository->findAll();
        $destinatairesInactifs = $destinatairesActifs = [];

        foreach ($this->ldapEmployeRepository->getAll() as $employe) {
            $attributes = $employe->getAttributes();
            $username = $attributes['sAMAccountName'][0];
            if ($this->isActif($employe)) {
                $destinatairesActifs[] = $username;
            } else {
                $destinatairesInactifs[] = $username;
            }
        }

        foreach ($destinatairesInactifs as $username) {
            if (str_contains((string)$username, 'stage')) {
                continue;
            }
            $userDb = $this->destinataireRepository->findOneBy([
                'username' => $username,
            ]);
            if ($userDb instanceof Destinataire) {
                $this->destinataireRepository->remove($userDb);
            }
        }

        foreach ($destinatairesLocaux as $user) {
            if (!\in_array($user->username, $destinatairesActifs)) {
                $this->destinataireRepository->remove($user);
            }
        }
    }

    public function isActif(Entry $entry): bool
    {
        $attributes = $entry->getAttributes();
        $useraccountcontrol = $attributes['userAccountControl'][0];

        return 66050 != $useraccountcontrol;
    }
}
