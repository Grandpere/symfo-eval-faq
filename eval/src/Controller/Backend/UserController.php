<?php

namespace App\Controller\Backend;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}/promote", name="promote")
     */
    public function promote(User $user)
    {

    }
}
