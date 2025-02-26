<?php

namespace App\Form;

use App\Entity\Cautionaryadvice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CautionaryadviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nameCautionaryadvice')
            ->add('sentencecautionaryadvice')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cautionaryadvice::class,
        ]);
    }
}
