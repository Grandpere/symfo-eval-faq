<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
                        ->innerJoin('q.author', 'u')
                        ->addSelect('u')
                        ->innerJoin('q.tags', 't')
                        ->addSelect('t')
                        ->andWhere('q.status = 1')
                        ->orderBy('q.id', 'DESC')
                        ->setFirstResult(0)
                        ->setMaxResults(7);
        return new Paginator($query);
    }

    public function allActiveQuestions($page = 1, $max_results = 7)
    {
        if ($page < 1) {
            throw new \InvalidArgumentException('L\'argument $page ne peut être inférieur à 1 (valeur : "'.$page.'").');
        }
        $query = $this->createQueryBuilder('q')
                        ->innerJoin('q.author', 'u')
                        ->addSelect('u')
                        ->innerJoin('q.tags', 't')
                        ->addSelect('t')
                        ->andWhere('q.status = 1')
                        ->orderBy('q.id', 'DESC')
                        ->setFirstResult(($page - 1) * $max_results)
                        ->setMaxResults($max_results);
        return new Paginator($query);
    }

    public function allInactiveQuestions()
    {
        $query = $this->createQueryBuilder('q')
                        ->andWhere('q.status = 0')
                        ->orderBy('q.id', 'DESC');
        return $query->getQuery()->getResult();
    }
}