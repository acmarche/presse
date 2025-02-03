<?php

namespace AcMarche\Presse\Service;

use AcMarche\Presse\Entity\Album;
use AcMarche\Presse\Entity\Message;
use AcMarche\Presse\Repository\ArticleRepository;
use AcMarche\Presse\Search\Ocr;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Address;
use Symfony\Component\String\Slugger\SluggerInterface;

class MailerPresse
{
    protected bool $debug = false;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
        private readonly ArticleRepository $articleRepository,
        private readonly Ocr $ocr,
        private readonly SluggerInterface $slugger,
    ) {}

    public function generateMessageForAlbum(Album $album, bool $attachment): TemplatedEmail
    {
        $message = (new TemplatedEmail())
            ->subject($album->subject)
            ->from(new Address($album->sender))
            ->htmlTemplate('@AcMarchePresse/mail/album.html.twig')
            ->textTemplate('@AcMarchePresse/mail/album.txt.twig')
            ->context(
                [
                    'album' => $album,
                    'attachment' => $attachment,
                ],
            );

        if ($attachment) {
            $this->attachFilesForAlbum($album, $message);
        }

        return $message;
    }

    private function attachFilesForAlbum(Album $album, TemplatedEmail $message): void
    {
        $articles = $this->articleRepository->findByAlbum($album);
        foreach ($articles as $article) {
            $path = $this->ocr->articleFile($article);
            if (is_readable($path)) {
                $name = $this->slugger->slug($article->nom).'-'.$article->fileName;
                $message->attachFromPath($path,$name);
            }
        }
    }

    public function generateMessage(Message $message): TemplatedEmail
    {
        $email = (new TemplatedEmail())
            ->subject($message->subject)
            ->from(new Address($message->sender))
            ->htmlTemplate('@AcMarchePresse/mail/message.html.twig')
            ->textTemplate('@AcMarchePresse/mail/message.txt.twig')
            ->context(
                [
                    'message' => $message,
                ],
            );

        $this->attachFiles($message, $email);

        return $email;
    }

    private function attachFiles(Message $message, TemplatedEmail $email): void
    {
        if ($message->fileName) {
            $path = $this->messageFile($message);
            if (is_readable($path)) {
                $email->attachFromPath($path);
            }
        }
    }

    public function dataDirectory(): string
    {
        return $this->projectDir.'/public/files/messages';
    }

    public function messageFile(Message $message): string
    {
        return $this->dataDirectory().DIRECTORY_SEPARATOR.$message->fileName;
    }
}
