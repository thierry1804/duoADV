<?php

namespace App\Repository;

use App\Entity\Lettrage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lettrage>
 */
class LettrageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lettrage::class);
    }

    /**
     * @throws Exception
     */
    public function getAll(): array
    {
        $sql = "
            SELECT 
                l.id, 
                l.label, 
                (
                    SELECT COALESCE(SUM((a.sell_price - COALESCE(s.promo, 0)) * (s.qty - s.qty_returned)), 0)
                    FROM sale s
                    INNER JOIN article a ON a.id = s.item_id
                    WHERE s.lettrage_id = l.id
                ) AS 'amountSales',
                (
                    SELECT COALESCE(SUM(e.amount), 0)
                    FROM expense e
                    WHERE e.lettrage_id = l.id
                ) AS 'amountExpenses',
                l.amount_to_bank AS 'amountToBank'
            FROM lettrage l
            WHERE 1;
        ";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->executeQuery($sql);

        return $stmt->fetchAllAssociative();
    }

    public function getTotals(array $lettrages): array
    {
        $totals = [
            'sales' => 0,
            'expenses' => 0,
            'toBank' => 0,
        ];

        foreach ($lettrages as $item) {
            $totals['sales'] += $item['amountSales'];
            $totals['expenses'] += $item['amountExpenses'];
            $totals['toBank'] += $item['amountToBank'];
        }

        return $totals;
    }
}
