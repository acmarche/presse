<?php

namespace AcMarche\Presse\Search;

use AcMarche\Presse\Entity\Article;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class Ocr
{
    public Filesystem $filesystem;
    public static $ocrFilename = 'ocr.txt';

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
        $this->filesystem = new Filesystem();
    }

    /**
     * @param string $tmpDirectory
     * @return void
     */
    public function cleanTmpDirectory(string $tmpDirectory): void
    {
        $files = scandir($tmpDirectory);
        // Filter out the '.' and '..' entries
        $files = array_diff($files, ['.', '..']);
        foreach ($files as $file) {
            $filePath = Path::makeAbsolute($file, $tmpDirectory);
            if (is_file($filePath)) {
                $this->filesystem->remove($filePath);
            }
        }
    }

    public function isCleanTmpDirectory(string $tmpDirectory): bool
    {
        $files = scandir($tmpDirectory);
        // Filter out the '.' and '..' entries
        $files = array_diff($files, ['.', '..']);

        return count($files) > 0;
    }

    public function convertToImages(string $file, string $tmpDirectory): void
    {
        $tmpDirectory .= DIRECTORY_SEPARATOR."img-ocr";
        shell_exec("pdftoppm -png \"$file\" $tmpDirectory");
    }

    public function convertToTxt(Article $article, string $mime, string $tmpDirectory): void
    {
        $ocrFile = $this->ocrFile($article);
        if (str_contains($mime, 'image')) {
            //because command tesseract add .txt
            $ocrFile = str_replace('.txt', '', $ocrFile);
            $filePath = $this->articleFile($article);
            shell_exec("tesseract \"$filePath\" $ocrFile --oem 1 --psm 3 -l fra logfile");

            return;
        }

        $files = scandir($tmpDirectory);
        $files = array_filter($files, function ($file) use ($tmpDirectory) {
            return (str_contains($file, 'img-ocr'));
        });

        $i = 1;
        foreach ($files as $item) {
            $filePath = Path::makeAbsolute($item, $tmpDirectory);
            shell_exec("tesseract $filePath $tmpDirectory/text-$i --oem 1 --psm 3 -l fra logfile");
            $i++;
        }
        shell_exec("cat $tmpDirectory/text-* > $ocrFile");
    }

    public function dataDirectory(): string
    {
        return $this->projectDir.'/public/files/';
    }

    public function tmpDirectory(): string
    {
        return $this->dataDirectory()."out";
    }

    public function articleFile(Article $article): string
    {
        return $this->dataDirectory().$article->getAlbum()->getDirectoryName(
            ).DIRECTORY_SEPARATOR.$article->getFileName();
    }

    public function ocrDirectory(Article $article): string
    {
        return $this->dataDirectory().$article->getId().DIRECTORY_SEPARATOR;
    }

    public function ocrFile(Article $article): string
    {
        return $this->ocrDirectory($article).$article->getId().'-'.Ocr::$ocrFilename;
    }

    public function fileExists(string $courierFile): bool
    {
        return is_readable($courierFile) && is_file($courierFile);
    }
}