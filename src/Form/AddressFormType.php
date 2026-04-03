<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $countries = Countries::getNames('fr');
        asort($countries, SORT_NATURAL | SORT_FLAG_CASE);

        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [new NotBlank(message: 'Le prénom est requis.')],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new NotBlank(message: 'Le nom est requis.')],
            ])
            ->add('numero', TextType::class, [
                'label' => 'Numéro (téléphone)',
                'required' => false,
            ])
            ->add('street', TextType::class, [
                'label' => 'Adresse',
                'constraints' => [new NotBlank(message: 'L\'adresse est requise.')],
            ])
            ->add('street2', TextType::class, [
                'label' => 'Complément',
                'required' => false,
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'constraints' => [new NotBlank(message: 'Le code postal est requis.')],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'constraints' => [new NotBlank(message: 'La ville est requise.')],
            ])
            ->add('country', ChoiceType::class, [
                'label' => 'Pays',
                'choices' => array_flip($countries),
                'placeholder' => 'Sélectionnez un pays',
                'constraints' => [new NotBlank(message: 'Le pays est requis.')],
                'attr' => [
                    'data-country-select' => 'true',
                ],
            ])
            ->add('isDefault', CheckboxType::class, [
                'label' => 'Définir comme adresse par défaut',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
