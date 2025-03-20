<?php

namespace App\Form;

use App\Document\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('username', TextType::class)
            ->add('birthdate', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'male',
                    'Femme' => 'female',
                    'Autre' => 'other',
                ],
            ])
          
            ->add('bio', TextType::class, ['required' => false])
            ->add('picture', FileType::class, [
                'required' => false,
                'data_class' => null, 
            ])
            ->add('idCard', FileType::class, [
                'label' => 'Carte d\'identité (obligatoire pour la vérification)',
                'required' => true,
                'data_class' => null,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,application/pdf',
                    'class' => 'form-control'
                ],
                'help' => 'Veuillez télécharger une copie lisible de votre carte d\'identité pour vérification. Formats acceptés: JPEG, PNG, PDF.'
            ])
            ->add('save', SubmitType::class, ['label' => 'Créer un compte']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}