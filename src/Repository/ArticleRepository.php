<?php

namespace AcMarche\Presse\Repository;

use AcMarche\Presse\Entity\Album;
use AcMarche\Presse\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @return Article[]
     */
    public function getByDate(\DateTime $dateTime)
    {
        return $this->createQueryBuilder('article')
            ->andWhere('article.dateArticle = :date')
            ->setParameter('date', $dateTime->format('Y-m-d'))
            ->orderBy('article.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Album $album
     * @return Article[]
     */
    public function findByAlbum(Album $album)
    {
        return $this->createQueryBuilder('article')
            ->andWhere('article.album = :album')
            ->setParameter('album', $album)
            ->orderBy('article.dateArticle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function search($data)
    {
        $mot = $data['keyword'] ?? null;

        return $this->createQueryBuilder('article')
            ->andWhere('article.nom LIKE :mot OR article.description LIKE :mot')
            ->setParameter('mot', '%'.$mot.'%')
            ->orderBy('article.dateArticle', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
