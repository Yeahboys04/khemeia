<?php

namespace App\Form;

use App\Entity\Cupboard;
use App\Entity\Stock;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CupboardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    
        $builder
            ->add('nameCupboard')
            ->add('idStock', EntityType::class, 
            ['class' => Stock::class,
            'query_builder' => function (EntityRepository $er) use ($options) {
                return $er->createQueryBuilder('s')
                        ->where('s.idStock = :idStock' )
                        ->setParameter('idStock', $options['idStock'])
                        ;},
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cupboard::class,
            'idStock' => '1'
        ]);

        $resolver->setAllowedTypes('idStock', 'string');
    }
}
