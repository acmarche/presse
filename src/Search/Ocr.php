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
     * @param string $tmpDirectory
     * @return void
     */
    public function createAndCleanTmpDirectory(string $tmpDirectory): void
    {
        if (!is_writable($tmpDirectory)) {
            $this->filesystem->mkdir($tmpDirectory);
        }

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

    public function convertToImages(string $file, string $tmpDirectory): void
    {
        shell_exec("pdftoppm -png \"$file\" $tmpDirectory");
    }

    public function convertToTxt(Article $article, ?string $filePath = null, ?string $tmpDirectory = null): void
    {
        $ocrFile = $this->ocrFile($article);
        //because command tesseract add .txt
        $ocrFile = str_replace('.txt', '', $ocrFile);
        if ($filePath) {
            shell_exec("tesseract \"$filePath\" $ocrFile --oem 1 --psm 3 -l fra logfile");
        } else {
            $files = scandir($tmpDirectory);
            $files = array_filter($files, function ($file) use ($tmpDirectory) {
                return (str_contains($file, '.png') || str_contains($file, '.jpg'));
            });
            $i = 1;
            foreach ($files as $item) {
                $filePath = Path::makeAbsolute($item, $tmpDirectory);
                shell_exec("tesseract $filePath $tmpDirectory/text-$i --oem 1 --psm 3 -l fra logfile");
                $i++;
            }
            shell_exec("cat $tmpDirectory/text-* > $ocrFile");
        }
    }

    public function dataDirectory(): string
    {
        return $this->projectDir.'/public/files/';
    }

    public function tmpDirectory(): string
    {
        return $this->dataDirectory()."ocr";
    }

    public function articleFile(Article $article): string
    {
        return $this->dataDirectory().$article->getAlbum()->getDirectoryName(
            ).DIRECTORY_SEPARATOR.$article->getFileName();
    }

    public function ocrDirectory(Article $article): string
    {
        return $this->dataDirectory().$article->getAlbum()->getDirectoryName().DIRECTORY_SEPARATOR.$article->getId();
    }

    public function ocrFile(Article $article): string
    {
        return $this->ocrDirectory($article).'-'.Ocr::$ocrFilename;
    }

    public function fileExists(string $articleFile): bool
    {
        return is_readable($articleFile) && is_file($articleFile);
    }

}