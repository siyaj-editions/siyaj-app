<?php

namespace App\Form;

use App\Entity\ManuscriptSubmission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ManuscriptSubmissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [new NotBlank(message: 'Le prénom est requis.')],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new NotBlank(message: 'Le nom est requis.')],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [new NotBlank(message: 'L\'email est requis.')],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('bookTitle', TextType::class, [
                'label' => 'Titre du manuscrit',
                'constraints' => [new NotBlank(message: 'Le titre du manuscrit est requis.')],
            ])
            ->add('genre', TextType::class, [
                'label' => 'Genre littéraire',
                'required' => false,
            ])
            ->add('synopsis', TextareaType::class, [
                'label' => 'Résumé / note d\'intention',
                'attr' => ['rows' => 8],
                'constraints' => [
                    new NotBlank(message: 'Le résumé est requis.'),
                ],
            ])
            ->add('manuscriptUrl', UrlType::class, [
                'label' => 'Lien vers le manuscrit (Drive, Dropbox, PDF...)',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ManuscriptSubmission::class,
        ]);
    }
}
