<?php
declare(strict_types=1);

namespace AppBundle\Controller;

use Exception;
use DateTime;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Doctrine\Common\Collections\Criteria;

use AppBundle\Entity\Club;
use AppBundle\Entity\User;

/**
 * @Route("/club")
 */
class ClubController extends Controller
{
    /**
     * @Route("/{id}/status", name="club_status")
     */
    public function statusAction(Club $club)
    {
        return $this->render('club/status.html.twig', [
             'club' => $club,
             'repartition' => $club->getUserRepartition($this->get('state_machine.workflow')),
        ]);
    }

    /**
     * @Route("/{id}/admin", name="admin_panel")
     */
    public function adminPanelAction(Club $club)
    {
        $this->denyAccessUnlessGranted('ROLE_BUREAU', null, 'Vous devez être membre du bureau');

        $em = $this->get('doctrine')->getManager();

        $inLotteryCriteria = Criteria::create()->where(Criteria::expr()->eq('marking', 'in_lottery'));
        $users_in_lottery = $club->users->matching($inLotteryCriteria);

        $noLotteryStatusCriteria = Criteria::create()->where(Criteria::expr()->eq('temporary_lottery_status', null));
        $users_without_lottery_status = $users_in_lottery->matching($noLotteryStatusCriteria);

        return $this->render('admin/index.html.twig', [
            'club' => $club,
            'users_in_lottery' => count($users_in_lottery),
            'lottery_ready' => count($users_in_lottery) && !count($users_without_lottery_status),
        ]);
    }

    /**
     * @Route("/{id}/set_skill_checked/{user_id}", name="set_skill_checked")
     * @ParamConverter("user", class="AppBundle:User", options={"id" = "user_id"})
     */
    public function setSkillChecked(Club $club, User $user)
    {
        $this->denyAccessUnlessGranted('ROLE_BUREAU', null, 'Vous devez être membre du bureau');

        if ($user->skill_checked !== true) {
            $user->skill_checked = true;

            $workflow = $this->get('state_machine.workflow');

            if ($workflow->can($user, 'get_validated'))
                $workflow->apply($user, 'get_validated');

            $em = $this->get('doctrine')->getManager();
            $em->persist($user);
            $em->flush();
        }

        return $this->redirectToRoute('admin_panel', [
            'id' => $club->id,
        ]);
    }

    /**
     * @Route("/{id}/resend_email/{user_id}", name="resend_email")
     * @ParamConverter("user", class="AppBundle:User", options={"id" = "user_id"})
     */
    public function resendEmail(Club $club, User $user)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You need to be an admin to do this!');

        $mailer = $this->get('fos_user.mailer');
        $res = $mailer->sendConfirmationEmailMessage($user);

