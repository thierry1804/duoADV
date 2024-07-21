<?php

namespace App\Service;

use App\Entity\Movement;
use App\Repository\MovementRepository;
use App\Repository\SaleRepository;
use Doctrine\ORM\EntityManagerInterface;

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
}
