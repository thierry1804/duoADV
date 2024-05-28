<?php

namespace App\Controller;

use App\Entity\Lettrage;
use App\Form\LettrageType;
use App\Repository\LettrageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lettrage')]
class LettrageController extends AbstractController
{
    #[Route('/', name: 'app_lettrage_index', methods: ['GET'])]
    public function index(LettrageRepository $lettrageRepository): Response
    {
        return $this->render('lettrage/index.html.twig', [
            'lettrages' => $lettrageRepository->findAll(),
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
