<?php

namespace AcMarche\Presse\Repository;

use AcMarche\Presse\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Album|null find($id, $lockMode = null, $lockVersion = null)
 * @method Album|null findOneBy(array $criteria, array $orderBy = null)
 * @method Album[]    findAll()
 * @method Album[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    /**
     * @return Album[] Returns an array of Album objects
     */
    public function getRoots()
    {
        return $this->createQueryBuilder('album')
            ->leftJoin('album.albums', 'childs')
            ->addSelect('childs')
            ->andWhere('album.parent IS NULL')
            ->orderBy('album.date_album', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Album[] Returns an array of Album objects
     */
    public function getLasts(\DateTime|\DateTimeImmutable $date)
    {
        return $this->createQueryBuilder('album')
            ->andWhere('album.parent IS NULL')
            ->andWhere('album.date_album >= :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->orderBy('album.date_album', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getChilds(Album $album)
    {
        return $this->createQueryBuilder('album')
            ->andWhere('album.parent = :parent')
            ->setParameter('parent', $album)
            ->orderBy('album.date_album', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function search($data)
    {
        $mot = $data['keyword'] ?? null;

        return $this->createQueryBuilder('album')
            ->andWhere('album.nom LIKE :mot OR album.description LIKE :mot')
            ->setParameter('mot', '%'.$mot.'%')
            ->orderBy('album.date_album', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
