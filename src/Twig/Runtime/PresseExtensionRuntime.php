<?php

namespace AcMarche\Presse\Twig\Runtime;

use AcMarche\Presse\Repository\ArticleRepository;
use Twig\Extension\RuntimeExtensionInterface;

class PresseExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
    ) {}

    public function articlePath(int $articleId): ?string
    {
        if ($article = $this->articleRepository->find($articleId)) {
            return '/files/'.$article->getAlbum()->getDirectoryName().DIRECTORY_SEPARATOR.$article->fileName;
        }

        return null;
    }
}
