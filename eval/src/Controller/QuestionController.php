<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
            'questions' => $questionRepository->lastQuestions(),
        ]);
    }

    /**
     * @Route("/questions", name="questions", methods={"GET"})
     */
    public function questions(QuestionRepository $questionRepository) : Response
    {
        return $this->render('question/questions.html.twig', [
            'questions'=> $questionRepository->findAll(),
        ]);
    }

    /**
     * @Route("question/{id}/{slug}", name="show", methods={"GET"}, requirements={"id"="\d+","slug"="[a-zA-Z0-9-]+"})
     */
    public function show(Question $question): Response
    {
        if(!$question) {
            throw $this->createNotFoundException('Question introuvable');
        }
        return $this->render('question/show.html.twig', [
            'question' => $question,
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

            return $this->redirectToRoute('question_show', ['id'=> $question->getId(), 'slug'=> $question->getSlug()]);
        }

        return $this->render('question/new.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }
}
