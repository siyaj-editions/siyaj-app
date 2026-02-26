<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shippingAddress', CheckoutAddressType::class, [
                'label' => false,
            ])
            ->add('billingSameAsShipping', CheckboxType::class, [
                'label' => 'Utiliser la même adresse pour la facturation',
                'required' => false,
                'data' => true,
            ])
            ->add('billingAddress', CheckoutAddressType::class, [
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
