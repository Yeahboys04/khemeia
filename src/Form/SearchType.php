<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\Chimicalproduct;
use App\Entity\Storagecard;
use PhpParser\Node\Expr\Cast\Object_;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SearchType as SymfonySearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SearchType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Ajout du choix de recherche sur tous les sites (modifié pour être affiché en premier)
        $builder->add("searchAll", ChoiceType::class,[
            'label' => 'Chercher sur tous les sites ? ',
            'mapped' => false,
            'choices' => [
                'non' => 'non',
                'oui' => 'oui',
            ],
            'expanded' => true,
            'multiple' => false,
            'data' => 'non', // Default value changed to 'non'
            'attr' => [
                'class' => 'search-all-selector',
            ],
        ]);

        // Ajout du choix de site avec option "Tous les sites"
        $builder->add('idSite', EntityType::class, [
            'class' => Site::class,
            'mapped' => false,
            'placeholder' => 'Sélectionnez un site',
            'required' => true,
            'attr' => [
                'class' => 'form-select',
            ],
        ]);

        // Champ de recherche unifié (produit ou CAS)
        $builder->add('searchType', ChoiceType::class, [
            'mapped' => false,
            'choices' => [
                'Produit' => 'product',
                'Numéro CAS' => 'cas',
            ],
            'expanded' => true,
            'multiple' => false,
            'data' => 'product', // Default value
            'attr' => [
                'class' => 'search-type-selector',
            ],
        ]);

        // Champ pour la recherche de produit
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            // Par défaut, on affiche le champ de recherche par produit
            $form->add('idChimicalproduct', EntityType::class, [
                'class' => Chimicalproduct::class,
                'placeholder' => 'Sélectionnez un produit',
                'required' => true,
                'attr' => [
                    'class' => 'form-select product-search',
                    'data-placeholder' => 'Rechercher un produit',
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.idChimicalproduct', 'ASC'); // Utilisation de l'ID au lieu de 'name'
                },
            ]);

            // Ajouter aussi le champ pour la recherche par CAS (caché par défaut via JS)
            $form->add('casSearch', EntityType::class, [
                'class' => Chimicalproduct::class,
                'placeholder' => 'Sélectionnez un numéro CAS',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-select cas-search',
                    'data-placeholder' => 'Rechercher par numéro CAS',
                    'style' => 'display: none;', // Caché par défaut
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.casnumber IS NOT NULL')
                        ->orderBy('c.casnumber', 'ASC');
                },
                'choice_label' => function ($chimicalproduct) {
                    return $chimicalproduct->getCasnumber() . ' - ' . $chimicalproduct;
                },
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Storagecard::class,
            'csrf_protection' => true,
        ]);
    }
}