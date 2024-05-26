<?php

namespace App\Controller;

use App\Entity\Sale;
use App\Form\SaleType;
use App\Repository\SaleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sale')]
class SaleController extends AbstractController
{
    #[Route('/', name: 'app_sale_index', methods: ['GET'])]
    public function index(SaleRepository $saleRepository): Response
    {
        $sale = new Sale();
        $sale->setRecordedAt(new \DateTimeImmutable());
        $sale->setQty(0);
        $sale->setQtyReturned(0);
        $sale->setSoldOn(new \DateTime());
        $sale->setRegisteredBy($this->getUser());
        return $this->render('sale/index.html.twig', [
            'sales' => $saleRepository->findBy([], ['recordedAt' => 'DESC']),
            'form' => $this->createForm(SaleType::class, $sale),
        ]);
    }

    #[Route('/new', name: 'app_sale_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sale = new Sale();
        $form = $this->createForm(SaleType::class, $sale);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sale);
            $entityManager->flush();

            return $this->redirectToRoute('app_sale_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sale/new.html.twig', [
            'sale' => $sale,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sale_show', methods: ['GET'])]
    public function show(Sale $sale): Response
    {
        return $this->render('sale/show.html.twig', [
            'sale' => $sale,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sale_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sale $sale, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SaleType::class, $sale);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sale_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sale/edit.html.twig', [
            'sale' => $sale,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sale_delete', methods: ['POST'])]
    public function delete(Request $request, Sale $sale, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sale->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($sale);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sale_index', [], Response::HTTP_SEE_OTHER);
    }
}
