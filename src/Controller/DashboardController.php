<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
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
}
