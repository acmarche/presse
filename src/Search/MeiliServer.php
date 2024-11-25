<?php

namespace AcMarche\Presse\Search;

use AcMarche\Presse\Entity\Article;
use AcMarche\Presse\Repository\ArticleRepository;
use Carbon\Carbon;
use Meilisearch\Contracts\DeleteTasksQuery;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Endpoints\Keys;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MeiliServer
{
    use MeiliTrait;

    private string $primaryKey = 'idSearch';
    private ?Indexes $index = null;

    public function __construct(
        #[Autowire(env: 'MEILI_INDEX_PRESSE_NAME')]
        private string $indexName,
        #[Autowire(env: 'MEILI_MASTER_KEY')]
        private string $masterKey,
        private readonly ArticleRepository $articleRepository,
        private readonly Ocr $ocr,
    ) {}

    /**
     *
     * @return array<'taskUid','indexUid','status','enqueuedAt'>
     */
    public function createIndex(): array
    {
        $this->init();
        $this->client->deleteTasks((new DeleteTasksQuery())->setStatuses(['failed', 'canceled', 'succeeded']));
        $this->client->deleteIndex($this->indexName);

        return $this->client->createIndex($this->indexName, ['primaryKey' => $this->primaryKey]);
    }

    /**
     * https://raw.githubusercontent.com/meilisearch/meilisearch/latest/config.toml
     * curl -X PATCH 'http://localhost:7700/experimental-features/' -H 'Content-Type: application/json' -H 'Authorization: Bearer xxxxxx' --data-binary '{"containsFilter": true}'
     * @return array
     */
    public function settings(): array
    {
        $this->client->index($this->indexName)->updateFilterableAttributes($this->filterableAttributes);

        return $this->client->index($this->indexName)->updateSortableAttributes($this->sortableAttributes);
    }

    public function createApiKey(): Keys
    {
        $this->init();

        return $this->client->createKey([
            'description' => 'presse API key',
            'actions' => ['*'],
            'indexes' => [$this->indexName],
            'expiresAt' => '2042-04-02T00:42:42Z',
        ]);
    }

    public function addArticle(Article $article): void
    {
        $document = $this->createDocument($article);
        $this->init();
        $index = $this->client->index($this->indexName);
        $index->addDocuments([$document], $this->primaryKey);
    }

    public function addArticles(int $year): void
    {
        $this->init();
        $documents = [];

        foreach ($this->articleRepository->findByYear($year) as $article) {
            $documents[] = $this->createDocument($article);
        }
        $index = $this->client->index($this->indexName);
        $index->addDocuments($documents, $this->primaryKey);
    }

    public function createDocument(Article $article): array
    {
        $document = [];
        $document['id'] = $article->getId();
        $document['idSearch'] = MeiliServer::createKey($article->getId());
        $document['nom'] = Cleaner::cleandata($article->nom);
        $document['description'] = Cleaner::cleandata($article->description);
        $document['album'] = $article->getAlbum()?->getId();
        $document['date_article'] = $article->dateArticle->format('Y-m-d');
        $document['year'] = $article->dateArticle->format('Y');
        $date = $article->dateArticle;
        $dateArticle = Carbon::createFromDate(
            $date->format('Y'),
            $date->format('m'),
            $date->format('d'),
            'UTC',
        )->hour(0)->minute(0)->second(0);
        $document['date_article_timestamp'] = $dateArticle->getTimestamp();
        $content = '';
        $ocrFile = $this->ocr->ocrFile($article);
        if (file_exists($ocrFile)) {
            $content = Cleaner::cleandata(file_get_contents($ocrFile));
        }
        $document['content'] = $content;

        return $document;
    }

    public function updateArticle(Article $article): void
    {
        $this->init();
        $documents = [$this->createDocument($article)];
        $index = $this->client->index($this->indexName);
        $index->addDocuments($documents, $this->primaryKey);
    }

    public function deleteArticle(string $id): void
    {
        $this->init();
        $index = $this->client->index($this->indexName);
        $index->deleteDocument(MeiliServer::createKey($id));
    }

    private static function createKey(int $id): string
    {
        return 'article-'.$id;
    }

}