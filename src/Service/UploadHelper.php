<?php

namespace AcMarche\Presse\Service;

use AcMarche\Presse\Entity\Album;
use AcMarche\Presse\Entity\Article;
use AcMarche\Presse\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Handler\UploadHandler;

class UploadHelper
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly UploadHandler $uploadHandler,
    ) {}

    /**
     * @param UploadedFile $file
     * @param Album $album
     * @return void
     * @throws \Exception
     */
    public function treatmentFile(UploadedFile $file, Album $album): void
    {
        $article = new Article($album);
        $article->dateArticle = $album->getDateAlbum();

        $orignalName = preg_replace(
            '#.'.$file->guessClientExtension().'#',
            '',
            $file->getClientOriginalName(),
        );
        $fileName = $orignalName.'-'.uniqid().'.'.$file->guessClientExtension();
        $nom = str_replace('.'.$file->getClientOriginalExtension(), '', $file->getClientOriginalName());

        $article->nom = $nom;
        $article->mime = $file->getMimeType();
        $article->fileName = $fileName;
        $article->file = $file;
        try {
            $this->uploadHandler->upload($article, 'file');
        } catch (\Exception $exception) {
            throw new \Exception('Erreur upload image: '.$exception->getMessage());
        }

        $this->articleRepository->persist($article);
        $this->articleRepository->flush();
    }

}