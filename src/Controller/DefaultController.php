<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Form\SearchArticleType;
use AcMarche\Presse\Repository\AlbumRepository;
use AcMarche\Presse\Repository\ArticleRepository;
use AcMarche\Presse\Search\SearchMeili;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    public function __construct(
        private readonly AlbumRepository $albumRepository,
        private readonly ArticleRepository $articleRepository,
        private readonly SearchMeili $searchMeili,
    ) {}

    #[Route(path: '/', name: 'homepage')]
    public function index(): Response
    {
        $end = new DateTime();
        $end->modify('-12 months');
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
            $this->searchMeili->search($args['keyword']);
            dd($data);
            $albums = $this->albumRepository->search($data);
            $articles = $this->articleRepository->search($data);
        }

        return $this->render(
            '@AcMarchePresse/default/search.html.twig',
            [
                'albums' => $albums,
                'articles' => $articles,
                'form' => $form->createView(),
            ],
        );
    }
}
