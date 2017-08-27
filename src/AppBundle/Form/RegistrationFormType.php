<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('username')
            ->add('first_name', null, [
                'required' => true,
            ])
            ->add('main_club', null, [
                'label' => 'Club',
                'required' => true,
                'disabled' => true,
                'preferred_choices' => function ($val, $key) {
                    return $val->name === 'Les Trois Mousquetons';
                },
            ])
        ;
    }

    public function getParent()
    {
        return BaseRegistrationFormType::class;
    }
}