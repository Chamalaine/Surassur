<?php

namespace App\Form;

use App\Entity\Intermediaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ChangePasswordType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options)
 {
$builder
->add('oldPassword', PasswordType::class, array(
'mapped' => false
))
 ->add('Password', RepeatedType::class, array(
'type' => PasswordType::class,

))

 ->add('submit', SubmitType::class, array(
'attr' => array(
'class' => 'btn btn-primary btn-block'
)
 ))
;

}

public function configureOptions(OptionsResolver $resolver)
 {
$resolver->setDefaults([
'data_class' => Intermediaire::class,
]);
}
}