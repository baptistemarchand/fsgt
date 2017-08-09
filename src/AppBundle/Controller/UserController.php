<?php
declare(strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Vich\UploaderBundle\Form\Type\VichFileType;

use AppBundle\Entity\User;

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
        $user = $this->getUser();

        if ($status === 'in_lottery')
        {
            if (!$user->basicInfoFilled())
                throw new \Exception('Missing some required fields in profile');
        }
        
        if ($status !== 'in_lottery' || !in_array($user->status, ['new', 'waiting_list', 'lottery_open']))
            $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You need to be an admin to do this!');
        
        $em = $this->get('doctrine')->getManager();


        $user->status = $status;
        $em->persist($user);
        $em->flush();
        
        return $this->redirectToRoute('homepage');
    }
    
    /**
     * @Route("/edit", name="edit_user")
     */
    public function editUserAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createFormBuilder($user)
              ->add('email', null, [
                  'label' => 'Email',
                  'required' => true,
              ])
              ->add('first_name', null, [
                  'label' => 'Prénom',
                  'required' => true,
              ])
              ->add('last_name', null, [
                  'label' => 'Nom',
                  'required' => true,
              ])
              ->add('birthday', null, [
                  'widget' => 'single_text',
                  'label' => 'Date de naissance',
                  'required' => true,
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
                  'required' => true,
              ])
              ->add('city', null, [
                  'label' => 'Ville',
                  'required' => true,
              ])
              ->add('zip_code', null, [
                  'label' => 'Code Postal',
                  'required' => true,
              ])
              ->add('phone_number', null, [
                  'label' => 'Téléphone',
              ])
              ->add('does_not_need_training', null, [
                  'label' => 'Je sais grimper et assurer en tête',
              ])
              ->add('medicalCertificateFile', VichFileType::class, [
                  'label' => 'Certificat Médical',
                  'required' => false,
                  'allow_delete' => false,
              ])
              ->add('has_discount', null, [
                  'label' => 'J\'ai une réduction (étudiant,chômeur,RSA...)',
                  'required' => false,
              ])
              ->add('discountDocumentFile', VichFileType::class, [
                  'label' => 'Justificatif de tarif réduit (seulement si réduction)',
                  'required' => false,
                  'allow_delete' => false,
              ])
              ->add('save', SubmitType::class, [
                  'label' => 'Enregistrer',
              ])
              ->getForm();

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $user = $form->getData();
            if ($user->basicInfoFilled())
                $user->basic_info_filled = true;

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('homepage');
        }
        
        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
