<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Entity\Message;
use AcMarche\Presse\Form\MessageType;
use AcMarche\Presse\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_PRESSE_ADMIN')]
#[Route(path: '/message')]
class MessageController extends AbstractController
{
    public function __construct(private readonly MessageRepository $messageRepository) {}

    #[Route(path: '/', name: 'presse_message_new', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (!$data->text || mb_strlen(trim($data->text)) < 5) {
                $this->addFlash('danger', 'Texte obligatoire');

                return $this->redirectToRoute('presse_message_new');
            }
            $message->sender = $this->getUser()->email;
            $message->sended = false;
            $message->text = $data->text;
            $message->subject = $data->subject;
            $this->messageRepository->persist($message);
            $this->messageRepository->flush();

            $this->addFlash('success', 'Le message a bien été enregistré et va être envoyé.');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('@AcMarchePresse/message/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
