<?php
declare(strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Vich\UploaderBundle\Form\Type\VichFileType;

use AppBundle\Entity\User;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(Request $request)
    {
        $user = $this->getUser();

        $repartition = $user->main_club->getUserRepartition($this->get('state_machine.workflow'));

        if ($user->getState() === 'new')
        {
            $form = $this->getUserEditForm($user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $user = $form->getData();
                $workflow = $this->get('state_machine.workflow');
                $workflow->apply($user, 'fill_profile');
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            }
            else
                return $this->render('default/index.html.twig', [
                    'profile_form' => $form->createView(),
                    'user' => $user,
                    'places' => array_keys($repartition),
                ]);
        }

        if ($user->getState() === 'waiting_certificate')
        {
            $form = $this->getCertificateForm($user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $user = $form->getData();

                $workflow = $this->get('state_machine.workflow');
                $workflow->apply($user, 'upload_certificate');

                if (!$user->has_discount)
                    $workflow->apply($user, 'upload_discount_document');

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            }
            else
                return $this->render('default/index.html.twig', [
                    'certificate_form' => $form->createView(),
                    'user' => $user,
                    'places' => array_keys($repartition),
                ]);
        }

        if ($user->getState() === 'waiting_discount_document')
        {
            $form = $this->getDiscountDocumentForm($user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $user = $form->getData();
                $workflow = $this->get('state_machine.workflow');
                $workflow->apply($user, 'upload_discount_document');

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            }
            else
                return $this->render('default/index.html.twig', [
                    'discount_form' => $form->createView(),
                    'user' => $user,
                    'places' => array_keys($repartition),
                ]);
        }

        return $this->render('default/index.html.twig', [
            'user' => $user,
            //            'repartition' => $repartition,
            //            'errors' => $request->get('errors'),
            'places' => array_keys($repartition),
        ]);
    }

    protected function getCertificateForm(User $user)
    {
        return $this->createFormBuilder($user)
            ->add('medicalCertificateFile', VichFileType::class, [
                'label' => 'Certificat Médical',
                'required' => true,
                'allow_delete' => false,
            ])->getForm();
    }

    protected function getDiscountDocumentForm(User $user)
    {
        return $this->createFormBuilder($user)
            ->add('discountDocumentFile', VichFileType::class, [
                'label' => 'Justificatif de tarif réduit',
                'required' => true,
                'allow_delete' => false,
            ])->getForm();
    }

    protected function getUserEditForm(User $user)
    {
        return $this->createFormBuilder($user)
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
            ->add('has_discount', null, [
                'label' => 'J\'ai une réduction (étudiant,chômeur,RSA...). Un justificatif sera demandé.',
                'required' => false,
            ])
            ->getForm();
    }

}
