<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Address name',
                'attr' => [
                    'placeholder' => 'Address name'
                ]
            ])
            ->add('firstname', null, [
                'label' => 'Firstname',
                'attr' => [
                    'placeholder' => 'Firstname'
                ]
            ])
            ->add('lastname', null, [
                'label' => 'Lastname',
                'attr' => [
                    'placeholder' => 'Lastname'
                ]
            ])
            ->add('company', null, [
                'label' => 'Company',
                'attr' => [
                    'placeholder' => 'Company'
                ]
            ])
            ->add('address', null, [
                'label' => 'Address',
                'attr' => [
                    'placeholder' => 'Address'
                ]
            ])
            ->add('code', null, [
                'label' => 'ZIP/Postal code',
                'attr' => [
                    'placeholder' => 'ZIP/Postal code'
                ]
            ])
            ->add('city', null, [
                'label' => 'City',
                'attr' => [
                    'placeholder' => 'City'
                ]
            ])
            ->add('country', CountryType::class, [
                'label' => 'Country'
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone',
                'attr' => [
                    'placeholder' => 'Phone'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Confirm'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
