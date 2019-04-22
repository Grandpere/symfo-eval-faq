<?php

namespace App\Controller\Backend;

use App\Entity\Answer;
use App\Repository\AnswerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/backend/answer", name="backend_answer_")
 */
class AnswerController extends AbstractController
{
    /**
     * @Route("/", name="list", methods={"GET"})
     */
    public function list(AnswerRepository $answerRepository): Response
    {
        return $this->render('backend/answer/list.html.twig', [
            'answers' => $answerRepository->allInactiveAnswers(),
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function show(Answer $answer): Response
    {
        return $this->render('backend/answer/show.html.twig', [
            'answer' => $answer,
        ]);
    }

    /**
     * @Route("/{id}/status", name="status", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function status(Request $request, Answer $answer) : Response
    {
        if (!$answer) {
            throw $this->createNotFoundException('Réponse introuvable');
        }
        if ($this->isCsrfTokenValid('answer-status'.$answer->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $answer->setStatus(!$answer->getStatus());
            $entityManager->flush();
            $this->addFlash(
            'success',
            'Enregistrement effectué'
            );
        }
        return $this->redirectToRoute('question_show', ['id'=> $answer->getQuestion()->getId() , 'slug'=> $answer->getQuestion()->getSlug()]);
    }
}
