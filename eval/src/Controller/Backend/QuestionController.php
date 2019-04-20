<?php

namespace App\Controller\Backend;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend/question", name="backend_question_")
 */
class QuestionController extends AbstractController
{
    /**
     * @Route("/", name="list", methods={"GET"})
     */
    public function list(QuestionRepository $questionRepository): Response
    {
        return $this->render('backend/question/list.html.twig', [
            'questions' => $questionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($question);
            $entityManager->flush();

            return $this->redirectToRoute('backend_question_list');
        }

        return $this->render('backend/question/new.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show(Question $question): Response
    {
        return $this->render('backend/question/show.html.twig', [
            'question' => $question,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Question $question): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('backend_question_list', [
                'id' => $question->getId(),
            ]);
        }

        return $this->render('backend/question/edit.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/status", name="status", methods={"POST"})
     */
    public function status(Request $request, Question $question) : Response
    {
        if (!$question) {
            throw $this->createNotFoundException('Question introuvable');
        }
        if ($this->isCsrfTokenValid('question-status'.$question->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $question->setStatus(!$question->getStatus());
            $entityManager->flush();
            $this->addFlash(
            'success',
            'Enregistrement effectué'
            );
        }
        return $this->redirectToRoute('question_show', ['id'=> $question->getId() , 'slug'=> $question->getSlug()]);
    }
}
