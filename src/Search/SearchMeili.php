<?php

namespace AcMarche\Presse\Search;

use AcMarche\Presse\Entity\Destinataire;
use AcMarche\Presse\Repository\ArticleRepository;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Meilisearch\Search\SearchResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @param UserInterface $user
     * @param int $limit
     * @param array $sort
     * @return SearchResult
     * @throws \Exception
     */
    public function search(
        array $args,
        UserInterface $user,
        int $limit = 150,
        array $sort = [],
    ): SearchResult {
        $filters = $this->setQuery($args, $user);
        $filters = array_filter($filters, fn($item) => $item !== null);
        $filter = implode(' AND ', $filters);

        $this->init();
        $index = $this->client->index($this->indexName);
        $keyword = $args['nom'] ?? '';

        $this->query = $filter;

        return $index->search($keyword, [
            'limit' => $limit,
            'filter' => $filter,
            'sort' => ['date_courrier_timestamp:desc', 'expediteur:asc'],
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

    /**
     * @param array $args
     * @param UserInterface|null $user
     * @return string
     * @throws \Exception
     */
    private function setQuery(array $args, ?UserInterface $user): array
    {
        $expediteur = $args['expediteur'] ?? null;
        $numero = $args['numero'] ?? null;
        $destinataire = $args['destinataire'] ?? null;
        $service = $args['service'] ?? null;
        $date_debut = $args['date_debut'] ?? null;
        $date_fin = $args['date_fin'] ?? null;

        if (!$date_debut instanceof CarbonInterface && $date_debut !== null) {
            $date_debut = Carbon::createFromDate(
                $date_debut->format('Y'),
                $date_debut->format('m'),
                $date_debut->format('d'),
                'UTC',
            )->hour(0)->minute(0)->second(0);
        }
        if (!$date_fin instanceof Carbon && $date_fin !== null) {
            $date_fin = Carbon::createFromDate(
                $date_fin->format('Y'),
                $date_fin->format('m'),
                $date_fin->format('d'),
                'UTC',
            )->hour(0)->minute(0)->second(0);
        }

        $filters = [];

        if ($numero) {
            return ['numero = '.$numero];
        }

        if ($expediteur) {
            $filters['expediteur'] = 'expediteur CONTAINS '.$expediteur;
        }

        if ($service instanceof Service) {
            $filters['services'] = 'services = '.$service->getId();
        }

        if ($destinataire instanceof Destinataire) {
            $filters['destinataires'] = 'destinataires = '.$destinataire->getId();
        }

        if ($dates = $this->addDates($date_debut, $date_fin)) {
            $filters['dates'] = $dates;
        }

        if (
            !$user->hasRole('ROLE_INTRANET_ADMIN') &&
            !$user->hasRole('ROLE_INDICATEUR_VILLE_READ') &&
            !$user->hasRole('ROLE_INDICATEUR_VILLE_ADMIN') &&
            !$user->hasRole('ROLE_INDICATEUR_VILLE_INDEX')
        ) {
            $filters = [...$filters, ...$this->setConstaintUser($user->getUserIdentifier(), $service)];
        }

        return $filters;
    }

    /**
     * @param string $userName
     * @param Service|null $serviceSelected
     * @return array
     * @throws \Exception
     * date_courrier_timestamp >= 1730934000 AND date_courrier_timestamp <= 1731020400
     * AND (destinataires = 6)
     * OR (services = 9)
     */
    private function setConstaintUser(
        string $userName,
        ?Service $serviceSelected,
    ): array {
        if (!$destinataire = $this->destinataireRepository->findOneByUsername($userName)) {
            throw new \Exception('Vous n\'avez pas été trouvé dans la liste des destinataires');
        }
        $services = $this->serviceRepository->findByDestinataires([$destinataire]);
        if ($serviceSelected) {
            if (count($services) === 0) {
                throw new \Exception('Vous n\'êtes dans aucun service');
            }
            if (!$serviceSelected->isServiceInArray($services)) {
                throw new \Exception('Le service sélectionné n\'est pas dans la liste de vos services');
            }

            return ['services' => 'services = '.$serviceSelected->getId()];
        } else {
            $filters = [];
            $servicesFilter = [];
            foreach ($services as $service) {
                $servicesFilter[] = 'services = '.$service->getId();
            }
            $servicesFilter = '('.implode(' OR ', $servicesFilter).')';
            $filters['services'] = '((destinataires = '.$destinataire->getId().') OR '.$servicesFilter.')';
            $filters['destinataires'] = null;
        }

        return $filters;
    }

    private function addDates(?CarbonInterface $date_debut, ?CarbonInterface $date_fin): ?string
    {
        if (!$date_fin && !$date_debut) {
            return null;
        }

        if (!$date_fin instanceof CarbonInterface) {
            return 'date_courrier_timestamp = '.$date_debut->getTimestamp();
        }

        if ($date_debut->format('Y-m-d') === $date_fin->format('Y-m-d')) {
            return 'date_courrier_timestamp = '.$date_debut->getTimestamp();
        }

        return 'date_courrier_timestamp >= '.$date_debut->getTimestamp(
            ).' AND date_courrier_timestamp <= '.$date_fin->getTimestamp();
    }
}