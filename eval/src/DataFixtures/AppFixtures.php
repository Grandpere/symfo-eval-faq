<?php

namespace App\DataFixtures;

// use Nelmio\Alice\Loader\NativeLoader;
use Faker\Factory;
use App\Entity\Role;
use App\Service\Slugger;
use Faker\ORM\Doctrine\Populator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;
    private $slugger;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, Slugger $slugger)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->slugger = $slugger;
    }
    public function load(ObjectManager $manager)
    {
        // $loader = new NativeLoader();
        // $entities = $loader->loadFile(__DIR__.'/fixtures.yml')->getObjects();

        // foreach ($entities as $entity) {
        //     if (is_a($entity, 'App\Entity\Tag')) {
        //         $slug = $this->slugger->slugify($entity->getName());
        //         $entity->setSlug($slug);
        //     }
        //     if (is_a($entity, 'App\Entity\Question')) {
        //         $slug = $this->slugger->slugify($entity->getTitle());
        //         $entity->setSlug($slug);
        //     }
        //     $manager->persist($entity);
        // };

        // $manager->flush();

        $generator = Factory::create('fr_FR');
        $generator->seed('faqoclock');

        $populator = new Populator($generator, $manager);

        // ROLE
        $roleUser = new Role();
        $roleUser->setName('ROLE_USER');
        $roleUser->setLabel('Utilisateur');
        $manager->persist($roleUser);

        $roleAdmin = new Role();
        $roleAdmin->setName('ROLE_ADMIN');
        $roleAdmin->setLabel('Administrateur');
        $manager->persist($roleAdmin);

        $roleModo = new Role();
        $roleModo->setName('ROLE_MODERATEUR');
        $roleModo->setLabel('Modérateur');
        $manager->persist($roleModo);

        // USER
        $populator->addEntity('App\Entity\User', 50, array(
            'email' => function() use ($generator) { return $generator->unique()->email(); },
            'description' => function() use ($generator) { return $generator->sentence(); },
            'username' => function() use ($generator) { return $generator->unique()->username(); },
            'avatar' => function() use ($generator) { return $generator->imageUrl($width = 640, $height = 480); },
            'role' => $roleUser,
            'firstname' => function() use ($generator) { return $generator->firstName(); },
            'lastname' => function() use ($generator) { return $generator->lastName(); },
        ), array(
            function($user) {
                $encodedPassword = $this->passwordEncoder->encodePassword($user, 'password');
                $user->setPassword($encodedPassword);
                $slug = $this->slugger->slugify($user->getUsername());
                $user->setSlug($slug);
            }
        ));

        // TAG
        $populator->addEntity('App\Entity\Tag', 20, array(
            'name' => function() use ($generator) { return $generator->unique()->jobTitle(); }
        ), array(
            function($tag) {
                $slug = $this->slugger->slugify($tag->getName());
                $tag->setSlug($slug);
            }
        ));

        // QUESTION
        $populator->addEntity('App\Entity\Question', 50, array(
            'title' => function() use ($generator) { return $generator->sentence(); },
            'content' => function() use ($generator) { return $generator->sentence(); },
            'status' => 1,
            'vote' => 0,
        ), array(
            function($question) {
                $slug = $this->slugger->slugify($question->getTitle());
                $question->setSlug($slug);
            }
        ));

        // ANSWER
        $populator->addEntity('App\Entity\Answer', 150, array(
            'content' => function() use ($generator) { return $generator->sentence(); },
            'status' => 1,
            'vote' => 0,
        ));

        $inserted = $populator->execute();

        // relation m2m QUESTION_TAG
        $tags = $inserted['App\Entity\Tag'];
        $questions =  $inserted['App\Entity\Question'];

        foreach($questions as $question) {
            shuffle($tags);

            $question->addTag($tags[0]);
            $question->addTag($tags[1]);
            $question->addTag($tags[2]);
            $question->addTag($tags[3]);
        }
        $manager->flush();
    }
}
