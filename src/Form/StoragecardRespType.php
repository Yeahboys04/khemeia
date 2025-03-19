<?php

namespace App\Form;

use App\Entity\Chimicalproduct;
use App\Entity\Shelvingunit;
use App\Entity\Storagecard;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class StoragecardRespType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [
            'Oui' => true,
            'Non' => false
        ];

        // Ajouter le champ pour choisir entre solide et liquide
        $builder->add('stateType', ChoiceType::class, [
            'choices' => [
                'Solide' => 'solid',
                'Liquide' => 'liquid'
            ],
            'expanded' => true,
            'multiple' => false,
            'required' => true,
            'label' => 'État physique du produit'
        ]);

        // Quantité adaptative avec unités
        $builder->add('stockquantity', NumberType::class, [
            'label' => 'Quantité en stock',
            'html5' => true,
            'scale' => 4, // Permet des décimales
            'attr' => [
                'placeholder' => 'Entrez une quantité',
                'class' => 'quantity-input'
            ]
        ]);

        // Unité de quantité
        $builder->add('stockUnit', ChoiceType::class, [
            'mapped' => false,
            'label' => false,
            'choices' => [
                'g' => 'g',
                'mg' => 'mg',
                'kg' => 'kg',
                'ml' => 'ml',
                'cl' => 'cl',
                'L' => 'L'
            ],
            'data' => 'g',
            'attr' => ['class' => 'unit-selector solid-units']
        ]);

        // Capacité adaptative avec unités
        $builder->add('capacity', NumberType::class, [
            'label' => 'Capacité totale',
            'html5' => true,
            'scale' => 4, // Permet des décimales
            'attr' => [
                'placeholder' => 'Entrez une capacité',
                'class' => 'capacity-input'
            ]
        ]);

        // Unité de capacité
        $builder->add('capacityUnit', ChoiceType::class, [
            'mapped' => false,
            'label' => false,
            'choices' => [
                'g' => 'g',
                'mg' => 'mg',
                'kg' => 'kg',
                'ml' => 'ml',
                'cl' => 'cl',
                'L' => 'L'
            ],
            'data' => 'g',
            'attr' => ['class' => 'unit-selector solid-units']
        ]);

        // Reste du formulaire conservé à l'identique
        $builder
            ->add('purity')
            ->add('serialnumber')
            ->add('temperature')
            ->add('opendate', DateType::class,
                ['label' => 'Date d\'ouverture',
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'required'   => false])
            ->add('expirationdate', DateType::class,
                ['label' => 'Date d\'expiration',
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'required'   => false])
            ->add('isarchived', ChoiceType::class, array(
                'choices'  => $choices,
                'choice_attr' => function($choice, $value)
                {return ['class' => 'radio-button'];},
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('isrisked', ChoiceType::class, array(
                'choices'  => $choices,
                'choice_attr' => function($choice, $value)
                {return ['class' => 'radio-button'];},
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('ispublished', ChoiceType::class, array(
                'choices'  => [
                    'Publier' => true,
                    'Analyser' => false],
                'choice_attr' => function($choice, $value)
                {return ['class' => 'radio-button'];},
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('idChimicalproduct',EntityType::class,[
                'class' => Chimicalproduct::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('qb')
                        ->orderBy('qb.nameChimicalproduct', 'ASC');
                },
                'choice_label' => function ($chimicalproduct) {
                    if($chimicalproduct->getCasnumber() !== null){
                        return $chimicalproduct . ' (' . $chimicalproduct->getCasnumber() . ')';
                    } else {
                        return $chimicalproduct;
                    }
                },

            ])
            ->add('uploadedSecurityFile', FileType::class, [
                'label' => 'Fiche de prudence (fichier PDF)',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Merci d\'importer un fichier PDF valide.',
                    ])
                ],
            ])
            ->add('uploadedAnalysisFile', FileType::class, [
                'label' => 'Certificat d\'analyse (fichier PDF)',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Merci d\'importer un fichier PDF valide.',
                    ])
                ],
            ])
            ->add('idProperty');

            if($options['action'] == 'copy'){
                $builder->add('idShelvingunit',EntityType::class,
                    ['class' => Shelvingunit::class,
                        'query_builder' => function (EntityRepository $er) use ($options) {
                            return $er->createQueryBuilder('qb')
                                ->innerJoin('App\Entity\Cupboard', 'c', 'WITH', 'c.idCupboard = qb.idCupboard')
                                ->innerJoin('App\Entity\Stock', 'st', 'WITH', 'c.idStock = st.idStock');},
                    ]);

            }else{
                $builder->add('idShelvingunit',EntityType::class,
                    ['class' => Shelvingunit::class,
                        'query_builder' => function (EntityRepository $er) use ($options) {
                            return $er->createQueryBuilder('qb')
                                ->innerJoin('App\Entity\Cupboard', 'c', 'WITH', 'c.idCupboard = qb.idCupboard')
                                ->innerJoin('App\Entity\Stock', 'st', 'WITH', 'c.idStock = st.idStock')
                                ->where('st.idSite = :querysite')
                                ->setParameter('querysite' , $options['idSite']);},
                    ]);
            }

            $builder->add('idSupplier')
            ->add('reference')
            ->add('commentary');

        // Gestionnaire d'événements pour la conversion des unités
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            // Si les champs d'unités sont présents dans les données
            if (isset($data['stockUnit']) && isset($data['stockquantity'])) {
                // Convertir en g ou ml selon le type d'état physique
                switch ($data['stockUnit']) {
                    // Conversions pour solides
                    case 'mg':
                        $data['stockquantity'] = $data['stockquantity'] / 1000; // mg -> g
                        break;
                    case 'kg':
                        $data['stockquantity'] = $data['stockquantity'] * 1000; // kg -> g
                        break;
                    // Conversions pour liquides
                    case 'cl':
                        $data['stockquantity'] = $data['stockquantity'] * 10; // cl -> ml
                        break;
                    case 'L':
                        $data['stockquantity'] = $data['stockquantity'] * 1000; // l -> ml
                        break;
                }
            }

            // Même chose pour la capacité
            if (isset($data['capacityUnit']) && isset($data['capacity'])) {
                switch ($data['capacityUnit']) {
                    // Conversions pour solides
                    case 'mg':
                        $data['capacity'] = $data['capacity'] / 1000; // mg -> g
                        break;
                    case 'kg':
                        $data['capacity'] = $data['capacity'] * 1000; // kg -> g
                        break;
                    // Conversions pour liquides
                    case 'cl':
                        $data['capacity'] = $data['capacity'] * 10; // cl -> ml
                        break;
                    case 'L':
                        $data['capacity'] = $data['capacity'] * 1000; // l -> ml
                        break;
                }
            }

            // On retire les champs non mappés avant de soumettre
            unset($data['stockUnit']);
            unset($data['capacityUnit']);

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Storagecard::class,
            'idSite' => '1',
        ]);
        $resolver->setAllowedTypes('idSite', 'string');
    }
}