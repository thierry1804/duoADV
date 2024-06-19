<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Movement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
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

    /**
     * @throws Exception
     */
    public function getSalesAndExpenses(): array
    {
        $sql = "
            SELECT d.date, SUM(d.CashIN) AS 'cash_in', SUM(d.CashOUT) AS 'cash_out'
            FROM (
                (
                    SELECT s.sold_on AS 'date', SUM((s.qty - s.qty_returned) * (a.sell_price - COALESCE(s.promo, 0))) AS 'CashIN', 0 AS 'CashOUT'
                    FROM sale s
                    INNER JOIN article a ON a.id = s.item_id
                    GROUP BY s.sold_on
                )
                UNION ALL
                (
                    SELECT e.recorded_at AS 'date', 0 AS 'CashIN', SUM(e.amount) AS 'CashOUT'
                    FROM expense e
                    GROUP BY e.recorded_at
                )
            ) d
            GROUP BY d.date
            ORDER BY d.date;
        ";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->executeQuery($sql);

        return $stmt->fetchAllAssociative();
    }
}
