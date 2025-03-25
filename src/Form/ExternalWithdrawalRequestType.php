<?php

namespace App\Form;

use App\Entity\ExternalWithdrawalRequest;
use App\Entity\Site;
use App\Entity\Storagecard;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Doctrine\ORM\EntityManagerInterface;

class ExternalWithdrawalRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Si on a défini un site source spécifique
        $sourceSite = null;
        if (isset($options['source_site_id']) && $options['source_site_id']) {
            $sourceSite = $options['entity_manager']->getRepository(Site::class)->find($options['source_site_id']);
        }

        $builder
            ->add('sourceSite', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nameSite',
                'label' => 'Site source',
                'placeholder' => 'Sélectionnez un site',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un site source']),
                ],
                'attr' => [
                    'class' => 'form-control select2',
                    'data-placeholder' => 'Sélectionnez un site'
                ],
                'data' => $sourceSite,
                'disabled' => $options['edit_mode'] || $sourceSite !== null
            ])
            ->add('sourceStoragecard', EntityType::class, [
                'class' => Storagecard::class,
                'choice_label' => function (Storagecard $storagecard) {
                    return $storagecard->getIdChimicalproduct()->getNameChimicalproduct() .
                        ' (' . $storagecard->getStockquantity() . ' ' .
                        ($storagecard->getIdChimicalproduct()->getSolvent() ?: 'unités') . ')';
                },
                'label' => 'Fiche de stockage (produit)',
                'placeholder' => 'Sélectionnez un produit',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un produit']),
                ],
                'attr' => [
                    'class' => 'form-control select2',
                    'data-placeholder' => 'Sélectionnez un produit'
                ],
                'disabled' => $options['edit_mode'],
                'group_by' => function (Storagecard $storagecard) {
                    return $storagecard->getIdShelvingunit()->getLocalName();
                },
                'query_builder' => function($repository) use ($sourceSite) {
                    $qb = $repository->createQueryBuilder('sc')
                        ->innerJoin('sc.idShelvingunit', 'sh')
                        ->innerJoin('sh.idCupboard', 'c')
                        ->innerJoin('c.idStock', 'st')
                        ->where('sc.isarchived = false')
                        ->andWhere('sc.stockquantity > 0');

                    // Si un site source est spécifié, filtrer les produits pour ce site
                    if ($sourceSite) {
                        $qb->andWhere('st.idSite = :sourceSite')
                            ->setParameter('sourceSite', $sourceSite);
                    }

                    return $qb;
                }
            ])
            ->add('requestedQuantity', NumberType::class, [
                'label' => 'Quantité demandée',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer une quantité']),
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'La quantité doit être supérieure à 0'
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => 'any'
                ],
                'disabled' => $options['edit_mode']
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Justification de la demande',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez fournir une justification']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Expliquez pourquoi vous avez besoin de ce produit et ne pouvez pas l\'obtenir autrement.'
                ],
                'disabled' => $options['edit_mode']
            ])
            ->add('isUrgent', CheckboxType::class, [
                'label' => 'Demande urgente',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'disabled' => $options['edit_mode']
            ]);

        // Si on est en mode réponse (pour les responsables), ajouter le champ commentaire
        if ($options['response_mode']) {
            $builder->add('responseComment', TextareaType::class, [
                'label' => 'Commentaire de réponse',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Commentaire optionnel pour expliquer votre décision.'
                ]
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExternalWithdrawalRequest::class,
            'edit_mode' => false,
            'response_mode' => false,
            'source_site_id' => null,
            'entity_manager' => null,
        ]);
    }
}