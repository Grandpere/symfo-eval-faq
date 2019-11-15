<?php

namespace App\Controller\Frontend;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/user", name="user_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="list")
     */
    public function list(UserRepository $userRepository) : Response
    {
        return $this->render('frontend/user/list.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}/{slug}", name="show", methods={"GET"}, requirements={"id"="\d+", "slug"="[a-zA-Z0-9-]+"})
     */
    public function show(User $user) : Response
    {
        if(!$user) {
            throw $this->createNotFoundException('User introuvable');
        }
        return $this->render('frontend/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/{slug}/edit", name="edit", methods={"GET","POST"}, requirements={"id"="\d+", "slug"="[a-zA-Z0-9-]+"})
     */
    public function edit(Request $request, User $user) : Response
    {   
        if(!$user) {
            throw $this->createNotFoundException('User introuvable');
        }
        
        if($this->getUser() == $user) {
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('user_edit', [
                    'id' => $user->getId(),
                    'slug' => $user->getSlug(),
                ]);
            }

            return $this->render('frontend/user/edit.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]);
        }
        return $this->redirectToRoute('user_show', ['id'=>$user->getId(), 'slug'=>$user->getSlug()]);
    }
}
