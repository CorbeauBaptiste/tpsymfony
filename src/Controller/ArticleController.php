<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/article/create', name: 'app_article_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger,  #[Autowire('%kernel.project_dir%/public/uploads/brochures')] string $brochuresDirectory): Response
    {
        $article = new Article;
        $article->setTitre("Mon premier article")
            ->setText("Du texte qui presente mon article présentement")
            ->setPublie(1)
            ->setDate(new DateTimeImmutable());
        $formulaire = $this->createForm(ArticleType::class, $article);

        $formulaire->handleRequest($request);
        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $brochureFile = $formulaire->get('brochure')->getData();
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                try {
                    $brochureFile->move($brochuresDirectory, $newFilename);
                } catch (FileException $e) {
                }

                $article->setBrochureFilename($newFilename);
            }
            $article = $formulaire->getData();

            $entityManager->persist($article);
            $entityManager->flush();
            $this->addFlash('success', 'Article Created! Knowledge is power!');

            return $this->redirectToRoute('app_article_fetch_list');
        }

        return $this->render('article/create.html.twig', [
            'form' => $formulaire
        ]);
    }

    #[Route('/article/fetchlist', name: 'app_article_fetch_list')]
    public function fetchlist(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('article/fetchlist.html.twig', [
            'articles' => $articles
        ]);
    }

    #[Route('/article/update/{id}', name: 'app_article_update')]
    public function update(Request $request, EntityManagerInterface $entitymanager, int $id): Response
    {
        $article = $entitymanager->getRepository(article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException(
                'No article found for id ' . $id
            );
        }

        $formulaire = $this->createForm(ArticleType::class, $article);

        $formulaire->handleRequest($request);
        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $article = $formulaire->getData();
            $entitymanager->flush();
            $this->addFlash('success', 'Article Updated!');

            return $this->redirectToRoute('app_article_fetch_list');
        }

        return $this->render('article/create.html.twig', [
            'form' => $formulaire
        ]);
    }

    #[Route('/article/delete/{id}', name: 'app_article_delete')]
    public function delete(EntityManagerInterface $entitymanager, int $id): Response
    {
        $article = $entitymanager->getRepository(article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException(
                'No article found for id ' . $id
            );
        }

        $retourid = $id;

        $entitymanager->remove($article);
        $entitymanager->flush();
        $this->addFlash('success', 'Article Deleted!');
        return $this->redirectToRoute('app_article_fetch_list');
    }

    public function creatingForm(Request $request): Response
    {
        $articleform = new Article();

        $formulaire = $this->createForm(ArticleType::class, $articleform);

        return $this->render('article/create.hmtl.twig', [
            'form' => $formulaire
        ]);
    }
}