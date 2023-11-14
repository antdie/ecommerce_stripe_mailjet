<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Carrier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', EntityType::class, [
                'label' => 'Pick a delivery address',
                'help' => '<a href="'.$options['address_add_url'].'">Add a new address</a>',
                'help_html' => true,
                'class' => Address::class,
                'choices' => $options['user']->getAddresses(),
                'label_html' => true,
                'choice_label' => function ($address) {
                    return $address->getName().'<br>'.$address->getAddress().'<br>'.$address->getCity(). ' - '.$address->getCountry();
                },
                'multiple' => false,
                'expanded' => true
            ])
            ->add('carrier', EntityType::class, [
                'label' => 'Pick a carrier',
                'class' => Carrier::class,
                'label_html' => true,
                'choice_label' => function ($carrier) {
                    return $carrier->getName().'<br>'.$carrier->getDescription().'<br>€'.$carrier->getPrice() / 100;
                },
                'choice_attr' => function($carrier) {
                    // adds html elements to inputs
                    return [
                        'onchange' => 'refresh(this)',
                        'data-price' => $carrier->getPrice(),
                    ];
                },
                'multiple' => false,
                'expanded' => true
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Pay'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'user' => [],
            'address_add_url' => ''
        ]);
    }
}
