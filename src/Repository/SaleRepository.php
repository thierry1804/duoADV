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

    /**
     * @throws Exception
     */
    public function recapSales(): array
    {
        $sql = "
            SELECT 
                s.sold_on, 
                SUM((s.qty - s.qty_returned) * (a.sell_price - COALESCE(s.promo, 0))) AS 'vente',
                IF(s.received = 1, SUM((s.qty - s.qty_returned) * (a.sell_price - COALESCE(s.promo, 0))), 0) AS 'vente_encaissee',
                SUM((s.qty - s.qty_returned) * (a.sell_price - COALESCE(s.promo, 0))) - IF(s.received = 1, SUM((s.qty - s.qty_returned) * (a.sell_price - COALESCE(s.promo, 0))), 0) as 'reste_a_encaisser'
            FROM sale s 
            INNER JOIN article a ON a.id = s.item_id 
            GROUP BY s.sold_on 
            ORDER BY s.sold_on DESC
        ";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->executeQuery($sql);

        return $stmt->fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    public function buildRecap(array $recap, array $sales): array
    {
        $salesRecap = [
            'totalAmount' => 0,
            'totalReceived' => 0,
            'totalUnReceived' => 0,
            'recap' => [],
        ];

        $totRes = $this->calcAllSales();
        foreach ($totRes as $tot) {
            $salesRecap['totalAmount'] = $tot['total'];
            $salesRecap['totalReceived'] = $tot['totalEncaisse'];
            $salesRecap['totalUnReceived'] = $tot['totalNonEncaisse'];
        }

        foreach ($recap as $item) {
            $dateToFilter = $item['sold_on'];
            $filteredSales = array_filter($sales, function($item) use ($dateToFilter) {
                return $item->getSoldOn()->format('Y-m-d') == $dateToFilter;
            });
            $salesRecap['recap'][] = [
                'sold_on' => $item['sold_on'],
                'vente' => $item['vente'],
                'vente_encaissee' => $item['vente_encaissee'],
                'reste_a_encaisser' => $item['reste_a_encaisser'],
                'sales' => $filteredSales,
            ];
        }

        return $salesRecap;
    }
}
