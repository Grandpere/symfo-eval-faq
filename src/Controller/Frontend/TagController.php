<?php

namespace App\Controller\Frontend;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/tag", name="tag_")
 */
class TagController extends AbstractController
{
    /**
     * @Route("/", name="list")
     */
    public function list(TagRepository $tagRepository) : Response
    {
        return $this->render('frontend/tag/list.html.twig', [
            'tags' => $tagRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}/{slug}", name="show", methods={"GET"}, requirements={"id"="\d+", "slug"="[a-zA-Z0-9-]+"})
     */
    public function show(Tag $tag): Response
    {
        if(!$tag) {
            throw $this->createNotFoundException('Tag introuvable');
        }
        return $this->render('frontend/tag/show.html.twig', [
            'tag' => $tag,
        ]);
    }
}
