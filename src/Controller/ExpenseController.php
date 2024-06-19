<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Form\ExpenseType;
use App\Repository\ExpenseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/expense')]
#[IsGranted('ROLE_ADMIN')]
class ExpenseController extends AbstractController
{
    #[Route('/', name: 'app_expense_index', methods: ['GET'])]
    public function index(ExpenseRepository $expenseRepository): Response
    {
        $expenses = $expenseRepository->findBy([], ['recordedAt' => 'DESC']);
        $total = $expenseRepository->calcAllExpenses($expenses);
        $totalPaid = $expenseRepository->calcAllExpenses($expenses, true);
        $totalUnPaid = $expenseRepository->calcAllExpenses($expenses, false);
        $expense = new Expense();
        $expense->setRecordedAt(new \DateTime());
        $expense->setAmount(0);
        $expense->setPaid(false);
        $expense->setCreatedBy($this->getUser());
        $form = $this->createForm(ExpenseType::class, $expense);
        return $this->render('expense/index.html.twig', [
            'expenses' => $expenses,
            'totalAmount' => $total,
            'totalPaidAmount' => $totalPaid,
            'totalUnpaidAmount' => $totalUnPaid,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_expense_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $expense = new Expense();
        $form = $this->createForm(ExpenseType::class, $expense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
            $expense->setCreatedBy($user);
            $entityManager->persist($expense);
            $entityManager->flush();

            return $this->redirectToRoute('app_expense_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('expense/new.html.twig', [
            'expense' => $expense,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_expense_show', methods: ['GET'])]
    public function show(Expense $expense): Response
    {
        return $this->render('expense/show.html.twig', [
            'expense' => $expense,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_expense_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Expense $expense, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ExpenseType::class, $expense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_expense_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('expense/edit.html.twig', [
            'expense' => $expense,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_expense_delete', methods: ['POST'])]
    public function delete(Request $request, Expense $expense, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$expense->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($expense);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_expense_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit/payment-status', name: 'app_expense_edit_payment_status', methods: ['GET', 'POST'])]
    public function editPaymentStatus(Expense $expense, EntityManagerInterface $entityManager): JsonResponse
    {
        $paid = !$expense->isPaid();
        if ($expense->getLettrage() === null) {
            $expense->setPaid($paid);
            $entityManager->persist($expense);
            $entityManager->flush();
        }

        return new JsonResponse(['status' => $paid]);
    }
}
