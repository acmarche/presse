<?php

namespace AcMarche\Presse\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use AcMarche\Presse\Entity\User;
use AcMarche\Presse\Form\UserEditType;
use AcMarche\Presse\Form\UserType;
use AcMarche\Presse\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 * @IsGranted("ROLE_PRESSE_ADMIN")
 */
class UserController extends AbstractController
{
    private UserPasswordEncoderInterface $userPasswordEncoder;
    private UserRepository $userRepository;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder, UserRepository $userRepository)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/", name="presse_user_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render(
            '@AcMarchePresse/user/index.html.twig',
            [
                'users' => $this->userRepository->findAll(),
            ]
        );
    }

    /**
     * @Route("/new", name="presse_user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $user->setPassword(
                $this->userPasswordEncoder->encodePassword($user, $user->getPassword())
            );
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Le user a bien été ajouté');

            return $this->redirectToRoute('presse_user_index');
        }

        return $this->render(
            '@AcMarchePresse/user/new.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="presse_user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render(
            '@AcMarchePresse/user/show.html.twig',
            [
                'user' => $user,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="presse_user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le user a bien été modifié');

            return $this->redirectToRoute('presse_user_index');
        }

        return $this->render(
            '@AcMarchePresse/user/edit.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="presse_user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Le user a bien été supprimé');
        }

        return $this->redirectToRoute('presse_user_index');
    }
}
