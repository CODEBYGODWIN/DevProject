<?php

namespace App\Form;

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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use App\Document\User;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => "L'email est requis"]),
                    new Email(['message' => "L'email n'est pas valide"]),
                    new Length([
                        'min' => 8,
                        'minMessage' => "L'email doit avoir au moins 8 caractères",
                    ]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new NotBlank(['message' => "Le mot de passe est requis"]),
                    new Length([
                        'min' => 8,
                        'minMessage' => "Le mot de passe doit contenir au moins 8 caractères",
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
                        'message' => "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial",
                    ]),
                ],
            ])
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => "Le nom d'utilisateur est requis"]),
                    new Length([
                        'min' => 6,
                        'minMessage' => "Le nom d'utilisateur doit avoir au moins 6 caractères",
                    ]),
                ],
            ])
            ->add('birthdate', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => "La date de naissance est requise"]),
                    new LessThanOrEqual([
                        'value' => '-18 years',
                        'message' => "Vous devez avoir au moins 18 ans pour vous inscrire",
                    ]),
                ],
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
            ->add('save', SubmitType::class, ['label' => 'Créer un compte']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
