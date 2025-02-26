<?php

namespace App\Form;

use App\Entity\Status;
use App\Entity\User;
use App\Entity\Site;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Class UserType qui paramètre un formulaire d'utilisateur
 * @package App\Form
 */
class UserType extends AbstractType
{
    /**
     * Construit un formulaire utilisateur avec les paramètres de base
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, 
                ['label' => 'Login'])
            ->add('fullname', TextType::class, 
                ['label' => 'Nom et Prénom'])
            ->add('mail', EmailType::class, 
                ['label' => 'Email'])
            ->add('password', PasswordType::class, 
                ['label' => 'Mot de passe',
                'empty_data' => $options['emptyPassword'],
                'required' => $options['requirePassword'],])
            ->add('endrightdate', DateType::class,
                ['label' => 'Date de fin de droit',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'required'   => false])
            ->add('idStatus', EntityType::class, 
                ['class' => Status::class,
                'label' => 'Statut'])
            ->add('idSite', EntityType::class, 
                ['class' => Site::class,
                'label' => 'Site'])
        ;
    }

    /**
     * Associe le formulaire à l'entité utilisateur
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'requirePassword' => true,
            'emptyPassword' => '',
            ]);
        
        $resolver->setAllowedTypes('requirePassword', 'bool');
        $resolver->setAllowedTypes('emptyPassword', 'string');
    }
}
