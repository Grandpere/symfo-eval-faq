<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Form\AnswerType;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\AnswerRepository;
use App\Form\QuestionVoteType;

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
        // dd($questionRepository->sevenlastActiveQuestions());
        return $this->render('question/list.html.twig', [
            'questions' => $questionRepository->sevenlastActiveQuestions(),
        ]);
    }

    /**
     * @Route("/questions", name="questions", methods={"GET"})
     */
    public function questions(QuestionRepository $questionRepository) : Response
    {
        // dd($questionRepository->allActiveQuestions());
        return $this->render('question/questions.html.twig', [
            'questions'=> $questionRepository->allActiveQuestions(),
        ]);
    }

    /**
     * @Route("question/{id}/{slug}", name="show", methods={"GET", "POST"}, requirements={"id"="\d+","slug"="[a-zA-Z0-9-]+"})
     */
    public function show(Request $request, Question $question, AnswerRepository $answerRepository): Response
    {
        if(!$question) {
            throw $this->createNotFoundException('Question introuvable');
        }

        // Affichage et submit des réponses
        $answer = new Answer();
        $formAnswer = $this->createForm(AnswerType::class, $answer);
        $formAnswer->handleRequest($request);

        $questionVote = $this->createForm(QuestionVoteType::class, $question);
        $questionVote->handleRequest($request);

        if ($questionVote->isSubmitted() && $questionVote->isValid()) {
            if ($this->getUser() == null) {
                $this->addFlash(
                    'danger',
                    'Vous devez vous enregistrer pour voter'
                );
                return $this->render('question/show.html.twig', [
                    'question' => $question,
                    'answers' => $answerRepository->allActiveAnswersByQuestion($question),
                    'formAnswer' => $questionVote->createView(),
                    'questionVote' => $questionVote->createView(),
                ]);
            }
            $entityManager = $this->getDoctrine()->getManager();
            if($questionVote->get('up')->isClicked()) {
                $question->setVote($question->getVote()+1);
            }
            if($questionVote->get('down')->isClicked()) {
                $question->setVote($question->getVote()-1);
            }
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Vote soumis'
            );
            return $this->redirectToRoute('question_show', ['id'=> $question->getId(), 'slug'=> $question->getSlug()]);
        }

        if ($formAnswer->isSubmitted() && $formAnswer->isValid()) {
            if ($this->getUser() == null) {
                $this->addFlash(
                    'danger',
                    'Vous devez vous enregistrer pour répondre'
                );
                return $this->render('question/show.html.twig', [
                    'question' => $question,
                    'answers' => $answerRepository->allActiveAnswersByQuestion($question),
                    'formAnswer' => $formAnswer->createView(),
                    'questionVote' => $questionVote->createView(),
                ]);
            }
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

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answers' => $answerRepository->allActiveAnswersByQuestion($question),
            'formAnswer' => $formAnswer->createView(),
            'questionVote' => $questionVote->createView(),
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

    /**
     * @Route("/question/{id}/answer/{answerId}/vote", name="answer_vote", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function vote(Request $request, Question $question, AnswerRepository $answerRepository, $answerId) : Response
    {
        if (!$question) {
            throw $this->createNotFoundException('Question introuvable');
        }

        $answer = $answerRepository->findOneBy(['id' => $answerId]);
        if (!$answer) {
            throw $this->createNotFoundException('Réponse introuvable');
        }

        if ($this->isCsrfTokenValid('answer-vote'.$answer->getId(), $request->request->get('_token'))) {
            if ($this->getUser() == null) {
                $this->addFlash(
                    'danger',
                    'Vous devez vous enregistrer pour voter'
                );
                $formAnswer = $this->createForm(AnswerType::class, $answer);
                $formAnswer->handleRequest($request);

                $questionVote = $this->createForm(QuestionVoteType::class, $question);
                $questionVote->handleRequest($request);

                return $this->render('question/show.html.twig', [
                    'question' => $question,
                    'answers' => $answerRepository->allActiveAnswersByQuestion($question),
                    'formAnswer' => $formAnswer->createView(),
                    'questionVote' => $questionVote->createView(),
                ]);
            }
            $up = $request->request->get('up');
            $down = $request->request->get('down');
            // dd($up, $down);
            $entityManager = $this->getDoctrine()->getManager();
            if($up == true) {
                $answer->setVote($answer->getVote()+1);
            }
            if($down == true) {
                $answer->setVote($answer->getVote()-1);
            }
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Vote soumis'
            );
        }
        return $this->redirectToRoute('question_show', ['id'=> $question->getId(), 'slug'=> $question->getSlug()]);
    }
}
