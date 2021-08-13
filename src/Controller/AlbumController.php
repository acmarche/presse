<?php

namespace AcMarche\Presse\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Exception;
use DateTime;
use AcMarche\Presse\Entity\Album;
use AcMarche\Presse\Form\AlbumType;
use AcMarche\Presse\Repository\AlbumRepository;
use AcMarche\Presse\Repository\ArticleRepository;
use AcMarche\Presse\Service\AlbumService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/album")
 */
class AlbumController extends AbstractController
{
    private AlbumRepository $albumRepository;
    private AlbumService $albumService;
    private ArticleRepository $articleRepository;

    public function __construct(
        AlbumRepository $albumRepository,
        AlbumService $albumService,
        ArticleRepository $articleRepository
    ) {
        $this->albumRepository = $albumRepository;
        $this->albumService = $albumService;
        $this->articleRepository = $articleRepository;
    }

    /**
     * @Route("/", name="album_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render(
            '@AcMarchePresse/album/index.html.twig',
            [
                'albums' => $this->albumRepository->getRoots(),
            ]
        );
    }

    /**
     * @Route("/new", name="album_new", methods={"GET","POST"})
     * @Route("/new/{id}", name="album_add_child", methods={"GET","POST"})
     * @param Request $request
     * @param Album|null $parent
     * @return Response
     * @throws Exception
     * @IsGranted("ROLE_PRESSE_ADMIN")
     */
    public function new(Request $request, ?Album $parent = null): Response
    {
        $album = new Album();
        $today = new DateTime();
        $album->setDateAlbum($today);

        if ($parent !== null) {
            $album->setParent($parent);
        }
        else {
            $album->setDateAlbum(new DateTime('first day of this month'));
        }

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $album->setDirectoryName(AlbumService::getDirectory($album));

            $entityManager->persist($album);
            $entityManager->flush();


            if ($album->getImage() === null) {
                try {
                    $this->albumService->createFolder($album);
                } catch (IOException $exception) {
                    $this->addFlash('danger', 'Le répertoire n\'a pas su être créé '.$exception->getMessage());
                }
            }

            $this->addFlash('success', 'L\'album a bien été créé');

            return $this->redirectToRoute('album_show', ['id' => $album->getId()]);
        }

        return $this->render(
            '@AcMarchePresse/album/new.html.twig',
            [
                'album' => $album,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="album_show", methods={"GET"})
     */
    public function show(Album $album): Response
    {
        $paths = $this->albumService->getPath($album);
        $articles = $this->articleRepository->findByAlbum($album);
        $childs = $this->albumRepository->getChilds($album);

        return $this->render(
            '@AcMarchePresse/album/show.html.twig',
            [
                'album' => $album,
                'childs' => $childs,
                'paths' => $paths,
                'articles' => $articles,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="album_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_PRESSE_ADMIN")
     */
    public function edit(Request $request, Album $album): Response
    {
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'L\'album a bien été modifié');

            return $this->redirectToRoute('album_show', ['id' => $album->getId()]);
        }

        return $this->render(
            '@AcMarchePresse/album/edit.html.twig',
            [
                'album' => $album,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="album_delete", methods={"DELETE"})
     * @IsGranted("ROLE_PRESSE_ADMIN")
     */
    public function delete(Request $request, Album $album): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$album->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($album);
            $entityManager->flush();
            $this->addFlash('success', 'L\'album a bien été supprimé');
        }

        return $this->redirectToRoute('album_index');
    }
}
