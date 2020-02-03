<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Form\SearchArticleType;
use AcMarche\Presse\Repository\AlbumRepository;
use AcMarche\Presse\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Default controller.
 *
 *
 */
class DefaultController extends AbstractController
{
    /**
     * @var AlbumRepository
     */
    private $albumRepository;
    /**
     * @var ArticleRepository
     */
    private $articleRepository;

    public function __construct(AlbumRepository $albumRepository, ArticleRepository $articleRepository)
    {
        $this->albumRepository = $albumRepository;
        $this->articleRepository = $articleRepository;
    }

    /**
     * @Route("/", name="homepage")
     *
     */
    public function index()
    {
        $end = new \DateTime();
        $end->modify('-7 months');
        $albums = $this->albumRepository->getLasts($end);

        return $this->render('@AcMarchePresse/default/index.html.twig', ['albums' => $albums]);
    }

    /**
     * @Route("/search", name="presse_search", methods={"GET","POST"})
     *
     */
    public function search(Request $request)
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

    public function formsearch()
    {
        $form = $this->createForm(
            SearchArticleType::class,
            [
            ],
            [
                'action'=>$this->generateUrl('presse_search'),
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
