<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Entity\Album;
use AcMarche\Presse\Entity\Article;
use AcMarche\Presse\Form\ArticlesEditType;
use AcMarche\Presse\Repository\ArticleRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Handler\UploadHandler;

#[IsGranted('ROLE_PRESSE_ADMIN')]
class UploadController extends AbstractController
{
    public function __construct(
        private UploadHandler $uploadHandler,
        private ArticleRepository $articleRepository,
    ) {
    }

    #[Route(path: '/upload/{id}', name: 'presse_upload')]
    public function upload(Request $request, Album $album): Response
    {
        $article = new Article($album);
        /**
         * @var UploadedFile $file
         */
        $file = $request->files->get('file');
        $nom = str_replace('.'.$file->getClientOriginalExtension(), '', $file->getClientOriginalName());
        $article->setNom($nom);
        $article->setMime($file->getMimeType());
        $article->setFileName($file->getClientOriginalName());
        $article->setDateArticle($album->getDateAlbum());
        $article->setFile($file);
        try {
            $this->uploadHandler->upload($article, 'file');
        } catch (Exception $exception) {
            return $this->render('@AcMarchePresse/upload/_response_fail.html.twig', [
                'error' => $exception->getMessage(),
            ]);
        }
        $this->articleRepository->persist($article);
        $this->articleRepository->flush();

        return $this->render('@AcMarchePresse/upload/_response_ok.html.twig');
    }

    #[Route(path: '/edit/{id}', name: 'upload_edit', methods: ['GET', 'POST'])]
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
            ]
        );
    }
}
