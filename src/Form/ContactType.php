<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre email',
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'L’adresse email est obligatoire.'),
                    new Email(message: 'Veuillez saisir une adresse email valide.'),
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'Le titre est obligatoire.'),
                    new Length(
                        min: 3,
                        minMessage: 'Le titre doit contenir au moins 3 caractères.'
                    ),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Message',
                'required' => true,
                'empty_data' => '',
                'attr' => ['rows' => 6],
                'constraints' => [
                    new NotBlank(message: 'Le message est obligatoire.'),
                    new Length(
                        min: 10,
                        minMessage: 'Le message doit contenir au moins 10 caractères.'
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}