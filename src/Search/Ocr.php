<?php

namespace AcMarche\Presse\Search;

use AcMarche\Presse\Entity\Article;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class Ocr
{
    public Filesystem $filesystem;
    public static string $ocrFilename = 'ocr.txt';

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
        $this->filesystem = new Filesystem();
    }

    /**
     * @param string $outputDirectory
     * @param bool $onlyOcrImage
     * @return void
     */
    public function createAndCleanOutputDirectory(string $outputDirectory, bool $onlyOcrImage = false): void
    {
        if (!is_writable($outputDirectory)) {
            $this->filesystem->mkdir($outputDirectory);

            return;
        }
        $files = scandir($outputDirectory);
        if ($onlyOcrImage) {
            $files = array_filter($files, function ($file) use ($outputDirectory) {
                return (str_contains($file, 'ocr-image'));
            });
        } else {
            // Filter out the '.' and '..' entries
            $files = array_diff($files, ['.', '..']);
        }
        foreach ($files as $file) {
            $filePath = Path::makeAbsolute($file, $outputDirectory);
            if (is_file($filePath)) {
                $this->filesystem->remove($filePath);
            }
        }
    }

    public function convertToImages(string $file, string $outputDirectory): void
    {
        shell_exec("pdftoppm -png $file $outputDirectory/ocr-image");
    }

    public function convertToTxt(string $outputDirectory): void
    {
        $files = scandir($outputDirectory);
        $files = array_filter($files, function ($file) use ($outputDirectory) {
            return (str_contains($file, '.png'));
        });

        $i = 1;
        foreach ($files as $item) {
            $filePath = Path::makeAbsolute($item, $outputDirectory);
            shell_exec("tesseract $filePath $outputDirectory/text-$i --oem 1 --psm 3 -l fra logfile");
            $i++;
        }

        shell_exec("cat $outputDirectory/text-* > $outputDirectory/".Ocr::$ocrFilename);
    }

    public function dataDirectory(): string
    {
        return $this->projectDir.'/public/files/';
    }

    public function outputDirectory(Article $article): string
    {
        return $this->dataDirectory().$article->getAlbum()->getDirectoryName().DIRECTORY_SEPARATOR."out";
    }

    public function articleFile(Article $article): string
    {
        return $this->dataDirectory().DIRECTORY_SEPARATOR.$article->getAlbum()->getDirectoryName(
            ).DIRECTORY_SEPARATOR.$article->getFileName();
    }

    public function ocrFile(Article $article): string
    {
        return $this->outputDirectory($article).'/'.Ocr::$ocrFilename;
    }

    public function fileExists(string $articleFile): bool
    {
        return is_readable($articleFile) && is_file($articleFile);
    }

}