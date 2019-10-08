<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('telephone', TextType::class,[
                'constraints' => 
                    new Length([
                    'min' => 5,
                    'minMessage' => 'Votre téléphone doit faire au moins 5 caractères',
                    'max' => 20,
                ]),
                ])

            ->add('objet',  TextType::class,[
                'constraints' => 
                    new Length([
                    'min' => 5,
                    'minMessage' => 'Votre objet doit faire au moins 5 caractères',
                    'max' => 100,
                ]),
                ])

            ->add('message', TextType::class,[
            'constraints' => 
                new Length([
                'min' => 50,
                'minMessage' => 'Votre message doit faire au moins 50 caractères',
                'max' => 1800,
            ]),
            ])

            ->add('Envoyer', SubmitType::class, array(
                'attr' => array(
                'class' => 'btn btn-primary'
                    )));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
