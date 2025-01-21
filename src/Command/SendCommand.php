<?php

namespace AcMarche\Presse\Command;

use AcMarche\Presse\Entity\Destinataire;
use AcMarche\Presse\Repository\AlbumRepository;
use AcMarche\Presse\Repository\DestinataireRepository;
use AcMarche\Presse\Repository\MessageRepository;
use AcMarche\Presse\Service\MailerPresse;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsCommand(
    name: 'presse:send',
    description: 'Send press review by mail',
)]
class SendCommand extends Command
{
    private bool $debug = true;
    private SymfonyStyle $io;

    public function __construct(
        private readonly MailerPresse $mailerPresse,
        private readonly AlbumRepository $albumRepository,
        private readonly DestinataireRepository $destinataireRepository,
        private readonly MessageRepository $messageRepository,
        private readonly MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->sendAlbums();
        $this->sendMessages();

        return Command::SUCCESS;
    }

    private function sendAlbums(): void
    {
        $albums = $this->albumRepository->findNotSended();
        if (count($albums) === 0) {
            return;
        }
        $album = $albums[0];
        if ($this->debug) {
            $this->io->writeln($album->getId());
        }
        try {
            $messageBase = $this->mailerPresse->generateMessageForAlbum($album, false);
            $messageWithAttachments = $this->mailerPresse->generateMessageForAlbum($album, true);
        } catch (\Exception $exception) {
            $this->io->error($exception->getMessage());

            return;
        }
        foreach ($this->destinataireRepository->findAllWantNotification() as $recipient) {
            $message = $messageBase;
            if ($recipient->attachment) {
                $message = $messageWithAttachments;
            }
            $this->sendEmail($message, $recipient);
        }

        $album->sended = true;
        $this->albumRepository->flush();
    }

    private function sendMessages(): void
    {
        $messages = $this->messageRepository->findNotSended();
        if (count($messages) === 0) {
            return;
        }
        $message = $messages[0];
        if ($this->debug) {
            $this->io->writeln($message->getId());
        }
        try {
            $messageBase = $this->mailerPresse->generateMessage($message);
        } catch (\Exception $exception) {
            $this->io->error($exception->getMessage());

            return;
        }
        foreach ($this->destinataireRepository->findAllWantNotification() as $recipient) {
            $this->sendEmail($messageBase, $recipient);
        }

        $message->sended = true;
        $this->messageRepository->flush();
    }

    private function sendEmail(TemplatedEmail $templatedEmail, Destinataire $recipient): void
    {
        if ($this->debug) {
            $this->io->writeln($recipient->email);
            $templatedEmail->to(new Address('jf@marche.be', $recipient->email));
        } else {
            $templatedEmail->to($recipient->email);
        }
        try {
            $this->mailer->send($templatedEmail);
        } catch (TransportExceptionInterface $e) {
            $this->io->error($e->getMessage());
        }
    }

}