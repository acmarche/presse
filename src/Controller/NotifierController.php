<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Entity\Album;
use AcMarche\Presse\Form\NotifierType;
use AcMarche\Presse\Repository\AlbumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_PRESSE_ADMIN')]
class NotifierController extends AbstractController
{
    public function __construct(
        private readonly AlbumRepository $albumRepository,
    ) {}

    #[Route(path: '/notifier/{id}', name: 'presse_notifier', methods: ['GET', 'POST'])]
    public function index(Request $request, Album $album): RedirectResponse|Response
    {
        $form = $this->createForm(NotifierType::class, ['subject' =>'Revue de presse : '.$album->niceName()

        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $album->subject = $form->getData()['subject'];
            $album->sender = $this->getUser()->email;
            $album->sended = false;
            $album->text = $form->get('text')->getData();
            $this->albumRepository->flush();

            $this->addFlash('warning', 'Dû au traitements des pièces jointes, les mails partiront dans la demi-heure');
            $this->addFlash('success', 'La notification a bien été lancée');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('@AcMarchePresse/default/notifier.html.twig', [
            'form' => $form->createView(),
            'album' => $album,
        ]);
    }
}
