<?php

namespace App\Controller;

use App\Entity\Lettrage;
use App\Form\LettrageType;
use App\Repository\BankReconciliationRepository;
use App\Repository\LettrageRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/lettrage')]
#[IsGranted('ROLE_ADMIN')]
class LettrageController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/', name: 'app_lettrage_index', methods: ['GET'])]
    public function index(LettrageRepository $lettrageRepository,
                          BankReconciliationRepository $reconciliationRepository): Response
    {
        $lettrages = $lettrageRepository->getAll();
        $totals = $lettrageRepository->getTotals($lettrages);
        $balance = $reconciliationRepository->getBalance();
        return $this->render('lettrage/index.html.twig', [
            'lettrages' => $lettrages,
            'totals' => $totals,
            'balance' => $balance[0]['balance'],
        ]);
    }

    #[Route('/new', name: 'app_lettrage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lettrage = new Lettrage();
        $lettrage->setRecordedBy($this->getUser());
        $form = $this->createForm(LettrageType::class, $lettrage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($lettrage->getSales() as $sale) {
                $sale->setLettrage($lettrage);
                $entityManager->persist($sale);
            }
            foreach ($lettrage->getExpenses() as $expense) {
                $expense->setLettrage($lettrage);
                $entityManager->persist($expense);
            }
            $entityManager->persist($lettrage);
            $entityManager->flush();

            return $this->redirectToRoute('app_lettrage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lettrage/new.html.twig', [
            'lettrage' => $lettrage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_lettrage_show', methods: ['GET'])]
    public function show(Lettrage $lettrage): Response
    {
        return $this->render('lettrage/show.html.twig', [
            'lettrage' => $lettrage,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_lettrage_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lettrage $lettrage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LettrageType::class, $lettrage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_lettrage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lettrage/edit.html.twig', [
            'lettrage' => $lettrage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_lettrage_delete', methods: ['POST'])]
    public function delete(Request $request, Lettrage $lettrage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lettrage->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($lettrage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_lettrage_index', [], Response::HTTP_SEE_OTHER);
    }
}
