<?php

namespace App\Form;

use App\Entity\Answer;
use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class RightAnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $answers = $options['answers'];
        $builder
            ->add('right_answer', EntityType::class, [
                'label' => false,
                'class' => Answer::class,
                'choice_label' => 'content',
                'choices' => $answers,
                'expanded' => true,
                'multiple' => false,
                'empty_data' => null,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
            'answers' => null,
        ]);
    }
}
