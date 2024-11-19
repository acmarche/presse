<?php

namespace AcMarche\Presse\Command;

use AcMarche\Presse\Repository\ArticleRepository;
use AcMarche\Presse\Search\Ocr;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
    public function __construct(
        private readonly Ocr $ocr,
        private readonly ArticleRepository $articleRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', "force", InputOption::VALUE_NONE, 'Delete old ocr txt');
        $this->addOption('check', "check", InputOption::VALUE_NONE, 'Check ocr txt exist');
        $this->addArgument('year', InputArgument::OPTIONAL, default: (int)date('Y'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $force = (bool)$input->getOption('force');
        $check = (bool)$input->getOption('check');
        $year = $input->getArgument('year');
        $currentYear = (int)date('Y');

        $years = range($year, $currentYear);
        if ($check) {
            foreach ($years as $year) {
                foreach ($this->articleRepository->findByYear($year) as $article) {
                    $articleFile = $this->ocr->articleFile($article);

                    if (!$this->ocr->fileExists($articleFile)) {
                        continue;
                    }

                    if (!$this->ocr->ocrFile($article)) {
                        $io->writeln($article->getDateArticle()->format('d-m-Y').' | '.$article->getId());
                    }
                }

                return Command::SUCCESS;
            }
        }

        foreach ($years as $year) {
            foreach ($this->articleRepository->findByYear($year) as $article) {
                $articleFile = $this->ocr->articleFile($article);

                if (!$this->ocr->fileExists($articleFile)) {
                    continue;
                }

                $io->writeln($articleFile);

                if ($this->ocr->fileExists($this->ocr->ocrFile($article))) {
                    if ($force) {
                        try {
                            $this->ocr->filesystem->remove($this->ocr->ocrFile($article));
                        } catch (\Exception $e) {
                            $io->error($e->getMessage());
                            continue;
                        }
                    } else {
                        continue;
                    }
                }

                $tmpDirectory = $this->ocr->tmpDirectory();
                try {
                    $this->ocr->createAndCleanTmpDirectory($tmpDirectory);
                } catch (\Exception $e) {
                    $io->error($e->getMessage());
                    continue;
                }

                if ($article->getMime() === 'application/pdf') {
                    try {
                        $this->ocr->convertToImages($articleFile, $tmpDirectory);
                    } catch (\Exception $e) {
                        $io->error($e->getMessage());
                        continue;
                    }
                    try {
                        $this->ocr->convertToTxt($article, tmpDirectory: $tmpDirectory);
                    } catch (\Exception $e) {
                        $io->error($e->getMessage());
                        continue;
                    }
                } else {
                    try {
                        $this->ocr->convertToTxt($article, filePath: $articleFile);
                    } catch (\Exception $e) {
                        $io->error($e->getMessage());
                        continue;
                    }
                }

                try {
                    $this->ocr->createAndCleanTmpDirectory($tmpDirectory);
                } catch (\Exception $e) {
                    $io->error($e->getMessage());
                    continue;
                }
            }
        }

        return Command::SUCCESS;
    }

}
