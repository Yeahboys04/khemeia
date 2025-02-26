<?php

namespace App\Form;

use App\Entity\Dangersymbol;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\File as FileFile;
use Symfony\Component\Validator\Constraints\File;

class DangersymbolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nameDangersymbol')
            ->add('descriptionDangersymbol')
            ->add('uploadedFile', FileType::class, [
                'label' => 'Icone du symbole de danger',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Merci d\'importer un fichier JPG, PNG ou GIF valide.',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Dangersymbol::class,
        ]);
    }
}
