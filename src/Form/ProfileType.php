<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('avatarFile', FileType::class, [
                'label' => 'Image de profil',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp',
                    'class' => 'hidden',
                ],
            ])
            ->add('croppedAvatarData', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('biography', TextareaType::class, [
                'label' => 'Présentation',
                'required' => false,
                'attr' => [
                    'maxlength' => 500,
                    'rows' => 5,
                    'placeholder' => 'Parlez-nous de vous en quelques mots...',
                ],
            ])
            ->add('externalLink1', UrlType::class, [
                'label' => 'Lien externe 1',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://exemple.com',
                ],
            ])
            ->add('externalLink2', UrlType::class, [
                'label' => 'Lien externe 2',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://exemple.com',
                ],
            ])
            ->add('externalLink3', UrlType::class, [
                'label' => 'Lien externe 3',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://exemple.com',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'profile_form',
        ]);
    }
}
