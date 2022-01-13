<?php

namespace AcMarche\Presse\Controller;

use AcMarche\Presse\Entity\User;
use AcMarche\Presse\Form\UserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/password')]
#[IsGranted(data: 'ROLE_PRESSE_ADMIN')]
class PasswordController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordEncoder
    ) {
    }

    #[Route(path: '/{id}', name: 'presse_user_password')]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserPasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $password = $data->getPassword();
            $user->setPassword($this->userPasswordEncoder->hashPassword($user, $password));
            $this->entityManager->flush();

            return $this->redirectToRoute(
                'presse_user_show',
                [
                    'id' => $user->getId(),
                ]
            );
        }

        return $this->render(
            '@AcMarchePresse/user/edit_password.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }
}
