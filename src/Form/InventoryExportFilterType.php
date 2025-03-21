<?php

namespace App\Form;

use App\Entity\Chimicalproduct;
use App\Entity\Shelvingunit;
use App\Entity\Site;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class InventoryExportFilterType extends AbstractType
{
    private $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('defaultFilter', CheckboxType::class, [
                'required' => false,
                'data' => true,
                'label' => false
            ])
            // Stock status filter
            ->add('stockStatus', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Tous les produits' => 'all',
                    'Produits avec stock suffisant' => 'sufficient',
                    'Produits avec stock faible ou périmés' => 'low_expired',
                    'Produits en rupture de stock' => 'empty'
                ],
                'placeholder' => 'Sélectionnez un statut de stock'
            ]);

        // Ajouter les options spécifiques aux administrateurs
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            $builder
                ->add('allSites', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Afficher les produits de tous les sites',
                    'data' => false
                ])
                ->add('site', EntityType::class, [
                    'class' => Site::class,
                    'required' => false,
                    'placeholder' => 'Sélectionner un site spécifique',
                    'label' => 'Site',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('s')
                            ->orderBy('s.nameSite', 'ASC');
                    }
                ]);
        }

        $builder
            // Filter by chemical product
            ->add('product', EntityType::class, [
                'class' => Chimicalproduct::class,
                'required' => false,
                'placeholder' => 'Tous les produits',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.nameChimicalproduct', 'ASC');
                }
            ])
            // Filter by location (shelving unit)
            ->add('location', EntityType::class, [
                'class' => Shelvingunit::class,
                'required' => false,
                'placeholder' => 'Tous les emplacements',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.nameShelvingunit', 'ASC');
                }
            ])
            // Filter by CMR (Cancérigènes, Mutagènes, Reprotoxiques)
            ->add('filterByCMR', CheckboxType::class, [
                'required' => false,
                'label' => 'Uniquement les produits CMR',
            ])
            // Sort options
            ->add('sortBy', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Nom du produit (A-Z)' => 'product_asc',
                    'Nom du produit (Z-A)' => 'product_desc',
                    'Quantité en stock (croissant)' => 'quantity_asc',
                    'Quantité en stock (décroissant)' => 'quantity_desc',
                    'Date d\'expiration (proche-lointaine)' => 'expiration_asc',
                    'Date d\'expiration (lointaine-proche)' => 'expiration_desc',
                    'Emplacement (A-Z)' => 'location_asc'
                ],
                'placeholder' => 'Trier par...'
            ])
            // Display options
            ->add('showDetails', CheckboxType::class, [
                'required' => false,
                'label' => 'Afficher les détails complets',
            ])
            ->add('showLocation', CheckboxType::class, [
                'required' => false,
                'label' => 'Afficher l\'emplacement',
                'data' => true,
            ])
            ->add('showQuantity', CheckboxType::class, [
                'required' => false,
                'label' => 'Afficher la quantité en stock',
                'data' => true,
            ])
            ->add('showExpiration', CheckboxType::class, [
                'required' => false,
                'label' => 'Afficher la date d\'expiration',
                'data' => true,
            ])
            ->add('showOpenDate', CheckboxType::class, [
                'required' => false,
                'label' => 'Afficher la date d\'ouverture',
            ])
            ->add('showCMR', CheckboxType::class, [
                'required' => false,
                'label' => 'Afficher le statut CMR',
            ])
            ->add('showSupplier', CheckboxType::class, [
                'required' => false,
                'label' => 'Afficher le fournisseur',
            ])
            ->add('showSymbols', CheckboxType::class, [
                'required' => false,
                'label' => 'Afficher les symboles de danger',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'attr' => [
                'novalidate' => 'novalidate',
                'target' => '_blank' // Pour ouvrir le PDF dans un nouvel onglet
            ]
        ]);
    }
}