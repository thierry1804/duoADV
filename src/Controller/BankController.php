<?php

namespace App\Controller;

use App\Entity\BankReconciliation;
use App\Form\BankType;
use App\Repository\BankAccountRepository;
use App\Repository\BankReconciliationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BankController extends AbstractController
{
    #[Route('/bank', name: 'app_bank')]
    public function index(BankAccountRepository $bankAccountRepository): Response
    {
        $bank = $bankAccountRepository->findOneBy([]);
        $form = $this->createForm(BankType::class, $bank);
        return $this->render('bank/index.html.twig', [
            'form' => $form,
            'bank' => $bank,
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/bank/add-reconciliation', name: 'app_bank_add_reconciliation', methods: ['POST'])]
    public function addReconciliation(Request $request, BankAccountRepository $bankAccountRepository,
                                      BankReconciliationRepository $bankReconciliationRepository,
                                      EntityManagerInterface $entityManager): Response
    {
        $bankId = $request->request->get('bankAccount');
        $bank = $bankAccountRepository->findOneBy(['id' => $bankId]);
        $reconciliation = new BankReconciliation();

        $reconciliation->setBankAccount($bank);
        $reconciliation->setOperationDate(new \DateTime($request->request->get('operationDate')));
        $reconciliation->setLabel($request->request->get('label'));
        $reconciliation->setCredit($request->request->get('credit'));
        $reconciliation->setDebit($request->request->get('debit'));

        $entityManager->persist($reconciliation);
        $entityManager->flush();

        return $this->render('bank/response.html.twig', [
            'reconciliation' => $reconciliation,
        ]);
    }
}
