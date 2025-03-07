<?php

namespace App\Form;

use App\Entity\Chimicalproduct;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TracabilityFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entityManager = $options['entityManager'];
        $isAdmin = $options['is_admin'] ?? false;

        $builder
            ->add('defaultFilter', CheckboxType::class, [
                'label' => 'Utiliser le filtre par défaut (mon historique personnel)',
                'required' => false,
                'data' => true,
            ])
            ->add('filterByCMR', CheckboxType::class, [
                'label' => 'Filtrer les produits CMR uniquement',
                'required' => false,
                'data' => false,
            ])
            ->add('dateRangeFilter', ChoiceType::class, [
                'label' => 'Période',
                'choices' => [
                    'Toutes les dates' => 'all',
                    'Dernier mois' => 'lastMonth',
                    'Derniers 3 mois' => 'last3Months',
                    'Derniers 6 mois' => 'last6Months',
                    'Dernière année' => 'lastYear',
                    'Période personnalisée' => 'custom',
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'data' => 'all',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ]);

        // Afficher le champ de sélection d'utilisateur seulement pour les admins
        if ($isAdmin) {
            $builder->add('showAllUsers', CheckboxType::class, [
                'label' => 'Inclure tous les utilisateurs dans le rapport',
                'required' => false,
                'data' => false,
                'mapped' => false, // Ce champ n'est pas directement mappé à une entité
            ]);

            $builder->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getFullname();
                },
                'label' => 'Ou sélectionner un utilisateur spécifique',
                'required' => false,
                'placeholder' => 'Sélectionner un utilisateur',
                'attr' => [
                    'class' => 'form-control select2',
                    'data-dependent-field' => 'showAllUsers', // Champ dépendant
                ],
            ]);
        }

        $builder->add('product', EntityType::class, [
            'class' => Chimicalproduct::class,
            'choice_label' => function (Chimicalproduct $product) {
                return $product->getNameChimicalproduct();
            },
            'label' => 'Produit chimique',
            'required' => false,
            'placeholder' => 'Tous les produits',
            'attr' => ['class' => 'form-control select2'],
        ])
            ->add('showSymbols', CheckboxType::class, [
                'label' => 'Afficher les symboles de danger',
                'required' => false,
                'data' => true,
            ])
            ->add('showCautionaryAdvice', CheckboxType::class, [
                'label' => 'Afficher les conseils de prudence',
                'required' => false,
                'data' => true,
            ])
            ->add('showDangerNotes', CheckboxType::class, [
                'label' => 'Afficher les mentions de danger',
                'required' => false,
                'data' => true,
            ])
            ->add('showProductTypes', CheckboxType::class, [
                'label' => 'Afficher les types de produit',
                'required' => false,
                'data' => true,
            ])
            ->add('showQuantity', CheckboxType::class, [
                'label' => 'Afficher les quantités utilisées',
                'required' => false,
                'data' => true,
            ])
            ->add('showDate', CheckboxType::class, [
                'label' => 'Afficher les dates d\'utilisation',
                'required' => false,
                'data' => true,
            ])
            ->add('showCMR', CheckboxType::class, [
                'label' => 'Afficher le statut CMR',
                'required' => false,
                'data' => true,
            ])
            ->add('sortBy', ChoiceType::class, [
                'label' => 'Trier par',
                'choices' => [
                    'Date (plus récente d\'abord)' => 'date_desc',
                    'Date (plus ancienne d\'abord)' => 'date_asc',
                    'Nom du produit' => 'product_name',
                    'Quantité utilisée' => 'quantity',
                    'Utilisateur' => 'user_name', // Ajout de l'option de tri par utilisateur
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'data' => 'date_desc',
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);

        $resolver->setRequired(['entityManager']);
        $resolver->setDefaults(['is_admin' => false]);
    }
}