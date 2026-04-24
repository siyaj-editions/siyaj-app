<?php

namespace App\Form;

use App\Model\ContactMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new NotBlank(message: 'Le prénom est requis.'),
                    new Length(max: 100),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank(message: 'Le nom est requis.'),
                    new Length(max: 100),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(message: 'L’email est requis.'),
                    new Email(message: 'Adresse email invalide.'),
                    new Length(max: 180),
                ],
            ])
            ->add('subject', ChoiceType::class, [
                'label' => 'Motif',
                'placeholder' => 'Sélectionnez un motif',
                'choices' => [
                    'Relations publiques' => 'relations_publiques',
                    'Communication' => 'communication',
                    'Médias' => 'medias',
                    'Service éditorial' => 'service_editorial',
                    'Relation libraires' => 'relation_libraires',
                    'Suivi de commande' => 'suivi_de_commande',
                    'Autre' => 'autre',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le motif est requis.'),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['rows' => 7],
                'constraints' => [
                    new NotBlank(message: 'Le message est requis.'),
                    new Length(min: 10, max: 4000, minMessage: 'Le message doit contenir au moins 10 caractères.'),
                ],
            ])
            ->add('company', TextType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'tabindex' => '-1',
                    'autocomplete' => 'nope',
                    'spellcheck' => 'false',
                    'class' => 'absolute -left-[10000px] top-auto h-px w-px overflow-hidden opacity-0 pointer-events-none',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactMessage::class,
        ]);
    }
}
