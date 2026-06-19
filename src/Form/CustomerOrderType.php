<?php

namespace App\Form;

use App\Entity\CustomerOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class CustomerOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('deliveryAddress', TextType::class, [
                'label' => 'Adresse de livraison',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'L’adresse de livraison est obligatoire.']),
                ],
            ])
            ->add('deliveryPostalCode', TextType::class, [
                'label' => 'Code postal',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le code postal est obligatoire.']),
                ],
            ])
            ->add('deliveryCity', TextType::class, [
                'label' => 'Ville',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La ville est obligatoire.']),
                ],
            ])
            ->add('deliveryPlace', TextType::class, [
                'label' => 'Lieu de livraison',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le lieu de livraison est obligatoire.']),
                ],
            ])
            ->add('deliveryDate', DateType::class, [
                'label' => 'Date de prestation',
                'widget' => 'single_text',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La date de prestation est obligatoire.']),
                ],
            ])
            ->add('deliveryTime', TimeType::class, [
                'label' => 'Heure de livraison',
                'widget' => 'single_text',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'L’heure de livraison est obligatoire.']),
                ],
            ])
            ->add('personCount', IntegerType::class, [
                'label' => 'Nombre de personnes',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le nombre de personnes est obligatoire.']),
                    new GreaterThanOrEqual([
                        'value' => 1,
                        'message' => 'Le nombre de personnes doit être supérieur ou égal à 1.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerOrder::class,
        ]);
    }
}