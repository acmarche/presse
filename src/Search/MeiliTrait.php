<?php

namespace AcMarche\Presse\Search;

use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;

trait MeiliTrait
{
    public ?Client $client = null;

    private array $filterableAttributes = [
        'article_courrier_timestamp',
    ];
    private array $sortableAttributes = [
        'article_courrier_timestamp',
    ];
    private Indexes|null $index = null;

    public function init(): void
    {
        if (!$this->client) {
            $this->client = new Client('http://127.0.0.1:7700', $this->masterKey);
        }

        if (!$this->index) {
            $this->index = $this->client->index($this->indexName);
        }
    }
}