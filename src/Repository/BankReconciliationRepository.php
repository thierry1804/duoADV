<?php

namespace App\Repository;

use App\Entity\BankReconciliation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BankReconciliation>
 */
class BankReconciliationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankReconciliation::class);
    }

    public function getBalance(): array
    {
        $sql = "
            SELECT 
                SUM(b.credit) - SUM(b.debit) AS 'balance'
            FROM bank_reconciliation b
            WHERE 1;
        ";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->executeQuery($sql);

        return $stmt->fetchAllAssociative();
    }
}
