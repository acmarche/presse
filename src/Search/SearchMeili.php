<?php

namespace AcMarche\Presse\Search;

use AcMarche\Presse\Repository\ArticleRepository;
use Carbon\CarbonInterface;
use Meilisearch\Search\SearchResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SearchMeili
{
    use MeiliTrait;

    public string $query = '';

    public function __construct(
        #[Autowire(env: 'MEILI_INDEX_PRESSE_NAME')]
        private string $indexName,
        #[Autowire(env: 'MEILI_MASTER_KEY')]
        private string $masterKey,
        private readonly ArticleRepository $articleRepository,
    ) {}

    /**
     * @param array $args
     * @param int $limit
     * @param array $sort
     * @return SearchResult
     * @throws \Exception
     */
    public function search(
        array $args,
        int $limit = 150,
        array $sort = [],
    ): SearchResult {
        $filters = [];
        $filters = array_filter($filters, fn($item) => $item !== null);
        $filter = implode(' AND ', $filters);

        $this->init();
        $index = $this->client->index($this->indexName);
        $keyword = $args['keyword'] ?? '';

        $this->query = $filter;

        return $index->search($keyword, [
            'limit' => $limit,
            'filter' => $filter,
            //  'sort' => ['date_courrier_timestamp:desc', 'expediteur:asc'],
            'facets' => $this->filterableAttributes,
        ]);
    }

    /**
     * https://www.meilisearch.com/docs/learn/fine_tuning_results/filtering
     * @param string $keyword
     * @return iterable|SearchResult
     */
    public function doSearch(string $keyword): iterable|SearchResult
    {
        $this->init();

        return $this->index->search($keyword, []);
    }

    private function addDates(?CarbonInterface $date_debut, ?CarbonInterface $date_fin): ?string
    {
        if (!$date_fin && !$date_debut) {
            return null;
        }

        if (!$date_fin instanceof CarbonInterface) {
            return 'article_courrier_timestamp = '.$date_debut->getTimestamp();
        }

        if ($date_debut->format('Y-m-d') === $date_fin->format('Y-m-d')) {
            return 'article_courrier_timestamp = '.$date_debut->getTimestamp();
        }

        return 'article_courrier_timestamp >= '.$date_debut->getTimestamp(
            ).' AND article_courrier_timestamp <= '.$date_fin->getTimestamp();
    }
}