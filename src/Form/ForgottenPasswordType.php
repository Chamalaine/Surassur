<?php

namespace App\Form;

use App\Entity\Intermediaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;



class ForgottenPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    $builder
            ->add('email')
            ->add('Valider', SubmitType::class, array(
            'attr' => array(
            'class' => 'btn btn-primary'
                )));

    }

}