<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getArticles(): array
    {
        $sql = "
            SELECT
                a.id,
                a.label,
                a.description,
                a.purchase_price AS 'purchasePrice',
                a.sell_price AS 'sellPrice',
                a.min_stock AS 'minStock',
                COALESCE(m.stock_after, a.in_stock) AS 'inStock',
                IF(a.min_stock >= COALESCE(m.stock_after, a.in_stock), 'danger', 'success') AS 'status'
            FROM article a
            LEFT JOIN (
                SELECT 
                    article_id,
                    stock_after
                FROM movement
                WHERE (article_id, id) IN (
                    SELECT article_id, MAX(id)
                    FROM movement
                    GROUP BY article_id
                )
            ) m ON a.id = m.article_id
            ORDER BY a.label ASC;
        ";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->executeQuery($sql);

        return $stmt->fetchAllAssociative();
    }
}
