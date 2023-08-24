<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Entity\Album;
use AcMarche\Presse\Entity\Article;
use AcMarche\Presse\Form\ArticleType;
use AcMarche\Presse\Form\UploadType;
use AcMarche\Presse\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/article')]
class ArticleController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository,
    ) {
    }

    #[Route(path: '/', name: 'article_index', methods: ['GET'])]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('homepage');
    }

    #[Route(path: '/new/{id}', name: 'article_new', methods: ['GET'])]
    #[IsGranted('ROLE_PRESSE_ADMIN')]
    public function new(Album $album): Response
    {
        $article = new Article($album);
        $article->setAlbum($album);
        $form = $this->createForm(
            UploadType::class,
            [],
            [
                'action' => $this->generateUrl('presse_upload', [
                    'id' => $album->getId(),
                ]),
            ]
        );

        return $this->render(
            '@AcMarchePresse/article/new.html.twig',
            [
                'article' => $article,
                'album' => $album,
                'form' => $form->createView(),
            ]
        );
    }

    #[Route(path: '/{id}', name: 'article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render(
            '@AcMarchePresse/article/show.html.twig',
            [
                'article' => $article,
            ]
        );
    }

    #[Route(path: '/edit/{id}', name: 'article_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PRESSE_ADMIN')]
    public function edit(Request $request, Article $article): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->articleRepository->flush();

            $this->addFlash('success', 'L\' article ont été modifié');

            return $this->redirectToRoute('article_show', [
                'id' => $article->getId(),
            ]);
        }

        return $this->render(
            '@AcMarchePresse/article/edit.html.twig',
            [
                'article' => $article,
                'form' => $form->createView(),
            ]
        );
    }

    #[Route(path: '/{id}', name: 'article_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_PRESSE_ADMIN')]
    public function delete(Request $request, Article $article): RedirectResponse
    {
        $album = $article->getAlbum();
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {

            $this->articleRepository->remove($article);
            $this->articleRepository->flush();
            $this->addFlash('success', 'L\'article a bien été supprimé');
        }

        return $this->redirectToRoute('album_show', [
            'id' => $album->getId(),
        ]);
    }
}
