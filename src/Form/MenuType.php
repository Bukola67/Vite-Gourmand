<?php

namespace App\Form;

use App\Entity\Menu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('theme', TextType::class, [
                'label' => 'Thème',
                'required' => false,
            ])
            ->add('diet', TextType::class, [
                'label' => 'Régime',
                'required' => false,
            ])
            ->add('minimumPersons', NumberType::class, [
                'label' => 'Nombre minimum de personnes',
            ])
            ->add('basePrice', NumberType::class, [
                'label' => 'Prix minimum',
                'scale' => 2,
            ])
            ->add('conditionText', TextareaType::class, [
                'label' => 'Conditions',
                'required' => false,
            ])
            ->add('stockAvailable', NumberType::class, [
                'label' => 'Stock disponible',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}