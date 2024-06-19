<?php

namespace App\Repository;

use App\Entity\Expense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Expense>
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function calcAllExpenses(array $expenses, ?bool $isPaid = null): float
    {
        return array_reduce($expenses, function($total, $expense) use ($isPaid) {
            if ($isPaid === null || $expense->isPaid() === $isPaid) {
                return $total + $expense->getAmount();
            }
            return $total;
        }, 0.0);
    }

    /**
     * @throws Exception
     */
    public function calcAllAbsoluteExpenses(): array
    {
        $sql = "
            SELECT 
                e.recorded_at, 
                SUM(e.amount) AS 'totExpense', 
                SUM(IF(e.paid = 1, e.amount, 0)) AS 'totPaidExpense', 
                SUM(IF(e.paid = 0, e.amount, 0)) AS 'totUnpaidExpense'
            FROM eshopbyv_adv.expense e
            GROUP BY e.recorded_at 
            ORDER BY e.recorded_at DESC;
        ";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->executeQuery($sql);

        return $stmt->fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    public function buildRecap(array $recap, array $expenses): array
    {
        $expensesRecap = [
            'totalAmount' => 0,
            'totalReceived' => 0,
            'totalUnReceived' => 0,
            'recap' => [],
        ];

        $totRes = $this->calcAllAbsoluteExpenses();
        foreach ($totRes as $tot) {
            $expensesRecap['totalAmount'] += $tot['totExpense'];
            $expensesRecap['totalReceived'] += $tot['totPaidExpense'];
            $expensesRecap['totalUnReceived'] += $tot['totUnpaidExpense'];
        }

        foreach ($recap as $item) {
            $dateToFilter = $item['recorded_at'];
            $filteredExpenses = array_filter($expenses, function($item) use ($dateToFilter) {
                return $item->getRecordedAt()->format('Y-m-d') == $dateToFilter;
            });
            $expensesRecap['recap'][] = [
                'recorded_at' => $item['recorded_at'],
                'depense' => $item['totExpense'],
                'depense_payee' => $item['totPaidExpense'],
                'depense_impayee' => $item['totUnpaidExpense'],
                'depenses' => $filteredExpenses,
            ];
        }

        return $expensesRecap;
    }
}
