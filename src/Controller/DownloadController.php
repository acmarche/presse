<?php

namespace AcMarche\Presse\Controller;

use Symfony\Component\HttpFoundation\StreamedResponse;
use AcMarche\Presse\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Handler\DownloadHandler;

class DownloadController extends AbstractController
{
    #[Route(path: '/download/{id}', name: 'article_download')]
    public function downloadImageAction(Article $article, DownloadHandler $downloadHandler) : StreamedResponse
    {
        return $downloadHandler->downloadObject($article, $fileField = 'file');
    }

}
