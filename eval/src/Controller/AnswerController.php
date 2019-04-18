<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Form\AnswerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route(name="answer_")
 */
class AnswerController extends AbstractController
{
    /**
     * @Route("/question/{id}/{slug}/answer/new", name="new", methods={"GET","POST"}, requirements={"id"="\d+","slug"="[a-zA-Z0-9-]+"})
     */
    public function new(Request $request, Question $question): Response
    {
        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $author = $this->getUser();
            $answer->setAuthor($author);
            $answer->setQuestion($question);
            $entityManager->persist($answer);
            $entityManager->flush();

            return $this->redirectToRoute('question_show', ['id'=> $question->getId(), 'slug'=> $question->getSlug()]);
        }

        return $this->render('answer/new.html.twig', [
            'question' => $question,
            'answer' => $answer,
            'form' => $form->createView(),
        ]);
    }
}
