<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\ExpenseRepository;
use App\Repository\LettrageRepository;
use App\Repository\SaleRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/operations', name: 'app_dashboard_operation')]
    public function getOperationRecap(LettrageRepository $lettrageRepository,
                                      SaleRepository $saleRepository,
                                      ExpenseRepository $expenseRepository,
                                      ArticleRepository $articleRepository): Response
    {
        $lettrages = $lettrageRepository->getAll();
        $totals = $lettrageRepository->getTotals($lettrages);

        $totaux = $saleRepository->calcAllSales();
        $total = $totaux[0]['total'];
        $totalReceived = $totaux[0]['totalEncaisse'];
        $totalUnReceived = $totaux[0]['totalNonEncaisse'];

        $expenses = $expenseRepository->findBy([], ['recordedAt' => 'DESC']);
        $totalExpense = $expenseRepository->calcAllExpenses($expenses);
        $totalPaid = $expenseRepository->calcAllExpenses($expenses, true);
        $totalUnPaid = $expenseRepository->calcAllExpenses($expenses, false);
        return $this->render(
            'dashboard/_operation_recap.html.twig',
            [
                'totals' => $totals,
                'totalAmount' => $total,
                'totalReceived' => $totalReceived,
                'totalUnReceived' => $totalUnReceived,
                'totalAmountExpense' => $totalExpense,
                'totalPaidAmount' => $totalPaid,
                'totalUnpaidAmount' => $totalUnPaid,
                'articles' => $articleRepository->getArticles(),
            ]
        );
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/potential', name: 'app_dashboard_potential')]
    public function getSalePotential(ArticleRepository $repository): Response
    {
        $salePotential = $repository->getSalePotential()[0];
        $percentStock = (($salePotential['stockS'] - $salePotential['stockS1']) / $salePotential['stockS1']) * 100;
        $percentPotential = (($salePotential['potentielS'] - $salePotential['potentielS1']) /
            $salePotential['potentielS1']) * 100;
        return $this->render('dashboard/_sale_potential.html.twig', [
            'potentials' => $salePotential,
            'percentStock' => $percentStock,
            'percentPotential' => $percentPotential,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/sales', name: 'app_dashboard_sales')]
    public function getSaleRecap(SaleRepository $repository): Response
    {
        $saleRecap = $repository->buildRecap($repository->recapSales(),
            $repository->findBy([], ['recordedAt' => 'DESC']));
        return $this->render('dashboard/_sale_recap.html.twig', [
            'sales' => $saleRecap,
        ]);
    }
}
