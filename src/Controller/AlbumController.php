<?php

namespace AcMarche\Presse\Controller;

use Doctrine\Persistence\ManagerRegistry;
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

#[Route(path: '/album')]
class AlbumController extends AbstractController
{
    public function __construct(private AlbumRepository $albumRepository, private AlbumService $albumService, private ArticleRepository $articleRepository, private ManagerRegistry $managerRegistry)
    {
    }
    #[Route(path: '/', name: 'album_index', methods: ['GET'])]
    public function index() : Response
    {
        return $this->render(
            '@AcMarchePresse/album/index.html.twig',
            [
                'albums' => $this->albumRepository->getRoots(),
            ]
        );
    }
    /**
     * @throws Exception
     */
    #[Route(path: '/new', name: 'album_new', methods: ['GET', 'POST'])]
    #[Route(path: '/new/{id}', name: 'album_add_child', methods: ['GET', 'POST'])]
    #[IsGranted(data: 'ROLE_PRESSE_ADMIN')]
    public function new(Request $request, ?Album $parent = null) : Response
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
            $entityManager = $this->managerRegistry->getManager();
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
    #[Route(path: '/{id}', name: 'album_show', methods: ['GET'])]
    public function show(Album $album) : Response
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
    #[Route(path: '/{id}/edit', name: 'album_edit', methods: ['GET', 'POST'])]
    #[IsGranted(data: 'ROLE_PRESSE_ADMIN')]
    public function edit(Request $request, Album $album) : Response
    {
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->managerRegistry->getManager()->flush();

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
    #[Route(path: '/{id}', name: 'album_delete', methods: ['DELETE'])]
    #[IsGranted(data: 'ROLE_PRESSE_ADMIN')]
    public function delete(Request $request, Album $album) : RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$album->getId(), $request->request->get('_token'))) {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->remove($album);
            $entityManager->flush();
            $this->addFlash('success', 'L\'album a bien été supprimé');
        }
        return $this->redirectToRoute('album_index');
    }
}
