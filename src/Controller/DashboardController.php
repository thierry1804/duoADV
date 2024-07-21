<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\BankReconciliationRepository;
use App\Repository\ExpenseRepository;
use App\Repository\LettrageRepository;
use App\Repository\MovementRepository;
use App\Repository\SaleRepository;
use Doctrine\DBAL\Exception;
use QuickChart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard/{tab}', name: 'app_dashboard')]
    public function index(string $tab = 'recap'): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'tab' => $tab,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dash/operations', name: 'app_dashboard_operation')]
    public function getOperationRecap(LettrageRepository $lettrageRepository,
                                      SaleRepository $saleRepository,
                                      ExpenseRepository $expenseRepository,
                                      ArticleRepository $articleRepository,
                                      BankReconciliationRepository $reconciliationRepository): Response
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

        $balance = $reconciliationRepository->getBalance();
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
                'balance' => $balance[0]['balance'],
            ]
        );
    }

    /**
     * @throws Exception
     */
    #[Route('/dash/potential', name: 'app_dashboard_potential')]
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
    #[Route('/dash/sales', name: 'app_dashboard_sales')]
    public function getSaleRecap(SaleRepository $repository): Response
    {
        $saleRecap = $repository->buildRecap(
            $repository->recapSales(),
            $repository->findBy([], ['recordedAt' => 'DESC'])
        );
        return $this->render('dashboard/_sale_recap.html.twig', [
            'sales' => $saleRecap,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dash/expenses', name: 'app_dashboard_expenses')]
    public function getExpenseRecap(ExpenseRepository $repository): Response
    {
        $expenseRecap = $repository->buildRecap(
            $repository->calcAllAbsoluteExpenses(),
            $repository->findBy([], ['recordedAt' => 'DESC'])
        );
        return $this->render('dashboard/_expense_recap.html.twig', [
            'expenses' => $expenseRecap,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dash/stock', name: 'app_dashboard_stock')]
    public function getStockRecap(ArticleRepository $repository, MovementRepository $movementRepository): Response
    {
        $stockRecap = $repository->getStockRecap(
            $repository->getArticles(),
            $movementRepository->findAll()
        );
        return $this->render('dashboard/_stock_recap.html.twig', [
            'stocks' => $stockRecap,
        ]);
    }

    #[Route('/dash/bank', name: 'app_dashboard_bank')]
    public function getBankRecap(BankReconciliationRepository $repository): Response
    {
        $bankRecap = $repository->findBy([], ['operationDate' => 'DESC']);
        return $this->render('dashboard/_bank_recap.html.twig', [
            'bank' => $bankRecap,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dash/chart/se', name: 'app_dashboard_chart_se')]
    public function getChartSE(MovementRepository $repository): Response
    {
        $movements = $repository->getSalesAndExpenses();
        $labels = [];
        $dataIn = [];
        $dataOut = [];

        foreach ($movements as $movement) {
            $labels[] = $movement['date'];
            $dataIn[] = $movement['cash_in'];
            $dataOut[] = $movement['cash_out'];
        }

        $chartUrl = 'https://quickchart.io/chart?chart={type: "line",data: {labels: ' . json_encode($labels) . ',datasets: [{label: "Ventes",data: ' . json_encode($dataIn) . ',borderColor: "rgba(75, 192, 192, 1)",backgroundColor: "rgba(75, 192, 192, 0.2)",},{label: "Dépenses",data: ' . json_encode($dataOut) . ',borderColor: "rgba(255, 99, 132, 1)",backgroundColor: "rgba(255, 99, 132, 0.2)",},],},options: {scales: {y: {beginAtZero: true,},},},}';

        return $this->render('dashboard/_chart_se.html.twig', [
            'chart' => $chartUrl . '&width=800&height=400&devicePixelRatio=1.0&format=png&version=2.9.3',
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dash/chart/s', name: 'app_dashboard_chart_s')]
    public function getChartS(MovementRepository $repository): Response
    {
        $movements = $repository->getSalesAndExpenses();
        $labels = [];
        $dataIn = [];

        foreach ($movements as $movement) {
            $labels[] = $movement['date'];
            $dataIn[] = $movement['cash_in'];
        }

        $chartUrl = 'https://quickchart.io/chart?chart={type: "line",data: {labels: ' . json_encode($labels) . ',datasets: [{label: "Ventes",data: ' . json_encode($dataIn) . ',borderColor: "rgba(75, 192, 192, 1)",backgroundColor: "rgba(75, 192, 192, 0.2)",},],},options: {scales: {y: {beginAtZero: true,},},},}';

        return $this->render('dashboard/_chart_se.html.twig', [
            'chart' => $chartUrl . '&width=800&height=400&devicePixelRatio=1.0&format=png&version=2.9.3',
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dash/chart/e', name: 'app_dashboard_chart_e')]
    public function getChartE(MovementRepository $repository): Response
    {
        $movements = $repository->getSalesAndExpenses();
        $labels = [];
        $dataOut = [];

        foreach ($movements as $movement) {
            $labels[] = $movement['date'];
            $dataOut[] = $movement['cash_out'];
        }

        $chartUrl = 'https://quickchart.io/chart?chart={type: "line",data: {labels: ' . json_encode($labels) . ',datasets: [{label: "Dépenses",data: ' . json_encode($dataOut) . ',borderColor: "rgba(255, 99, 132, 1)",backgroundColor: "rgba(255, 99, 132, 0.2)",},],},options: {scales: {y: {beginAtZero: true,},},},}';

        return $this->render('dashboard/_chart_se.html.twig', [
            'chart' => $chartUrl . '&width=800&height=400&devicePixelRatio=1.0&format=png&version=2.9.3',
        ]);
    }
}
