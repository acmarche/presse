<?php

namespace AcMarche\Presse\Repository;

use AcMarche\Presse\Doctrine\OrmCrudTrait;
use AcMarche\Presse\Entity\Album;
use AcMarche\Presse\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @return Article[]
     */
    public function findByAlbum(Album $album): array
    {
        return $this
            ->createQueryBuilder('article')
            ->andWhere('article.album = :album')
            ->setParameter('album', $album)
            ->orderBy('article.dateArticle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[]
     */
    public function search($data): array
    {
        $mot = $data['keyword'] ?? null;

        return $this
            ->createQueryBuilder('article')
            ->andWhere('article.nom LIKE :mot OR article.description LIKE :mot')
            ->setParameter('mot', '%'.$mot.'%')
            ->orderBy('article.dateArticle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[]
     */
    public function findByYear(int $year): array
    {
        return $this
            ->createQueryBuilder('article')
            ->andWhere('article.dateArticle LIKE :year')
            ->setParameter('year', $year.'%')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[]
     */
    public function findLast(int $max = 20): array
    {
        return $this
            ->createQueryBuilder('article')
            ->setMaxResults($max)
            ->orderBy('article.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

}
