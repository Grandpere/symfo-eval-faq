<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function sevenlastActiveQuestions()
    {
        $query = $this->createQueryBuilder('q')
                        ->andWhere('q.status = 1')
                        ->orderBy('q.id', 'DESC')
                        ->setMaxResults(7);
        return $query->getQuery()->getResult();
    }

    public function allActiveQuestions()
    {
        $query = $this->createQueryBuilder('q')
                        ->andWhere('q.status = 1')
                        ->orderBy('q.id', 'DESC');
                        // ->setMaxResults(7);
        return $query->getQuery()->getResult();
    }
}
