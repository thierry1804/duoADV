<?php

namespace App\Repository;

use App\Entity\Expense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
