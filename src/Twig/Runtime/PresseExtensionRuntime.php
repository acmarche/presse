<?php

namespace AcMarche\Presse\Twig\Runtime;

use AcMarche\Presse\Repository\ArticleRepository;
use Twig\Extension\RuntimeExtensionInterface;

class PresseExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
    ) {}

    public function autoLinkUrls(?string $text): ?string
    {
        if (!$text) {
            return null;
        }
        // Regular expression to match URLs
        $pattern = '/(https?:\/\/[^\s]+)/i';
        $replacement = '<a href="$1" target="_blank">$1</a>';

        return preg_replace($pattern, $replacement, $text);
    }

    public function articlePath(int $articleId): ?string
    {
        if ($article = $this->articleRepository->find($articleId)) {
            return '/files/'.$article->getAlbum()->getDirectoryName().DIRECTORY_SEPARATOR.$article->fileName;
        }

        return null;
    }
}
