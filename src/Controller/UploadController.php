<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Entity\Album;
use AcMarche\Presse\Entity\Article;
use AcMarche\Presse\Form\ArticlesEditType;
use AcMarche\Presse\Repository\ArticleRepository;
use AcMarche\Presse\Service\UploadHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_PRESSE_ADMIN')]
class UploadController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private readonly UploadHelper $uploadHelper,
    ) {}


    #[Route(path: '/upload/new/{id}', name: 'upload_file', methods: ['GET', 'POST'])]
    public function upload(Request $request, Album $album): Response
    {
        $file = $request->files->get('file');
        if ($file instanceof UploadedFile) {
            try {
                $this->uploadHelper->treatmentFile($file, $album);
            } catch (\Exception $e) {
                return new JsonResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }

        return new JsonResponse($_POST);
    }

    #[Route(path: '/upload/edit/{id}', name: 'upload_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Album $album): Response
    {
        $articles = $this->articleRepository->findByAlbum($album);
        if (0 == \count($articles)) {
            $this->addFlash('warning', 'Aucun articles dans cet album');

            return $this->redirectToRoute('album_show', [
                'id' => $album->getId(),
            ]);
        }
        $form = $this->createForm(ArticlesEditType::class, [
            'articles' => $articles,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->articleRepository->flush();

            $this->addFlash('success', 'Les articles ont été sauvegardés');

            return $this->redirectToRoute('album_show', [
                'id' => $album->getId(),
            ]);
        }

        return $this->render(
            '@AcMarchePresse/upload/edit.html.twig',
            [
                'articles' => $articles,
                'album' => $album,
                'form' => $form->createView(),
            ],
        );
    }
}
