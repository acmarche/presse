<?php
/**
 * This file is part of presse application.
 *
 * @author jfsenechal <jfsenechal@gmail.com>
 * @date 14/10/19
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcMarche\Presse\Service;

use AcMarche\Presse\Entity\Article;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\RouterInterface;

class PresseService
{
    public function __construct(
        #[Autowire(env: 'PRESSE_URI')]
        private readonly string $uri,
        private readonly RouterInterface $router,
    ) {}

    public static function getRoles(): array
    {
        return ['ROLE_PRESSE', 'ROLE_PRESSE_ADMIN'];
    }

    /**
     * @param array|Article[] $articles
     * @return array
     */
    public function serializeArticles(array $articles): array
    {
        $data = [];
        foreach ($articles as $article) {
            $data[] = [
                'id' => $article->getId(),
                'dateArticle' => $article->dateArticle->format('Y-m-d'),
                'title' => $article->nom,
                'link' => $this->router->generate(
                    'article_show',
                    ['id' => $article->getId()],
                    RouterInterface::ABSOLUTE_URL,
                ),
                'image' => $this->uri.'files/'.$article->getAlbum()->getDirectoryName(
                    ).DIRECTORY_SEPARATOR.$article->fileName,
            ];
        }

        return $data;
    }
}
