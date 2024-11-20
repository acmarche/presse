<?php

namespace AcMarche\Presse\Command;

use AcMarche\Presse\Entity\Article;
use AcMarche\Presse\Repository\ArticleRepository;
use AcMarche\Presse\Search\Ocr;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ocr:presse',
    description: 'Extract texts',
)]
class OcrCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly Ocr $ocr,
        private readonly ArticleRepository $articleRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Delete old ocr txt');
        $this->addOption('check', null, InputOption::VALUE_NONE, 'Check ocr txt exist');
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'Id article');
        $this->addOption('year', null, InputOption::VALUE_REQUIRED, 'Depuis cette année', default: date('Y'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $force = (bool)$input->getOption('force');
        $check = (bool)$input->getOption('check');
        $id = (int)$input->getOption('id');
        $year = (int)$input->getOption('year');
        $currentYear = (int)date('Y');
        $years = range($year, $currentYear);

        if ($id) {
            if (!$article = $this->articleRepository->find($id)) {
                $this->io->error('Courier not found');

                return Command::FAILURE;
            }
            $this->treatment($article, $force);

            return Command::SUCCESS;
        }

        if ($check) {
            foreach ($years as $year) {
                foreach ($this->articleRepository->findByYear($year) as $article) {
                    $articleFile = $this->ocr->articleFile($article);

                    if (!$this->ocr->fileExists($articleFile)) {
                        continue;
                    }

                    if (!$this->ocr->ocrFile($article)) {
                        $this->io->writeln($article->getDateArticle()->format('d-m-Y').' | '.$article->getId());
                    }
                }

                return Command::SUCCESS;
            }
        }

        foreach ($years as $year) {
            foreach ($this->articleRepository->findByYear($year) as $article) {
                $this->treatment($article, $force);
            }
        }

        return Command::SUCCESS;
    }

    private function treatment(Article $article, bool $force): void
    {
        $courierFile = $this->ocr->articleFile($article);
        $this->io->writeln($courierFile);
        $this->io->writeln($this->ocr->ocrFile($article));
        $tmpDirectory = $this->ocr->tmpDirectory();

        if (!$this->ocr->fileExists($courierFile)) {
            return;
        }

        if (!$force) {
            if ($this->ocr->fileExists($this->ocr->ocrFile($article))) {
                $this->io->warning('ocr file already exist');

                return;
            }
        }

        try {
            $this->ocr->cleanTmpDirectory($tmpDirectory);
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());

            return;
        }

        if ($this->ocr->isCleanTmpDirectory($tmpDirectory)) {
            $this->io->error('Le dossier temporaire n\'est pas vide !');

            return;
        }

        $this->io->writeln($article->getMime());
        if (!str_contains($article->getMime(), 'image')) {
            try {
                $this->ocr->convertToImages($courierFile, $tmpDirectory);
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());

                return;
            }
        }

        try {
            $this->ocr->convertToTxt($article, $article->getMime(), $tmpDirectory);
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());

            return;
        }
        try {
            $this->ocr->cleanTmpDirectory($tmpDirectory);
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());

            return;
        }
    }
}
