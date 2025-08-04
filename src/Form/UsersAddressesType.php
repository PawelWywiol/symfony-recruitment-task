<?php

namespace App\Form;

use App\Entity\Users;
use App\Entity\UsersAddresses;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsersAddressesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('addressType')
            ->add('validFrom')
            ->add('postCode')
            ->add('city')
            ->add('countryCode')
            ->add('street')
            ->add('buildingNumber')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('user', EntityType::class, [
                'class' => Users::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UsersAddresses::class,
        ]);
    }
}
