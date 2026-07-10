<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'label' => 'Note',
                'choices' => [
                    '1 / 5' => 1,
                    '2 / 5' => 2,
                    '3 / 5' => 3,
                    '4 / 5' => 4,
                    '5 / 5' => 5,
                ],
                'placeholder' => 'Choisissez une note',
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez sélectionner une note.'
                    ),
                    new Range(
                        min: 1,
                        max: 5,
                        notInRangeMessage: 'La note doit être comprise entre 1 et 5.'
                    ),
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Décrivez votre expérience...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}