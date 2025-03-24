<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncompatibilityRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reason', TextareaType::class, [
                'label' => 'Raison de la demande de dérogation',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Veuillez expliquer pourquoi vous avez besoin de stocker ces produits incompatibles au même emplacement',
                    'rows' => 5
                ]
            ])
            ->add('urgencyLevel', CheckboxType::class, [
                'label' => 'Demande urgente (nécessite une réponse dans les 24h)',
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Soumettre la demande'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}