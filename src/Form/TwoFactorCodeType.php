<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class TwoFactorCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('code', TextType::class, [
            'label' => 'Code de vérification',
            'constraints' => [
                new NotBlank(message: 'Veuillez saisir le code reçu par email.'),
                new Length(exactly: 6, exactMessage: 'Le code doit contenir exactement {{ limit }} chiffres.'),
                new Regex(pattern: '/^\d{6}$/', message: 'Le code doit être composé de 6 chiffres.'),
            ],
            'attr' => [
                'maxlength' => 6,
                'inputmode' => 'numeric',
                'autocomplete' => 'one-time-code',
                'placeholder' => '000000',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
