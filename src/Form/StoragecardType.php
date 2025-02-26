<?php

namespace App\Form;

use App\Entity\Storagecard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class StoragecardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [
            'Oui' => true,
            'Non' => false
        ];

        $builder
            ->add('stockquantity')
            ->add('capacity')
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
            ->add('idChimicalproduct')
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
            ->add('idProperty')
            ->add('idShelvingunit')
            ->add('idSupplier')
            ->add('reference')
            ->add('commentary')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Storagecard::class,
        ]);
    }
}
