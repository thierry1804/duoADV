<?php

namespace App\Repository;

use App\Entity\Sale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sale>
 */
class SaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sale::class);
    }

    /**
     * @throws Exception
     */
    public function calcAllSales(): array
    {
        $sql = "
            SELECT 
                SUM((a.sell_price - COALESCE(s.promo, 0)) * (s.qty - s.qty_returned)) AS 'total',
                SUM(IF(s.received = 1, (a.sell_price - COALESCE(s.promo, 0)) * (s.qty - s.qty_returned), 0)) AS 'totalEncaisse',
                SUM(IF(s.received != 1, (a.sell_price - COALESCE(s.promo, 0)) * (s.qty - s.qty_returned), 0)) AS 'totalNonEncaisse'
            FROM sale s
            INNER JOIN article a ON a.id = s.item_id;
        ";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->executeQuery($sql);

        return $stmt->fetchAllAssociative();
    }
}