        return $this->redirectToRoute('admin_panel', [
            'id' => $club->id,
        ]);
    }

    /**
     * @Route("/{id}/open_lottery", name="open_lottery")
     */
    public function openLotteryAction(Club $club)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You need to be an admin to do this!');

        $em = $this->get('doctrine')->getManager();
        $users = $club->getUsersByState([
            'new',
            'in_waiting_list',
        ]);

        $userEmails = $users->map(function($user) {
            return $user->getEmail();
        })->getValues();

        $message = (new \Swift_Message('Ouverture des inscriptions 2018 !'))
                 ->setFrom('contact@troismousquetons.com')
                 ->setBcc($userEmails)
                 ->setBody(
                     $this->renderView('email/lottery_open.txt.twig')
                 );

        if (!$this->get('mailer')->send($message, $failures))
            $this->get('logger')->critical('Some emails failed', [
                'failures' => $failures,
            ]);

        $club->status = 'lottery_open';
        $em->persist($club);
        $em->flush();

        return $this->redirectToRoute('admin_panel', [
            'id' => $club->id,
        ]);
    }

    /**
     * @Route("/{id}/re_registration", name="re_registration")
     */
    public function reRegistrationAction(Club $club)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You need to be an admin to do this!');

        $em = $this->get('doctrine')->getManager();
        $users = $club->getUsersByState(['member']);

        foreach($users as $user)
        {
            $workflow = $this->get('state_machine.workflow');
            $workflow->apply($user, 're_subscribe');

            $user->payment_status = null;
            $user->last_year_medical_certificate = $user->getMedicalCertificateName();
            $user->setMedicalCertificateName(null);
            $em->persist($user);
        }

        $em->flush();

        $userEmails = $users->map(function($user) {
            return $user->getEmail();
        })->getValues();

        $message = (new \Swift_Message('Les Trois Mousquetons - Ré-inscriptions'))
                 ->setFrom('contact@troismousquetons.com')
                 ->setBcc($userEmails)
                 ->setBody($this->renderView('email/re_registration.txt.twig'));

        if ($userEmails)
            $this->get('mailer')->send($message);

        return $this->redirectToRoute('admin_panel', [
            'id' => $club->id,
        ]);
    }

    /**
     * @Route("/{id}/test_lottery", name="test_lottery")
     */
    public function testLotteryAction(Club $club)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You need to be an admin to do this!');

        $em = $this->get('doctrine')->getManager();
        $club->status = 'lottery_closed';
        $em->persist($club);
        $em->flush();

        $users = $club->getUsersByState(['in_lottery'])->toArray();

        if (count($users) === 0)
            throw new Exception('No users in lottery');

        $numberOfExperienced = (int)($club->maxWinners * $club->percentageOfExperienced);
        $numberOfBeginners = $club->maxWinners - $numberOfExperienced;

        $beginners = [];
        $experienced = [];

        foreach($users as $user)
        {
            if ($user->vip)
            {
                $user->temporary_lottery_status = 'selected';
                if ($user->does_not_need_training)
                    $numberOfExperienced -= 1;
                else
                    $numberOfBeginners -= 1;
            }
            else if ($user->does_not_need_training)
                $experienced[] = $user;
            else
                $beginners[] = $user;
        }

        if ($numberOfBeginners < 0)
            $numberOfBeginners = 0;
        if ($numberOfExperienced < 0)
            $numberOfExperienced = 0;
        //        var_dump($numberOfExperienced, $numberOfBeginners);die;
        $beginnerWinners = $numberOfBeginners === 0 ? [] : array_rand($beginners, min(count($beginners), $numberOfBeginners));
        $experiencedWinners = $numberOfExperienced === 0 ? [] : array_rand($experienced, min(count($experienced), $numberOfExperienced));

        // when there is only one result, array_rand does not return an array
        if (!is_array($beginnerWinners))
            $beginnerWinners = [$beginnerWinners];
        if (!is_array($experiencedWinners))
            $experiencedWinners = [$experiencedWinners];

        foreach($beginners as $i => $user)
        {
            if (in_array($i, $beginnerWinners))
                $user->temporary_lottery_status = 'selected';
            else
                $user->temporary_lottery_status = 'not_selected';
            $em->persist($user);
        }
        foreach($experienced as $i => $user)
        {
            if (in_array($i, $experiencedWinners))
                $user->temporary_lottery_status = 'selected';
            else
                $user->temporary_lottery_status = 'not_selected';
            $em->persist($user);
        }

        $em->flush();

        return $this->redirectToRoute('admin_panel', [
            'id' => $club->id,
        ]);
    }

    /**
     * @Route("/{id}/finish_lottery", name="finish_lottery")
     */
    public function finishLotteryAction(Club $club)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You need to be an admin to do this!');

        $em = $this->get('doctrine')->getManager();
        $club->status = 'lottery_closed';
        $em->persist($club);
        $em->flush();

        $users = $club->getUsersByState(['in_lottery']);

        $winnerEmails = [];
        $loserEmails = [];

        $workflow = $this->get('state_machine.workflow');

        foreach($users as $user)
        {
            if ($user->temporary_lottery_status === 'selected')
            {
                $workflow->apply($user, 'win_lottery');
                $winnerEmails[] = $user->getEmail();
            }
            elseif ($user->temporary_lottery_status === 'not_selected')
            {
                $workflow->apply($user, 'lose_lottery');
                $loserEmails[] = $user->getEmail();
            }
            else
                throw new Exception('User in lottery without temporary_lottery_satus set');

            $user->temporary_lottery_status = null;
            $em->persist($user);
        }

        $em->flush();

        $winnerMessage = (new \Swift_Message('Résultats du tirage au sort'))
                 ->setFrom('contact@troismousquetons.com')
                 ->setBcc($winnerEmails)
                 ->setBody(
                     $this->renderView('email/lottery_winners.txt.twig')
                 );
        $loserMessage = (new \Swift_Message('Résultats du tirage au sort'))
                 ->setFrom('contact@troismousquetons.com')
                 ->setBcc($loserEmails)
                 ->setBody(
                     $this->renderView('email/lottery_losers.txt.twig')
                 );

        if ($winnerEmails)
            $this->get('mailer')->send($winnerMessage);

        if ($loserEmails)
            $this->get('mailer')->send($loserMessage);

        return $this->redirectToRoute('admin_panel', [
            'id' => $club->id,
        ]);
    }

    /**
     * @Route("/{id}/export", name="club_export")
     */
    public function export(Club $club)
    {
        $em = $this->get('doctrine')->getManager();
        $users = $club->getUsersNeedingLicenses()->toArray();

        $formatedUsers = array_map(function ($user) {
            return [
                'last_name' => strtoupper($user->last_name ?: ''),
                'first_name' => strtolower($user->first_name ?: ''),
                'birthday' => $user->birthday ? $user->birthday->format('d/m/y') : '',
                'gender'    => $user->gender == 'male' ? 'M' : 'F',
                'address'   => $user->address,
                'address2'  => '',
                'address3'  => '',
                'zip_code'  => $user->zip_code,
                'city'      => $user->city,
                'insurance' => 'Oui',
                'home_phone_number' => '',
                'pro_phone_number' => '',
                'phone_number' => $user->phone_number,
                'email' => $user->getEmail(),
                'license_id' => $user->license_id,
                'license_type' => 'OMNI',
            ];
        }, $users);

        $out = fopen('php://output', 'w');
        array_map(function ($user) use ($out) {
            fputcsv($out, $user, ';');
        }, $formatedUsers);
        fclose($out);

        $response = new Response('');
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }

    /**
     * @Route("/{id}/import", name="club_import")
     */
    public function import(Request $request, Club $club)
    {
        $form = $this->createFormBuilder()
              ->add('submitFile', FileType::class, [
                  'label' => 'Fichier au format FSGT'
              ])
              ->getForm();

        $form->handleRequest($request);

        $keys = [
            'last_name',
            'first_name',
            'birthday',
            'gender',
            'address',
            'address2',
            'address3',
            'zip_code',
            'city',
            'insurance',
            'home_phone_number',
            'pro_phone_number',
            'phone_number',
            'email',
            'license_id',
            'license_type',
        ];

        if ($form->isSubmitted() && $form->isValid())
        {
            $file = $form->get('submitFile');
            $data = $file->getData();
            $users = array_map(function($line) {
                return str_getcsv($line, ';');
            }, file($data->getPathname()));

            $em = $this->get('doctrine')->getManager();
            foreach ($users as $user)
            {
                $user = array_combine($keys, $user);
                if ($em->getRepository(User::class)->findOneByEmail($user['email']))
                    continue;

                $u = new User();
                $u->setEmail($user['email']);
                $u->first_name = $user['first_name'];
                $u->last_name = $user['last_name'];
                $u->birthday = DateTime::createFromFormat('d/m/Y', $user['birthday']) ?: null;
                $u->gender = $user['gender'];
                $u->address = $user['address'];
                $u->first_name = $user['first_name'];
                $u->zip_code = $user['zip_code'];
                $u->city = $user['city'];
                $u->license_id = $user['license_id'];
                $u->phone_number = $user['phone_number'];
                $u->main_club = $club;

                $u->setPassword(base64_encode(random_bytes(16)));

                $em->persist($u);
            }
            $em->flush();
            $em->clear();

            return $this->redirectToRoute('admin_panel', [
                'id' => $club->id,
            ]);

        }

        return $this->render(
            'club/import.html.twig',
            [
                'form' => $form->createView(),
                'club' => $club,
            ]
        );
    }

}
