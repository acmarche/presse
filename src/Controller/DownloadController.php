<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Vich\UploaderBundle\Handler\DownloadHandler;

class DownloadController extends AbstractController
{
    #[Route(path: '/download/{id}', name: 'article_download')]
    public function downloadImageAction(Article $article, DownloadHandler $downloadHandler): StreamedResponse
    {
        return $downloadHandler->downloadObject($article, $fileField = 'file');
    }
}
