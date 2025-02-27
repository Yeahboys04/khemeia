<?php

namespace App\Form;

use App\Entity\Shelvingunit;
use App\Entity\Storagecard;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class QuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('retiredquantity', IntegerType::class, [
                'attr'=> [
                    'min' => 0,
                    'max' => $options['stockquantity']
                ],
                'mapped' => false,
                'required' => false
            ]);
        $builder
        ->add('ismoved', ChoiceType::class, array(
            'choices'  => [
                            'Oui' => true,
                            'Non' => false],
            'choice_attr' => function($choice, $value) 
                {return ['class' => 'radio-button'];},
            'expanded' => true,
            'multiple' => false,
            'mapped' => false
        ))
        ->add('isopened', ChoiceType::class, array(
                'choices'  => [
                    'Oui' => true,
                    'Non' => false],
                'choice_attr' => function($choice, $value)
                {return ['class' => 'radio-button'];},
                'expanded' => true,
                'multiple' => false,
                'mapped' => false
        ))
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
        ->add('idShelvingunit', EntityType::class, 
            ['class' => Shelvingunit::class,
            'query_builder' => function (EntityRepository $er) use ($options) {
                return $er->createQueryBuilder('qb')
                        ->innerJoin('App\Entity\Cupboard', 'c', 'WITH', 'c.idCupboard = qb.idCupboard')
                        ->innerJoin('App\Entity\Stock', 'st', 'WITH', 'c.idStock = st.idStock')
                        ->where('st.idSite = :querysite')
                        ->setParameter('querysite' , $options['idSite']);},
        ])
        ->add('agreeTerms', CheckboxType::class, [
            'mapped' => false,
            'constraints' => [
                new IsTrue([
                    'message' => 'I know, it\'s silly, but you must agree to our terms.'
                ])
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Storagecard::class,
            'idSite' => '1',
            'stockquantity' => null
        ]);
        $resolver->setAllowedTypes('idSite', 'string');
        // Correction : autoriser 'int' ou 'null' comme types pour stockquantity
        $resolver->setAllowedTypes('stockquantity', ['int', 'null']);
    }
}
