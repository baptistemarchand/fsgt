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

use Symfony\Component\Workflow\Exception\LogicException;
use AppBundle\Exception\UserException;
use AppBundle\Exception\WorkflowException;
use Exception;

use AppBundle\Entity\User;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/state/{state}", name="change_state")
     */
    public function changeState(string $state)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You need to be an admin to do this!');

        $user = $this->getUser();
        $user->setState($state);

        $em = $this->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/request_lottery_participation", name="request_lottery_participation")
     */
    public function requestLotteryParticipation()
    {
        $user = $this->getUser();

        if (!$user->basicInfoFilled())
            throw new UserException('Pour participer au tirage au sort il faut avoir remplis son profil.');

        $workflow = $this->get('state_machine.workflow');
        try {
            $workflow->apply($user, 'enter_lottery');
        } catch (LogicException $e) {
            throw new UserException('Impossible de participer au tirage au sort');
        }

        $em = $this->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/edit", name="edit_user")
     */
    public function editUser(Request $request)
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
                  'label' => 'J\'ai une réduction (étudiant,chômeur,RSA...). Un justificatif sera demandé.',
                  'required' => false,
              ])
              ->add('discountDocumentFile', VichFileType::class, [
                  'label' => 'Justificatif de tarif réduit (seulement si réduction)',
                  'required' => false,
                  'allow_delete' => false,
              ])
              ->add('other_club_license_id', null, [
                  'label' => 'Si tu es membre d\'un autre club FSGT cette année, indique ton numéro de license (pour ne pas payer l\'assurance en double)',
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
