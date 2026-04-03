<?php

namespace App\Form;

use App\Entity\Book;
use App\Enum\BookFormat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class BookFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('isbn', TextType::class, [
                'label' => 'ISBN',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5],
            ])
            ->add('coverImage', TextType::class, [
                'label' => 'Image de couverture (URL)',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('priceCents', IntegerType::class, [
                'label' => 'Prix (en centimes)',
                'attr' => ['class' => 'form-control'],
                'help' => 'Entrez le prix en centimes (ex: 1599 pour 15.99€)',
            ])
            ->add('format', EnumType::class, [
                'class' => BookFormat::class,
                'label' => 'Format',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'help' => 'Laisser vide pour les livres numériques',
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ])
            ->add('publishedAt', DateTimeType::class, [
                'label' => 'Date de publication',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('authorNames', CollectionType::class, [
                'label' => 'Auteur(s)',
                'mapped' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'entry_type' => TextType::class,
                'entry_options' => [
                    'label' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Nom de l’auteur',
                    ],
                ],
            ])
            ->add('genreNames', CollectionType::class, [
                'label' => 'Genre(s)',
                'mapped' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'entry_type' => TextType::class,
                'entry_options' => [
                    'label' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Nom du genre',
                    ],
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $book = $event->getData();
            if (!$book instanceof Book) {
                return;
            }

            $authorNames = $book->getAuthors()
                ->map(static fn ($author) => $author->getName())
                ->toArray();

            if ($authorNames === []) {
                $authorNames = [''];
            }

            $event->getForm()->get('authorNames')->setData($authorNames);

            $genreNames = $book->getGenres()
                ->map(static fn ($genre) => $genre->getName())
                ->toArray();

            if ($genreNames === []) {
                $genreNames = [''];
            }

            $event->getForm()->get('genreNames')->setData($genreNames);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
            'genre_choices' => [],
            'author_choices' => [],
        ]);
        $resolver->setAllowedTypes('genre_choices', 'array');
        $resolver->setAllowedTypes('author_choices', 'array');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['genre_choices'] = $options['genre_choices'];
        $view->vars['author_choices'] = $options['author_choices'];
    }
}
