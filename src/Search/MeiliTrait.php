<?php

namespace AcMarche\Presse\Search;

use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;

trait MeiliTrait
{
    public ?Client $client = null;

    private array $filterableAttributes = [
        'date_courrier_timestamp',
        'recommande',
        'destinataires',
        'services',
        'numero',
        'expediteur',
    ];
    private array $sortableAttributes = [
        'date_courrier_timestamp',
        'expediteur',
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