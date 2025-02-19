<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Repository\ArticleRepository;
use AcMarche\Presse\Service\PresseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api')]
class ApiController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly PresseService $presseService,
    ) {}

    #[Route(path: '/articles',methods: ['GET'])]
    public function index(): JsonResponse
    {
        $articles = $this->articleRepository->findLast(10);
        $data = $this->presseService->serializeArticles($articles);

        return $this->json($data);
    }

}
