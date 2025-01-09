<?php

namespace AcMarche\Presse\Repository;

use AcMarche\Presse\Doctrine\OrmCrudTrait;
use AcMarche\Presse\Entity\Destinataire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Destinataire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Destinataire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Destinataire[]    findAll()
 * @method Destinataire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DestinataireRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Destinataire::class);
    }

    /**
     * @return Destinataire[]
     */
    public function getAll(): array
    {
        return $this
            ->createQueryBuilder('d')
            ->orderBy('d.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Destinataire[]
     */
    public function findAllWantNotification(): array
    {
        return $this
            ->createQueryBuilder('d')
            ->andWhere('d.notification = true')
            ->orderBy('d.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
