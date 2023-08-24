<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Form\NotifierType;
use AcMarche\Presse\Repository\ArticleRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_PRESSE_ADMIN')]
class NotifierController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository
    ) {
    }

    #[Route(path: '/notifier', name: 'presse_notifier', methods: ['GET', 'POST'])]
    public function index(Request $request): RedirectResponse|Response
    {
        $date = new DateTime();
        $form = $this->createForm(NotifierType::class, [
            'date' => $date,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $articles = $this->articleRepository->getByDate($date);
            if (0 == \count($articles)) {
                $this->addFlash('warning', 'Aucun article trouvé à cette date');
            } else {
                $this->addFlash('success', 'Les articles ont été notifiés');
            }

            return $this->redirectToRoute('presse_notifier');
        }

        return $this->render('@AcMarchePresse/default/notifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
