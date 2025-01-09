<?php

namespace AcMarche\Presse\Command;

use AcMarche\Presse\Service\SyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'presse:sync-destinataires',
    description: 'Sync ldap',
)]
class SyncUserCommand extends Command
{
    public function __construct(
        private readonly SyncService $syncService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->syncService->syncAll();
        $this->syncService->removeOld();

        return Command::SUCCESS;
    }
}
