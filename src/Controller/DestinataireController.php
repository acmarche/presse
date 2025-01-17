<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Entity\Destinataire;
use AcMarche\Presse\Form\DestinataireType;
use AcMarche\Presse\Form\SearchDestinataireType;
use AcMarche\Presse\Repository\DestinataireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/destinataire')]
#[IsGranted('ROLE_PRESSE_ADMIN')]
class DestinataireController extends AbstractController
{
    public function __construct(
        private DestinataireRepository $destinataireRepository,
    ) {}

    #[Route(path: '/', name: 'presse_destinataire_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(SearchDestinataireType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $destinataires = $this->destinataireRepository->search(
                $data['name'],
                $data['attachment'],
                $data['notification'],
                $data['externe'],
            );
        } else {
            $destinataires = $this->destinataireRepository->getAll();
        }
        $response = new Response(null, $form->isSubmitted() ? Response::HTTP_ACCEPTED : Response::HTTP_OK);

        return $this->render(
            '@AcMarchePresse/destinataire/index.html.twig',
            [
                'destinataires' => $destinataires,
                'form' => $form->createView(),
                'search' => $form->isSubmitted(),
            ],
            $response,
        );
    }

    #[Route(path: '/new', name: 'presse_destinataire_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $destinataire = new Destinataire();
        $form = $this->createForm(DestinataireType::class, $destinataire);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->destinataireRepository->persist($destinataire);
            $this->destinataireRepository->flush();
            $this->addFlash('success', 'Le destinataire a bien été ajouté');

            return $this->redirectToRoute('presse_destinataire_index');
        }

        return $this->render(
            '@AcMarchePresse/destinataire/new.html.twig',
            [
                'destinataire' => $destinataire,
                'form' => $form->createView(),
            ],
        );
    }

    #[Route(path: '/{id}', name: 'presse_destinataire_show', methods: ['GET'])]
    public function show(Destinataire $destinataire): Response
    {
        return $this->render(
            '@AcMarchePresse/destinataire/show.html.twig',
            [
                'destinataire' => $destinataire,
            ],
        );
    }

    #[Route(path: '/{id}/edit', name: 'presse_destinataire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Destinataire $destinataire): Response
    {
        $form = $this->createForm(DestinataireType::class, $destinataire);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->destinataireRepository->flush();

            if ($destinataire->username) {
                $this->addFlash(
                    'info',
                    'Ce destinataire provient du système informatique, les champs email, nom et prénom ne sont pas modifiables',
                );
            }
            $this->addFlash('success', 'Le destinataire a bien été modifié');

            return $this->redirectToRoute('presse_destinataire_show', [
                'id' => $destinataire->getId(),
            ]);
        }

        return $this->render(
            '@AcMarchePresse/destinataire/edit.html.twig',
            [
                'destinataire' => $destinataire,
                'form' => $form->createView(),
            ],
        );
    }

    #[Route(path: '/{id}', name: 'presse_destinataire_delete', methods: ['POST'])]
    public function delete(Request $request, Destinataire $destinataire): RedirectResponse
    {
        if ($destinataire->username) {
            $this->addFlash('danger', 'Le destinataire ne peut être supprimé car il provient du système informatique');

            return $this->redirectToRoute('presse_destinataire_index');
        }

        if ($this->isCsrfTokenValid('delete'.$destinataire->getId(), $request->request->get('_token'))) {
            $this->destinataireRepository->remove($destinataire);
            $this->destinataireRepository->flush();
            $this->addFlash('success', 'Le destinataire a bien été supprimé');
        } else {
            $this->addFlash('danger', 'Le destinataire n\'a pas été supprimé');
        }

        return $this->redirectToRoute('presse_destinataire_index');
    }
}
