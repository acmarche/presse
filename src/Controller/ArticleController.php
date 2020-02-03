<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Entity\Album;
use AcMarche\Presse\Entity\Article;
use AcMarche\Presse\Form\ArticlesEditType;
use AcMarche\Presse\Form\UploadType;
use AcMarche\Presse\Form\ArticleType;
use AcMarche\Presse\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @var ArticleRepository
     */
    private $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * @Route("/", name="article_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/new/{id}", name="article_new", methods={"GET"})
     * @IsGranted("ROLE_PRESSE_ADMIN")
     */
    public function new(Album $album): Response
    {
        $article = new Article($album);
        $article->setAlbum($album);

        $form = $this->createForm(
            UploadType::class,
            [],
            [
                'action' => $this->generateUrl('presse_upload', ['id' => $album->getId()]),
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

    /**
     * @Route("/{id}", name="article_show", methods={"GET"})
     */
    public function show(Article $article): Response
    {
        return $this->render(
            '@AcMarchePresse/article/show.html.twig',
            [
                'article' => $article,
            ]
        );
    }

    /**
     * @Route("/edit/{id}", name="article_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_PRESSE_ADMIN")
     */
    public function edit(Request $request, Article $article): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'L\' article ont été modifié');

            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        return $this->render(
            '@AcMarchePresse/article/edit.html.twig',
            [
                'article' => $article,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="article_delete", methods={"DELETE"})
     * @IsGranted("ROLE_PRESSE_ADMIN")
     */
    public function delete(Request $request, Article $article): Response
    {
        $album = $article->getAlbum();
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($article);
            $entityManager->flush();
            $this->addFlash('success', 'L\'article a bien été supprimé');
        }

        return $this->redirectToRoute('album_show',['id'=>$album->getId()]);
    }
}
