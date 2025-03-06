<?php

namespace App\Form;

use App\Entity\Chimicalproduct;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entityManager = $options['entityManager'];

        $builder
            ->add('defaultExport', CheckboxType::class, [
                'label' => 'Utiliser l\'exportation par défaut pour GPUC',
                'required' => false,
                'data' => true,
            ])
            ->add('allSites', CheckboxType::class, [
                'label' => 'Exporter les données de tous les sites',
                'required' => false,
                'data' => false,
                'mapped' => false,
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nameSite',
                'label' => 'Site',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('stockFilter', ChoiceType::class, [
                'label' => 'Filtre de stock',
                'choices' => [
                    'Tous les produits' => 'all',
                    'Stock normal' => 'normal',
                    'Stock faible' => 'low',
                    'Rupture de stock' => 'empty',
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'data' => 'all',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('expirationFilter', ChoiceType::class, [
                'label' => 'Filtre d\'expiration',
                'choices' => [
                    'Tous les produits' => 'all',
                    'Produits valides' => 'valid',
                    'Produits expirés' => 'expired',
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'data' => 'all',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('includeArchived', CheckboxType::class, [
                'label' => 'Inclure les produits archivés',
                'required' => false,
                'data' => false,
            ])
            ->add('product', EntityType::class, [
                'class' => Chimicalproduct::class,
                'choice_label' => function (Chimicalproduct $product) {
                    return $product->getCasnumber() ? $product->getNameChimicalproduct() . ' (' . $product->getCasnumber() . ')' : $product->getNameChimicalproduct();
                },
                'label' => 'Produit chimique (optionnel)',
                'required' => false,
                'placeholder' => 'Tous les produits',
                'attr' => ['class' => 'form-control select2'],
            ])
            ->add('includeHeaders', CheckboxType::class, [
                'label' => 'Inclure les en-têtes dans le fichier CSV',
                'required' => false,
                'data' => true,
            ]);

        // Section pour les champs à inclure dans l'export
        $builder->add('includeCasNumber', CheckboxType::class, [
            'label' => 'Numéro CAS',
            'required' => false,
            'data' => true,
        ])
            ->add('includeProductName', CheckboxType::class, [
                'label' => 'Nom du produit',
                'required' => false,
                'data' => true,
            ])
            ->add('includeQuantity', CheckboxType::class, [
                'label' => 'Quantité en stock',
                'required' => false,
                'data' => true,
            ])
            ->add('includeCapacity', CheckboxType::class, [
                'label' => 'Capacité',
                'required' => false,
                'data' => false,
            ])
            ->add('includeLocation', CheckboxType::class, [
                'label' => 'Emplacement (Stock, Armoire, Étagère)',
                'required' => false,
                'data' => true,
            ])
            ->add('includeSite', CheckboxType::class, [
                'label' => 'Site',
                'required' => false,
                'data' => true,
            ])
            ->add('includeExpiration', CheckboxType::class, [
                'label' => 'Date d\'expiration',
                'required' => false,
                'data' => false,
            ])
            ->add('includeReference', CheckboxType::class, [
                'label' => 'Référence',
                'required' => false,
                'data' => false,
            ])
            ->add('includeSupplier', CheckboxType::class, [
                'label' => 'Fournisseur',
                'required' => false,
                'data' => false,
            ])
            ->add('includePurity', CheckboxType::class, [
                'label' => 'Pureté',
                'required' => false,
                'data' => false,
            ])
            ->add('includeOpenDate', CheckboxType::class, [
                'label' => 'Date d\'ouverture',
                'required' => false,
                'data' => false,
            ])
            ->add('includeSerialNumber', CheckboxType::class, [
                'label' => 'Numéro de série',
                'required' => false,
                'data' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);

        $resolver->setRequired(['entityManager']);
        $resolver->setAllowedTypes('entityManager', [EntityManagerInterface::class]);
    }
}