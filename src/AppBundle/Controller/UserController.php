<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/status/{status}", name="change_status")
     */
    public function changeStatusAction(Request $request, string $status)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You need to be an admin to do this!');
        
        $em = $this->get('doctrine')->getManager();
        $user = $this->getUser();
        $user->status = $status;
        $em->persist($user);
        $em->flush();
        
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/edit", name="edit_user")
     */
    public function editUserAction()
    {
        $user = $this->getUser();

        $form = $this->createFormBuilder($user)
              ->add('email', null, [
                  'label' => 'Email',
              ])
              ->add('first_name', null, [
                  'label' => 'Prénom',
              ])
              ->add('last_name', null, [
                  'label' => 'Nom',
              ])
              ->add('birthday', null, [
                  'widget' => 'single_text',
                  'label' => 'Date de naissance',
              ])
              ->add('gender', ChoiceType::class, [
                  'label' => 'Sexe',
                  'choices' => [
                      'Homme' => 'male',
                      'Femme' => 'female',
                  ],
              ])
              ->add('address', null, [
                  'label' => 'Adresse',
              ])
              ->add('zip_code', null, [
                  'label' => 'Code Postal',
              ])
              ->add('phone_number', null, [
                  'label' => 'Téléphone',
              ])
              ->add('does_not_need_training', null, [
                  'label' => 'Je sais grimper et assurer en tête',
              ])
              ->add('save', SubmitType::class, [
                  'label' => 'Enregistrer',
              ])
              ->getForm();

        return $this->render('user/edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
