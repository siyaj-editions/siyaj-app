<?php

namespace App\Form;

use App\Entity\Newsletter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewsletterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => 'Votre adresse email',
                'class' => 'flex-1 px-5 py-3.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/40 focus:border-gold focus:bg-white/15 transition-all duration-200',
            ],
            'constraints' => [
                new NotBlank(message: 'Veuillez renseigner une adresse email.'),
                new Email(message: 'Adresse email invalide.'),
            ],
        ])->add('company', TextType::class, [
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
            'data_class' => Newsletter::class,
        ]);
    }
}
