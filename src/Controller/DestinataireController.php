<?php

namespace AcMarche\Presse\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AcMarche\Presse\Entity\Destinataire;
use AcMarche\Presse\Form\DestinataireType;
use AcMarche\Presse\Repository\DestinataireRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/destinataire')]
#[IsGranted(data: 'ROLE_PRESSE_ADMIN')]
class DestinataireController extends AbstractController
{
    public function __construct(private DestinataireRepository $destinataireRepository, private ManagerRegistry $managerRegistry)
    {
    }
    #[Route(path: '/', name: 'presse_destinataire_index', methods: ['GET'])]
    public function index() : Response
    {
        return $this->render(
            '@AcMarchePresse/destinataire/index.html.twig',
            [
                'destinataires' => $this->destinataireRepository->getAll(),
            ]
        );
    }
    #[Route(path: '/new', name: 'presse_destinataire_new', methods: ['GET', 'POST'])]
    public function new(Request $request) : Response
    {
        $destinataire = new Destinataire();
        $form = $this->createForm(DestinataireType::class, $destinataire);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($destinataire);
            $entityManager->flush();
            $this->addFlash('success', 'Le destinataire a bien été ajouté');
            return $this->redirectToRoute('presse_destinataire_index');
        }
        return $this->render(
            '@AcMarchePresse/destinataire/new.html.twig',
            [
                'destinataire' => $destinataire,
                'form' => $form->createView(),
            ]
        );
    }
    #[Route(path: '/{id}', name: 'presse_destinataire_show', methods: ['GET'])]
    public function show(Destinataire $destinataire) : Response
    {
        return $this->render(
            '@AcMarchePresse/destinataire/show.html.twig',
            [
                'destinataire' => $destinataire,
            ]
        );
    }
    #[Route(path: '/{id}/edit', name: 'presse_destinataire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Destinataire $destinataire) : Response
    {
        $form = $this->createForm(DestinataireType::class, $destinataire);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->managerRegistry->getManager()->flush();
            $this->addFlash('success', 'Le destinataire a bien été modifié');

            return $this->redirectToRoute('presse_destinataire_show', ['id' => $destinataire->getId()]);
        }
        return $this->render(
            '@AcMarchePresse/destinataire/edit.html.twig',
            [
                'destinataire' => $destinataire,
                'form' => $form->createView(),
            ]
        );
    }
    #[Route(path: '/{id}', name: 'presse_destinataire_delete', methods: ['DELETE'])]
    public function delete(Request $request, Destinataire $destinataire) : RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$destinataire->getId(), $request->request->get('_token'))) {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->remove($destinataire);
            $entityManager->flush();
            $this->addFlash('success', 'Le destinataire a bien été supprimé');
        }
        return $this->redirectToRoute('presse_destinataire_index');
    }
}
