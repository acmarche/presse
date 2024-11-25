<?php

namespace AcMarche\Presse\Command;

use AcMarche\Presse\Search\MeiliServer;
use AcMarche\Presse\Search\SearchMeili;
use Meilisearch\Search\SearchResult;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'meili:presse',
    description: 'Mise à jour du moteur de recherche',
)]
class MeiliCommand extends Command
{
    public function __construct(
        private readonly MeiliServer $meiliServer,
        private readonly SearchMeili $searchMeili,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('key', "key", InputOption::VALUE_NONE, 'Create a key');
        $this->addOption('tasks', "tasks", InputOption::VALUE_NONE, 'Display tasks');
        $this->addOption('reset', "reset", InputOption::VALUE_NONE, 'Search engine reset');
        $this->addOption('update', "update", InputOption::VALUE_NONE, 'Update data');
        $this->addArgument('year', InputArgument::OPTIONAL, default: (int)date('Y'));
        $this->addArgument('keyword', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $keyword = $input->getArgument('keyword');
        $key = (bool)$input->getOption('key');
        $tasks = (bool)$input->getOption('tasks');
        $reset = (bool)$input->getOption('reset');
        $update = (bool)$input->getOption('update');

        $year = $input->getArgument('year');

        if ($key) {
            dump($this->meiliServer->createApiKey());

            return Command::SUCCESS;
        }

        if ($keyword) {
            $result = $this->searchMeili->doSearch($keyword);
            $this->displayResult($io, $result);

            return Command::SUCCESS;
        }

        if ($tasks) {
            $this->tasks($output);

            return Command::SUCCESS;
        }

        if ($reset) {
            $result = $this->meiliServer->createIndex();
            dump($result);
            $result = $this->meiliServer->settings();
            dump($result);
        }

        if ($update) {
            $this->meiliServer->addArticles($year);
        }

        return Command::SUCCESS;
    }

    private function tasks(OutputInterface $output): void
    {
        $this->meiliServer->init();
        $tasks = $this->meiliServer->client->getTasks();
        $data = [];
        foreach ($tasks->getResults() as $result) {
            $t = [$result['uid'], $result['status'], $result['type'], $result['startedAt']];
            $t['error'] = null;
            $t['url'] = null;
            if ($result['status'] == 'failed') {
                if (isset($result['error'])) {
                    $t['error'] = $result['error']['message'];
                    $t['link'] = $result['error']['link'];
                }
            }
            $data[] = $t;
        }
        $table = new Table($output);
        $table
            ->setHeaders(['Uid', 'status', 'Type', 'Date', 'Error', 'Url'])
            ->setRows($data);
        $table->render();
    }

    private function displayResult(OutputInterface $output, SearchResult $searchResult): void
    {
        $data = [];
        foreach ($searchResult->getHits() as $hit) {
            $data[] = ['id' => $hit['id'], 'expediteur' => $hit['expediteur'], 'date' => $hit['date_courrier']];
        }
        $table = new Table($output);
        $table
            ->setHeaders(['Id', 'Expediteur', 'Date'])
            ->setRows($data);
        $table->render();
    }

}
