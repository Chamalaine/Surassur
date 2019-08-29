<?php

namespace App\Form;

use App\Entity\Assure;
use App\Entity\Listing;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AssureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('dateNaissance')
            ->add('telephone')
            ->add('numero')
            ->add('libelle')
            ->add('ville')
            ->add('cp')
            ->add('complement')
            ->add('beneficiaire', SubmitType::class, ['label' => 'Ajouter Bénéficiaire'])
            ->add('assure', SubmitType::class, ['label' => 'Nouvel Assuré'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Assure::class,
        ]);
    }
}
