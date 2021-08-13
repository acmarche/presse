<?php

namespace AcMarche\Presse\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use DateTime;
use AcMarche\Presse\Form\NotifierType;
use AcMarche\Presse\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Default controller.
 *
 * @IsGranted("ROLE_PRESSE_ADMIN")
 */
class NotifierController extends AbstractController
{

    private ArticleRepository $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * @Route("/notifier", name="presse_notifier", methods={"GET","POST"})
     *
     */
    public function index(Request $request): RedirectResponse
    {
        $date = new DateTime();
        $form = $this->createForm(NotifierType::class, ['date'=>$date]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articles = $this->articleRepository->getByDate($date);
            if (count($articles) == 0) {
                $this->addFlash('warning', 'Aucun article trouvé à cette date');
            } else {
                $this->addFlash('success', 'Les articles ont été notifiés');
            }

            return $this->redirectToRoute('presse_notifier');
        }

        return $this->render('@AcMarchePresse/default/notifier.html.twig', ['form' => $form->createView()]);
    }

}
