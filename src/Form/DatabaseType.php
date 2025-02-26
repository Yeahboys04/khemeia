<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DatabaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('uploadedDatabaseFile', FileType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' =>'application/sql',
                        'mimeTypesMessage' => 'Merci d\'importer un fichier SQL valide.',
                    ])
                ],
            ])
        ;
    }

}
