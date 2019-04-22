<?php

namespace App\Controller\Backend;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\RoleRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/backend/user", name="backend_user_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="list")
     */
    public function list(UserRepository $userRepository)
    {
        return $this->render('backend/user/list.html.twig', [
            'users' => $userRepository->allUsersWithRoleUser(),
        ]);
    }

    /**
     * @Route("/{id}/promote", name="promote")
     */
    public function promote(Request $request, User $user, RoleRepository $roleRepository)
    {
        if (!$user) {
            throw $this->createNotFoundException('User introuvable');
        }
        if ($this->isCsrfTokenValid('user-promote'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $role = $roleRepository->findOneByName('ROLE_MODERATEUR');
            $user->setRole($role);
            $entityManager->flush();
            $this->addFlash(
            'success',
            'Enregistrement effectué'
            );
        }
        return $this->redirectToRoute('backend_user_list');
    }
}
