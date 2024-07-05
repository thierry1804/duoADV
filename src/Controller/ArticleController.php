<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/article')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->getArticles(),
        ]);
    }

    #[Route('/update-price', name: 'app_article_update_price')]
    public function updateSellPrice(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $articles = $articleRepository->findBy(['sellPrice' => 0], ['label' => 'ASC']);
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $data = array_filter($data['article'], function($dataArticle) {
                return $dataArticle['sellprice'] > 0;
            });

            foreach ($data as $articleData) {
                $article = $articleRepository->find($articleData['id']);
                $article->setSellPrice($articleData['sellprice']);
                $entityManager->persist($article);
            }
            $entityManager->flush();
            return $this->redirectToRoute('app_article_index');
        }
        return $this->render('article/maj_sell_price.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/stock/adjust/{initialQty}', name: 'app_article_stock',
        requirements: ['id' => '\d+', 'initialQty' => '\d+'], methods: ['GET'])]
    public function adjustStock(Article $article, int $initialQty, EntityManagerInterface $entityManager): Response
    {
        $diff = $article->getInStock() - $initialQty;
        $article->setInStock($initialQty);
        $entityManager->persist($article);

        //fix movements
        $movements = $article->getMovements();
        foreach ($movements as $movement) {
            $movement->setStockBefore($movement->getStockBefore() - $diff);
            $movement->setStockAfter($movement->getStockAfter() - $diff);
            $entityManager->persist($movement);
        }

        $entityManager->flush();
        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
