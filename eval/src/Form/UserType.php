<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('username')
            // ->add('password', PasswordType::class)
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class, 
                'mapped' => false,
                'constraints' => [
                            new Assert\Length([
                                'min' => 6,
                                'minMessage' => 'Your password should be at least {{ limit }} characters',
                                // max length allowed by Symfony for security reasons
                                'max' => 4096,
                            ]),
                        ],
                'invalid_message' => 'The password fields must match.',
                'first_options'  => ['label' => 'Password', 'help' => 'Laisser vide si le mot de passe reste inchangé'],
                'second_options' => ['label' => 'Repeat Password', 'help' => 'Laisser vide si le mot de passe reste inchangé'],
            ])
            ->add('description')
            ->add('avatar')
            ->add('firstname')
            ->add('lastname')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
}
