<?php

namespace App\Controller\Backend;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
            'questions' => $questionRepository->allInactiveQuestions(),
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function show(Question $question): Response
    {
        return $this->render('backend/question/show.html.twig', [
            'question' => $question,
        ]);
    }

    /**
     * @Route("/{id}/status", name="status", methods={"POST"}, requirements={"id"="\d+"})
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
        return $this->redirectToRoute('question_list');
    }
}
