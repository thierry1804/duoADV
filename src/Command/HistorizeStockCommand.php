<?php

namespace App\Command;

use App\Entity\Movement;
use App\Repository\MovementRepository;
use App\Repository\SaleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:historize-stock',
    description: 'Historize stock from each movement',
)]
class HistorizeStockCommand extends Command
{
    private SaleRepository $saleRepository;
    private EntityManagerInterface $entityManager;
    private MovementRepository $movementRepository;

    public function __construct(SaleRepository $saleRepository, EntityManagerInterface $entityManager,
                                MovementRepository $movementRepository)
    {
        $this->saleRepository = $saleRepository;
        $this->entityManager = $entityManager;
        $this->movementRepository = $movementRepository;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $message = $this->historizeStock();

        $io->success($message);

        return Command::SUCCESS;
    }

    private function historizeStock(): string
    {
        $sales = $this->saleRepository->findBy(['historized' => null]);
        if (count($sales) === 0) {
            return 'No sales to historize';
        }

        $historization = [];

        foreach ($sales as $sale) {
            $article = $sale->getItem();
            //lookup for the article in the historization array
            $stockBefore = 0;
            if (in_array($article->getId(), array_keys($historization))) {
                $stockBefore = $historization[$article->getId()];
            } else {
                //lookup for the article in the entity Movement
                $stockBefore = $this->movementRepository->getLastSockOfAnArticle($article);
            }
            if ($stockBefore === 0) {
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
