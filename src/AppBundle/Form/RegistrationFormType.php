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
            ->add('first_name')
            ->add('last_name')
            ->add('gender', ChoiceType::class, [
                'label' => 'Sexe',
                'required' => false,
                'choices' => [
                    'Homme' => 'male',
                    'Femme' => 'female',
                ],
            ])
            ->add('birthday', BirthdayType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('address')
            ->add('city')
            ->add('zip_code')
            ->add('does_not_need_training')
        ;
    }
    public function getParent()
    {
        return BaseRegistrationFormType::class;
    }
}