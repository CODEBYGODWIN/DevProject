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

class UserEditType extends AbstractType
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
            ->add('orientation', ChoiceType::class, [
                'choices' => [
                    'Hétérosexuel' => 'heterosexual',
                    'Homosexuel' => 'homosexual',
                    'Lesbienne' => 'lesbian',
                    'Pansexuel' => 'pansexual',
                    'Asexuel' => 'asexual',
                    'Non-binaire' => 'non-binary',
                    'Bisexuel' => 'bisexual',
                    'Autre' => 'other',
                ],
            ])
            ->add('bio', TextType::class, ['required' => false])
            ->add('picture', FileType::class, [
                'required' => false,
                'data_class' => null, 
            ])
            
            ->add('save', SubmitType::class, ['label' => 'Mettre à jour']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
