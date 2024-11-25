<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Form\SearchArticleType;
use AcMarche\Presse\Form\SearchInlineType;
use AcMarche\Presse\Repository\AlbumRepository;
use AcMarche\Presse\Search\SearchMeili;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    public function __construct(
        private readonly AlbumRepository $albumRepository,
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

    #[Route(path: '/search/{keyword}', name: 'presse_search', methods: ['GET', 'POST'])]
    public function search(Request $request, ?string $keyword = null): Response
    {
        $keyword =$request->query->get('keyword');
        $articles = [];
        $count = 0;
        $year = null;

        $form = $this->createForm(SearchArticleType::class, ['keyword' => $keyword]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $keyword = $data['keyword'];
            $year = $data['year'];
        } else {
            dump($form->getErrors());
        }

        if ($keyword) {
            try {
                $result = $this->searchMeili->search($keyword, $year);
                $articles = $result->getHits();
                $count = $result->count();
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $response = new Response(null, $form->isSubmitted() ? Response::HTTP_ACCEPTED : Response::HTTP_OK);

        return $this->render(
            '@AcMarchePresse/default/search.html.twig',
            [
                'articles' => $articles,
                'count' => $count,
                'form' => $form->createView(),
                'search' => $form->isSubmitted(),
                'keyword' => $keyword,
            ]
            , $response,
        );
    }

    #[Route(path: '/inline/search/form', name: 'presse_search_form')]
    public function searchForm(): Response
    {
        $form = $this->createForm(
            SearchInlineType::class,
            [],
            [
                'method' => 'GET',
                'action' => $this->generateUrl('presse_search'),
            ],
        );

        return $this->render(
            '@AcMarchePresse/default/_form_search.html.twig',
            [
                'form' => $form->createView(),
            ],
        );
    }
}
