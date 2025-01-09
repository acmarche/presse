<?php

namespace AcMarche\Presse\Command;

use AcMarche\Presse\Repository\AlbumRepository;
use AcMarche\Presse\Repository\DestinataireRepository;
use AcMarche\Presse\Service\MailerPresse;
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
    description: 'Send articles by mail',
)]
class SendCommand extends Command
{
    private bool $debug = true;

    public function __construct(
        private readonly MailerPresse $mailerPresse,
        private readonly AlbumRepository $albumRepository,
        private readonly DestinataireRepository $destinataireRepository,
        private readonly MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->albumRepository->findNotSended() as $album) {
            $messageBase = $this->mailerPresse->generateMessage($album, false);
            $messageWithAttachments = $this->mailerPresse->generateMessage($album, true);

            foreach ($this->destinataireRepository->findAllWantNotification() as $recipient) {
                $message = $messageBase;
                if ($recipient->attachment) {
                    $message = $messageWithAttachments;
                }
                if ($this->debug) {
                    $message->to(new Address('jf@marche.be', $recipient->email));
                } else {
                    $message->to($recipient->email);
                }
                try {
                    $this->mailer->send($message);
                } catch (TransportExceptionInterface $e) {
                    $io->error($e->getMessage());
                }
                $message = null;
            }

            $album->sended = true;
            $this->albumRepository->flush();
        }

        return Command::SUCCESS;
    }

}