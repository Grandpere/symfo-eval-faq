<?php

namespace App\EventListener;

use App\Entity\Question;
use App\Service\Slugger;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class SluggerListener
{
    private $slugger;

    public function __construct(Slugger $slugger)
    {
        $this->slugger = $slugger;
    }
    
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->slugify($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->slugify($args);
    }

    public function slugify(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Question) {
            return;
        }

        $slug = $this->slugger->slugify($entity->getTitle());
        $entity->setSlug($slug);
    }
}