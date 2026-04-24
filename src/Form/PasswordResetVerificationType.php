<?php

namespace App\Form;

use App\Model\PasswordResetVerification;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class PasswordResetVerificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => ['class' => 'form-control', 'autocomplete' => 'email'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer une adresse email.'),
                    new Email(message: 'Adresse email invalide.'),
                ],
            ])
            ->add('code', TextType::class, [
                'label' => 'Code à 6 chiffres',
                'attr' => ['class' => 'form-control', 'inputmode' => 'numeric', 'maxlength' => 6],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer le code reçu.'),
                    new Regex(pattern: '/^\d{6}$/', message: 'Le code doit contenir 6 chiffres.'),
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
                    'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password'],
                    'constraints' => [
                        new NotBlank(message: 'Veuillez entrer un nouveau mot de passe.'),
                        new Length(min: 6, minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères.', max: 4096),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'Les mots de passe doivent être identiques.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PasswordResetVerification::class,
        ]);
    }
}
