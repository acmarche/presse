<?php

namespace AcMarche\Presse\Service;

use AcMarche\Presse\Entity\Album;
use AcMarche\Presse\Entity\Destinataire;
use AcMarche\Presse\Repository\ArticleRepository;
use AcMarche\Presse\Search\Ocr;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class MailerPresse
{
    protected bool $debug = false;

    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly Ocr $ocr,
    ) {}

    public function generateMessage(Album $album, bool $attachment): TemplatedEmail
    {
        $message = (new TemplatedEmail())
            ->subject('Revue de presse : '.$album->niceName())
            ->from(new Address($album->sender))
            ->htmlTemplate('@AcMarchePresse/mail/mail.html.twig')
            ->textTemplate('@AcMarchePresse/mail/mail.txt.twig')
            ->context(
                [
                    'album' => $album,
                    'attachment' => $attachment,
                ],
            );

        if ($attachment) {
            $this->attachFiles($album, $message);
        }

        return $message;
    }

    private function attachFiles(Album $album, TemplatedEmail $message): void
    {
        $articles = $this->articleRepository->findByAlbum($album);
        foreach ($articles as $article) {
            $path = $this->ocr->articleFile($article);
            if (is_readable($path)) {
                $message->attachFromPath($path);
            }
        }
    }
}
