<?php

namespace App\Form;

use App\Entity\Cupboard;
use App\Entity\Shelvingunit;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShelvingunitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nameShelvingunit')
            ->add('idCupboard', EntityType::class, 
            ['class' => Cupboard::class,
            'query_builder' => function (EntityRepository $er) use ($options) {
                return $er->createQueryBuilder('s')
                        ->where('s.idCupboard = :idCupboard' )
                        ->setParameter('idCupboard', $options['idCupboard'])
                        ;},
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Shelvingunit::class,
            'idCupboard' => '1'
        ]);
        $resolver->setAllowedTypes('idCupboard', 'string');
    }
}
