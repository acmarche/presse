<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Form\SearchArticleType;
use AcMarche\Presse\Repository\AlbumRepository;
use AcMarche\Presse\Repository\ArticleRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Default controller.
 */
class DefaultController extends AbstractController
{
    public function __construct(
        private AlbumRepository $albumRepository,
        private ArticleRepository $articleRepository
    ) {
    }

    #[Route(path: '/', name: 'homepage')]
    public function index(): Response
    {
        $end = new DateTime();
        $end->modify('-7 months');
        $albums = $this->albumRepository->getLasts($end);

        return $this->render('@AcMarchePresse/default/index.html.twig', [
            'albums' => $albums,
        ]);
    }

    #[Route(path: '/search', name: 'presse_search', methods: ['GET', 'POST'])]
    public function search(Request $request): Response
    {
        $albums = $articles = $args = [];
        if ($t = $request->request->get('keyword')) {
            $args['keyword'] = $t;
        }
        $form = $this->createForm(SearchArticleType::class, $args);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $albums = $this->albumRepository->search($data);
            $articles = $this->articleRepository->search($data);
        }

        return $this->render(
            '@AcMarchePresse/default/search.html.twig',
            [
                'albums' => $albums,
                'articles' => $articles,
                'form' => $form->createView(),
            ]
        );
    }

    #[Route(path: 'formsearch', name: 'presse_form_search')]
    public function formsearch(): Response
    {
        $form = $this->createForm(
            SearchArticleType::class,
            [
            ],
            [
                'action' => $this->generateUrl('presse_search'),
            ]
        );

        return $this->render(
            '@AcMarchePresse/_form_search_inline.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
