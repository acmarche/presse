<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Entity\Destinataire;
use AcMarche\Presse\Form\DestinataireType;
use AcMarche\Presse\Repository\DestinataireRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/destinataire")
 * @IsGranted("ROLE_PRESSE_ADMIN")
 */
class DestinataireController extends AbstractController
{
    /**
     * @var DestinataireRepository
     */
    private $destinataireRepository;

    public function __construct(DestinataireRepository $destinataireRepository)
    {
        $this->destinataireRepository = $destinataireRepository;
    }

    /**
     * @Route("/", name="presse_destinataire_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render(
            '@AcMarchePresse/destinataire/index.html.twig',
            [
                'destinataires' => $this->destinataireRepository->getAll(),
            ]
        );
    }

    /**
     * @Route("/new", name="presse_destinataire_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $destinataire = new Destinataire();
        $form = $this->createForm(DestinataireType::class, $destinataire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
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

    /**
     * @Route("/{id}", name="presse_destinataire_show", methods={"GET"})
     */
    public function show(Destinataire $destinataire): Response
    {
        return $this->render(
            '@AcMarchePresse/destinataire/show.html.twig',
            [
                'destinataire' => $destinataire,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="presse_destinataire_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Destinataire $destinataire): Response
    {
        $form = $this->createForm(DestinataireType::class, $destinataire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
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

    /**
     * @Route("/{id}", name="presse_destinataire_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Destinataire $destinataire): Response
    {
        if ($this->isCsrfTokenValid('delete'.$destinataire->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($destinataire);
            $entityManager->flush();
            $this->addFlash('success', 'Le destinataire a bien été supprimé');
        }

        return $this->redirectToRoute('presse_destinataire_index');
    }
}
