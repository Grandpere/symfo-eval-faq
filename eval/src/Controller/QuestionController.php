<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Form\AnswerType;
use App\Form\QuestionType;
use App\Form\RightAnswerType;
use App\Repository\QuestionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\AnswerRepository;

/**
     * @Route(name="question_")
     */
class QuestionController extends AbstractController
{
    /**
     * @Route("/", name="list", methods={"GET"})
     */
    public function list(QuestionRepository $questionRepository) : Response
    {
        return $this->render('question/list.html.twig', [
            'questions' => $questionRepository->sevenlastActiveQuestions(),
        ]);
    }

    /**
     * @Route("/questions", name="questions", methods={"GET"})
     */
    public function questions(QuestionRepository $questionRepository) : Response
    {
        return $this->render('question/questions.html.twig', [
            'questions'=> $questionRepository->allActiveQuestions(),
        ]);
    }

    /**
     * @Route("question/{id}/{slug}", name="show", methods={"GET", "POST"}, requirements={"id"="\d+","slug"="[a-zA-Z0-9-]+"})
     */
    public function show(Request $request, Question $question): Response
    {
        if(!$question) {
            throw $this->createNotFoundException('Question introuvable');
        }

        // Affichage et submit des réponses
        $answer = new Answer();
        $formAnswer = $this->createForm(AnswerType::class, $answer);
        $formAnswer->handleRequest($request);

        if ($formAnswer->isSubmitted() && $formAnswer->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $author = $this->getUser();
            $answer->setAuthor($author);
            $answer->setQuestion($question);
            $entityManager->persist($answer);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Enregistrement effectué'
            );

            return $this->redirectToRoute('question_show', ['id'=> $question->getId(), 'slug'=> $question->getSlug()]);
        }

        // // Choix de la bonne réponse parmi les réponses existantes
        // $formRightAnswer = $this->createForm(RightAnswerType::class, $question, ['answers' => $question->getAnswers()]);
        // $formRightAnswer->handleRequest($request);

        // if ($formRightAnswer->isSubmitted() && $formRightAnswer->isValid()) {
        //     $entityManager = $this->getDoctrine()->getManager();
        //     $entityManager->flush();

        //     $this->addFlash(
        //         'success',
        //         'Enregistrement effectué'
        //     );
            
        //     return $this->redirectToRoute('question_show', ['id'=> $question->getId(), 'slug'=> $question->getSlug()]);
        // }

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answer' => $answer,
            'formAnswer' => $formAnswer->createView(),
            // 'formRightAnswer' => $formRightAnswer->createView(),
        ]);
    }

    /**
     * @Route("question/new", name="new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $author = $this->getUser();
            $question->setAuthor($author);
            $entityManager->persist($question);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Enregistrement effectué'
            );

            return $this->redirectToRoute('question_show', ['id'=> $question->getId(), 'slug'=> $question->getSlug()]);
        }

        return $this->render('question/new.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/question/{id}/answer/{answerId}/right", name="right", methods={"POST"}, requirements={"id"="\d+", "answerId"="\d+"})
     */
    public function right(Request $request, Question $question, $answerId, AnswerRepository $answerRepository) : Response
    {
        if(!$question) {
            throw $this->createNotFoundException('Question introuvable');
        }

        if ($this->getUser() == $question->getAuthor()) {
            $answer = $answerRepository->findOneBy(['id' => $answerId]);
            if (!$answer) {
                throw $this->createNotFoundException('Réponse introuvable');
            }
            if ($this->isCsrfTokenValid('right'.$question->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $question->setRightAnswer($answer);
                $entityManager->flush();
                $this->addFlash(
                'success',
                'Enregistrement effectué'
            );
            }
        }
        return $this->redirectToRoute('question_show', ['id'=> $question->getId(), 'slug'=> $question->getSlug()]);
    }
}
