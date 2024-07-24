<?php

namespace App\Controller;

use App\Repository\SaleRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OperationController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/operation', name: 'app_operation')]
    public function index(Request $request, SaleRepository $saleRepository): Response
    {
        $currentDate = new \DateTime();
        $week = $currentDate->format('Y-\WW');

        if ($request->isMethod('POST')) {
            $week = $request->request->get('week');
        }

        $periods = $this->getDateStartAndEndFromWeek($week);
        $dateStart = $periods[0];
        $dateEnd = $periods[1];

        try {
            $sales = $saleRepository->getSalesBetweenDates($dateStart, $dateEnd);
        } catch (Exception $e) {
            $this->addFlash('error', 'An error occurred while fetching the sales data.');
            $sales = [];
        }
        return $this->render('operation/index.html.twig', [
            'controller_name' => 'OperationController',
            'week' => $week,
            'sales' => $sales,
            'dates' => $this->getDatesFromStartToEnd($dateStart, $dateEnd),
        ]);
    }

    /**
     * @param string $week
     * @return array
     */
    private function getDateStartAndEndFromWeek(string $week): array
    {
        // Split the week input into year and week
        [$year, $weekNumber] = explode('-W', $week);

        // Create a new DateTime object
        $date = new \DateTime();

        // Set the date to the first day of the submitted week
        $date->setISODate($year, $weekNumber);

        // Get the previous Saturday
        $previousSaturday = clone $date;
        $previousSaturday->modify('last saturday');
        $previousSaturday = $previousSaturday->format('Y-m-d');

        // Get the next Friday
        $nextFriday = clone $date;
        $nextFriday->modify('next friday');
        $nextFriday = $nextFriday->format('Y-m-d');

        return [$previousSaturday, $nextFriday];
    }

    /**
     * @throws Exception
     */
    private function getDatesFromStartToEnd(string $dateStart, string $dateEnd): array
    {
        $dates = [];
        $currentDate = new \DateTime($dateStart);
        $dateEnd = new \DateTime($dateEnd);
        while ($currentDate <= $dateEnd) {
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->modify('+1 day');
        }
        return $dates;
    }
}
