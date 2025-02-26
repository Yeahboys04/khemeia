<?php

namespace App\Form;

use App\Entity\Chimicalproduct;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChimicalproductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [
            'Oui' => true,
            'Non' => false
        ];

        $builder
            ->add('nameChimicalproduct')
            ->add('solvent')
            ->add('formula')
            ->add('casnumber')
            ->add('iscmr', ChoiceType::class, array(
                'choices'  => $choices,
                'choice_attr' => function($choice, $value) 
                    {return ['class' => 'radio-button'];},
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('idCautionaryadvice')
            ->add('idDangernote')
            ->add('idDangersymbol')
            ->add('idType')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Chimicalproduct::class,
        ]);
    }
}
