<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = $options['address_choices'];

        $builder
            ->add('shippingAddressId', ChoiceType::class, [
                'label' => 'Adresse de livraison',
                'choices' => $choices,
            ])
            ->add('billingSameAsShipping', CheckboxType::class, [
                'label' => 'Utiliser la même adresse pour la facturation',
                'required' => false,
                'data' => true,
            ])
            ->add('billingAddressId', ChoiceType::class, [
                'label' => 'Adresse de facturation',
                'choices' => $choices,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'address_choices' => [],
        ]);
        $resolver->setAllowedTypes('address_choices', 'array');
    }
}
