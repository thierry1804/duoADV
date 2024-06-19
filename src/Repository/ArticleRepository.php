<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
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
     * @throws Exception
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

        return $this->fetchAll($sql);
    }

    /**
     * @throws Exception
     */
    public function getSalePotential(): array
    {
        $sql = "
            SELECT 
                SUM(IF(
                    IF(
                        COALESCE(mb.verif1,0) = 0, 
                        IF(
                            WEEK(a.created_at, 1) < WEEK(CURDATE(), 1), 
                            a.in_stock, 
                            0
                        ), 
                        COALESCE(mb.stock_after, 0)
                    ) > 0, 
                    1, 
                    0
                )) AS 'stockS1', 
                SUM(a.sell_price * IF(
                    COALESCE(mb.verif1,0) = 0, 
                    IF(
                        WEEK(a.created_at, 1) < WEEK(CURDATE(), 1), 
                        a.in_stock, 
                        0
                    ), 
                    COALESCE(mb.stock_after, 0)
                )) AS 'potentielS1',
                SUM(IF(
                    IF(
                        COALESCE(mb.verif1, 0) != COALESCE(m.verif2, 0),
                        COALESCE(mb.stock_after, 0),
                        IF(
                            COALESCE(m.verif2, 0) = 0,
                            IF(
                                WEEK(a.created_at, 1) = WEEK(CURDATE(), 1), 
                                a.in_stock, 
                                0
                            ), 
                            COALESCE(m.stock_after, 0)
                        )
                    ) > 0, 
                    1, 
                    0
                )) AS 'stockS',
                SUM(a.sell_price * IF(
                    COALESCE(mb.verif1, 0) != COALESCE(m.verif2, 0),
                    COALESCE(mb.stock_after, 0),
                    IF(
                        COALESCE(m.verif2, 0) = 0,
                        IF(
                            WEEK(a.created_at, 1) = WEEK(CURDATE(), 1), 
                            a.in_stock, 
                            0
                        ), 
                        COALESCE(m.stock_after, 0)
                    )
                )) AS 'potentielS'
            FROM article a
            LEFT JOIN (
                SELECT m1.article_id, m1.stock_after, (
                    SELECT COUNT(m2.id)
                    FROM movement m2
                    WHERE m2.article_id = m1.article_id
                ) AS 'verif1'
                FROM movement m1
                WHERE WEEK(m1.recorded_at, 1) < WEEK(CURDATE(), 1)
                AND m1.id = (
                    SELECT m2.id
                    FROM movement m2
                    WHERE m2.article_id = m1.article_id
                    AND WEEK(m2.recorded_at, 1) < WEEK(CURDATE(), 1)
                    ORDER BY m2.id DESC
                    LIMIT 1
                )
            ) mb ON a.id = mb.article_id
            LEFT JOIN (
                SELECT m3.article_id, m3.stock_after, (
                    SELECT COUNT(m4.id)
                    FROM movement m4
                    WHERE m4.article_id = m3.article_id
                ) AS 'verif2'
                FROM movement m3
                WHERE WEEK(m3.recorded_at, 1) = WEEK(CURDATE(), 1)
                AND m3.id = (
                    SELECT m4.id
                    FROM movement m4
                    WHERE m4.article_id = m3.article_id
                    AND WEEK(m4.recorded_at, 1) = WEEK(CURDATE(), 1)
                    ORDER BY m4.id DESC
                    LIMIT 1
                )
            ) m ON a.id = m.article_id;
        ";

        return $this->fetchAll($sql);
    }

    public function getStockRecap(array $articles, array $movements): array
    {
        $stockRecap = [];
        foreach ($articles as $article) {
            $id = $article['id'];
            $stockRecap[$article['id']] = [
                'label' => $article['label'],
                'sellPrice' => $article['sellPrice'],
                'inStock' => $article['inStock'],
                'status' => $article['status'],
                'movements' => array_filter($movements, function($item) use ($id) {
                    return $item->getArticle()->getId() == $id;
                }),
            ];
        }

        return $stockRecap;
    }

    /**
     * @throws Exception
     */
    private function fetchAll(string $sql): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->executeQuery($sql);

        return $stmt->fetchAllAssociative();
    }
}
