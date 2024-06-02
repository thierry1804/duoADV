<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Movement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movement>
 */
class MovementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movement::class);
    }

    public function getLastSockOfAnArticle(Article $article): int
    {
        $lastStock = 0;

        try {
            $qry = $this->createQueryBuilder('m')
                ->select('m.stockAfter')
                ->where('m.article = :article')
                ->setParameter('article', $article)
                ->orderBy('m.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult()
            ;

            if ($qry) {
                $lastStock = $qry['stockAfter'];
            }
        }
        catch (NonUniqueResultException $e) {
            dd($article, $e->getMessage());
        }

        return $lastStock;
    }
}
