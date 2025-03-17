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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

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
            ->add('username', TextType::class, [
                'label' => 'Login',
                'attr' => [
                    'class' => 'form-control rounded-lg',
                    'placeholder' => 'Entrez le login',
                ],
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ]
            ])
            ->add('fullname', TextType::class, [
                'label' => 'Nom et Prénom',
                'attr' => [
                    'class' => 'form-control rounded-lg',
                    'placeholder' => 'Entrez le nom complet',
                ],
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ]
            ])
            ->add('mail', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control rounded-lg',
                    'placeholder' => 'exemple@domaine.fr',
                ],
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => $options['requirePassword'],
                'mapped' => false, // Ne pas mapper directement à l'entité
                'attr' => [
                    'class' => 'form-control rounded-lg',
                    'placeholder' => $options['requirePassword'] ? 'Entrez le mot de passe' : 'Laissez vide pour conserver',
                    'autocomplete' => 'new-password'
                ],
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ]
            ])
            ->add('endrightdate', DateType::class, [
                'label' => 'Date de fin de droit',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control datepicker rounded-lg',
                    'data-placement' => 'right',
                    'data-format' => 'dd/mm/yyyy',
                    'data-language' => 'fr',
                    'placeholder' => 'JJ/MM/AAAA'
                ],
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ]
            ])
            ->add('idStatus', EntityType::class, [
                'class' => Status::class,
                'label' => 'Statut',
                'attr' => [
                    'class' => 'form-select rounded-lg'
                ],
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ]
            ])
            ->add('idSite', EntityType::class, [
                'class' => Site::class,
                'label' => 'Site',
                'attr' => [
                    'class' => 'form-select rounded-lg'
                ],
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ]
            ])
        ;

        // Ajouter l'option isArchived seulement si elle est demandée
        if ($options['showArchiveOption']) {
            $builder->add('isArchived', CheckboxType::class, [
                'label' => 'Archivé',
                'required' => false,
                'mapped' => false, // Ne pas mapper ce champ à l'entité car on utilise les méthodes dédiées
                'data' => $options['isArchived'], // Utiliser la valeur passée
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'label_attr' => [
                    'class' => 'form-check-label ms-1'
                ]
            ]);
        }
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
            'showArchiveOption' => false,
            'isArchived' => false,
        ]);

        $resolver->setAllowedTypes('requirePassword', 'bool');
        $resolver->setAllowedTypes('emptyPassword', 'string');
        $resolver->setAllowedTypes('showArchiveOption', 'bool');
        $resolver->setAllowedTypes('isArchived', 'bool');
    }
}