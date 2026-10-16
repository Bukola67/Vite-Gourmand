<?php

namespace App\Form;

use App\Entity\CustomerOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{DateType, EmailType, IntegerType, TelType, TextType, TimeType};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $minimum = $options['minimum_persons'];
        $text = static fn(string $label, string $help, int $max, string $autocomplete): array => [
            'label' => $label,
            'help' => $help,
            'required' => true,
            'empty_data' => '',
            'attr' => ['maxlength' => $max, 'autocomplete' => $autocomplete],
            'constraints' => [
                new Assert\NotBlank(message: 'Ce champ est obligatoire.'),
                new Assert\Length(max: $max, maxMessage: 'Saisissez au maximum {{ limit }} caractères.'),
            ],
        ];

        $builder
            ->add('customerFirstName', TextType::class, $text('Prénom', 'Prérempli depuis votre compte ; modifiez-le si nécessaire pour cette commande.', 100, 'given-name'))
            ->add('customerLastName', TextType::class, $text('Nom', 'Indiquez le nom de la personne à contacter pour la prestation.', 100, 'family-name'));

        $email = $text('Adresse e-mail', 'La confirmation de commande sera envoyée à cette adresse.', 180, 'email');
        $email['constraints'][] = new Assert\Email(message: 'Saisissez une adresse e-mail valide, par exemple nom@exemple.fr.');
        $builder->add('customerEmail', EmailType::class, $email);

        $phone = $text('Téléphone portable', 'Indiquez un portable joignable : 7 à 15 chiffres, avec indicatif international si nécessaire. Espaces, parenthèses, points et tirets autorisés (20 caractères au total).', 20, 'tel');
        $phone['constraints'][] = new Assert\Regex(
            pattern: '/^\+?(?:[ .()\-]*\d){7,15}[ .()\-]*$/',
            message: 'Saisissez un numéro valide de 7 à 15 chiffres, par exemple 06 12 34 56 78 ou +33 6 12 34 56 78.'
        );
        $builder
            ->add('customerPhone', TelType::class, $phone)
            ->add('deliveryAddress', TextType::class, $text('Adresse de prestation', 'Numéro et nom de rue. L’adresse du compte est proposée, mais peut être modifiée.', 255, 'street-address'))
            ->add('deliveryPostalCode', TextType::class, $text('Code postal', 'Indiquez le code postal du lieu de prestation (5 chiffres pour une adresse française).', 20, 'postal-code'))
            ->add('deliveryCity', TextType::class, $text('Ville', 'Livraison gratuite à Bordeaux ; ailleurs : 5 € + 0,59 €/km selon le calcul de distance actuel.', 100, 'address-level2'))
            ->add('deliveryPlace', TextType::class, $text('Lieu et précisions de livraison', 'Exemple : domicile, salle des fêtes, étage ou accès. Si aucune précision : indiquez « Domicile ».', 255, 'off'))
            ->add('deliveryDate', DateType::class, [
                'label' => 'Date de prestation',
                'help' => 'Choisissez aujourd’hui ou une date ultérieure. La date et l’heure combinées doivent être dans le futur.',
                'widget' => 'single_text', 'input' => 'datetime_immutable', 'required' => true,
                'invalid_message' => 'Saisissez une date valide.',
                'attr' => ['min' => (new \DateTimeImmutable('today', new \DateTimeZone('Europe/Paris')))->format('Y-m-d')],
                'constraints' => [
                    new Assert\NotBlank(message: 'La date de prestation est obligatoire.'),
                    new Assert\GreaterThanOrEqual(value: 'today', message: 'La date de prestation doit être aujourd’hui ou ultérieure.'),
                ],
            ])
            ->add('deliveryTime', TimeType::class, [
                'label' => 'Heure souhaitée de livraison',
                'help' => 'Indiquez l’heure souhaitée. Pour aujourd’hui, elle doit être postérieure à l’heure actuelle.',
                'widget' => 'single_text', 'input' => 'datetime_immutable', 'required' => true,
                'invalid_message' => 'Saisissez une heure valide.',
                'constraints' => [new Assert\NotBlank(message: 'L’heure de livraison est obligatoire.')],
            ])
            ->add('personCount', IntegerType::class, [
                'label' => 'Nombre de personnes',
                'help' => sprintf('Minimum : %d personnes. Remise de 10 %% sur les menus dès %d personnes, hors livraison.', $minimum, $minimum + 5),
                'required' => true,
                'invalid_message' => 'Saisissez un nombre entier de personnes.',
                'attr' => ['min' => $minimum, 'step' => 1, 'inputmode' => 'numeric'],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nombre de personnes est obligatoire.'),
                    new Assert\GreaterThanOrEqual(value: $minimum, message: 'Ce menu nécessite au minimum {{ compared_value }} personnes.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerOrder::class,
            'minimum_persons' => 1,
            'csrf_token_id' => 'customer_order',
            'csrf_message' => 'Votre session de formulaire a expiré. Rechargez la page avant de réessayer.',
        ]);
        $resolver->setAllowedTypes('minimum_persons', 'int');
        $resolver->setAllowedValues('minimum_persons', static fn(int $value): bool => $value >= 1);
    }
}
