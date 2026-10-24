<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class ProfileType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'autocomplete' => 'family-name',
                    'maxlength' => 100,
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Indiquez votre nom.'
                    ),
                    new Assert\Length(max: 100),
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'autocomplete' => 'given-name',
                    'maxlength' => 100,
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Indiquez votre prénom.'
                    ),
                    new Assert\Length(max: 100),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'disabled' => true,
                'help' => 'Votre adresse e-mail sert d’identifiant de connexion.',
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => [
                    'autocomplete' => 'street-address',
                    'maxlength' => 100,
                ],
                'constraints' => [
                    new Assert\Length(max: 100),
                ],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'required' => false,
                'attr' => [
                    'autocomplete' => 'postal-code',
                    'maxlength' => 20,
                ],
                'constraints' => [
                    new Assert\Length(max: 20),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'required' => false,
                'attr' => [
                    'autocomplete' => 'address-level2',
                    'maxlength' => 100,
                ],
                'constraints' => [
                    new Assert\Length(max: 100),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'required' => false,
                'attr' => [
                    'autocomplete' => 'tel',
                    'maxlength' => 100,
                ],
                'constraints' => [
                    new Assert\Length(max: 100),
                ],
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_token_id' => 'profile_edit',
        ]);
    }
}