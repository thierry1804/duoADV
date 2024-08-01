<?php

namespace App\Service;

use App\Entity\Movement;
use App\Entity\Sale;
use App\Repository\MovementRepository;
use App\Repository\SaleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\Date;

class StockService
{
    public function __construct(
        private SaleRepository $saleRepository,
        private EntityManagerInterface $entityManager,
        private MovementRepository $movementRepository
    ) {
    }

    public function historizeStock(): string
    {
        $sales = $this->saleRepository->findBy(['historized' => null]);
        if (count($sales) === 0) {
            return 'No sales to historize';
        }

        $historization = [];

        foreach ($sales as $sale) {
            $article = $sale->getItem();
            //lookup for the article in the historisation array
            $stockBefore = 0;
            if (in_array($article->getId(), array_keys($historization))) {
                $stockBefore = $historization[$article->getId()];
            } else {
                //lookup for the article in the entity Movement
                $stockBefore = $this->movementRepository->getLastSockOfAnArticle($article);
            }
            if ($stockBefore === 0 && !$this->movementRepository->isArticleHasMovement($article)) {
                $stockBefore = $article->getInStock();
            }
            $historization[$article->getId()] = $stockBefore + ($sale->getQtyReturned() - $sale->getQty());

            $movement = new Movement();
            $movement->setOperatedAt($sale->getSoldOn());
            $movement->setArticle($article);
            $movement->setType(2);
            $movement->setReference($sale->getId());
            $movement->setStockBefore($stockBefore);
            $movement->setQty($sale->getQtyReturned() - $sale->getQty());
            $movement->setStockAfter($historization[$article->getId()]);

            $sale->setHistorized(true);

            $this->entityManager->persist($sale);
            $this->entityManager->persist($movement);
            $this->entityManager->flush();
        }

        return 'Historization processed.';
    }

    /**
     * @param Sale $sale
     * @return void
     */
    public function historizeStockBySale(Sale $sale): void
    {
        $article = $sale->getItem();
        // Find in Movement if there is a record for this sale and the article with type = 3
        $movement = $this->movementRepository->findOneBy(['article' => $article, 'reference' => $sale->getId(), 'type' => 3]);
        // If any, delete it
        if ($movement) {
            $this->entityManager->remove($movement);
            $this->entityManager->flush();
        }

        //lookup for the article in the entity Movement
        $stockBefore = $this->movementRepository->getLastSockOfAnArticle($article);
        $stockAfter = $stockBefore + $sale->getQtyReturned();

        $movement = new Movement();
        $movement->setOperatedAt(new \DateTime());
        $movement->setArticle($article);
        $movement->setType(3);
        $movement->setReference($sale->getId());
        $movement->setStockBefore($stockBefore);
        $movement->setQty($sale->getQtyReturned());
        $movement->setStockAfter($stockAfter);

        $sale->setHistorized(true);

        $this->entityManager->persist($sale);
        $this->entityManager->persist($movement);
        $this->entityManager->flush();
    }
}
