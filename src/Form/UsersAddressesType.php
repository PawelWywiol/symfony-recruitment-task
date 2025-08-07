<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Users;
use App\Entity\UsersAddresses;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsersAddressesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('addressType', ChoiceType::class, [
                'choices' => [
                    'Home' => 'HOME',
                    'Work' => 'WORK',
                    'Invoice' => 'INVOICE',
                    'Post' => 'POST',
                ],
                'label' => 'Address Type'
            ])
            ->add('validFrom', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Valid From'
            ])
            ->add('street', TextType::class, [
                'label' => 'Street'
            ])
            ->add('buildingNumber', TextType::class, [
                'label' => 'Building Number'
            ])
            ->add('postCode', TextType::class, [
                'label' => 'Post Code'
            ])
            ->add('city', TextType::class, [
                'label' => 'City'
            ])
            ->add('countryCode', TextType::class, [
                'label' => 'Country Code (ISO3166-1 alpha-3)'
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
