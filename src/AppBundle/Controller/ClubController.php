<?php

namespace AppBundle\Controller;

use Exception;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        if ($this->getUser())
            return $this->redirectToRoute('homepage');
            
        $em = $this->get('doctrine')->getManager();
        $users = $em->getRepository(User::class)->findAll();
        $repartition = [
            'new' => 0,
            'in_lottery' => 0,
            'waiting_for_documents' => 0,
            'waiting_skill_check' => 0,
            'member' => 0,
            'in_waiting_list' => 0,
        ];

        foreach ($users as $u)
            $repartition[$u->status] += 1;
       
        return $this->render('club/status.html.twig', [
             'club' => $club,
             'repartition' => $repartition,
        ]);
    }

    /**
     * @Route("/{id}/admin", name="admin_panel")
     */
    public function adminPanelAction(Club $club)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You need to be an admin to do this!');

        $em = $this->get('doctrine')->getManager();
        $users = $em->getRepository(User::class)->findBy([], [
            'id' => 'ASC',
        ]);

        $users_in_lottery = array_filter($users, function($user) {
            return $user->status === 'in_lottery';
        });

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'club' => $club,
            'users_in_lottery' => !!count($users_in_lottery),
            'lottery_ready' => $users_in_lottery && !count(array_filter($users_in_lottery, function($user) {
                return $user->temporary_lottery_status === null;
            })),
        ]);
    }

    /**
     * @Route("/{id}/set_skill_checked/{user_id}", name="set_skill_checked")
     * @ParamConverter("user", class="AppBundle:User", options={"id" = "user_id"})
     */
    public function setSkillCheckedAction(Club $club, User $user)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You need to be an admin to do this!');

        if ($user->skill_checked !== true) {
            $em = $this->get('doctrine')->getManager();
            $user->skill_checked = true;
            if ($user->status === 'waiting_skill_check')
                $user->status = 'member';
            $em->persist($user);
            $em->flush();
        }
        
        return $this->redirectToRoute('admin_panel', [
            'id' => $club->id,
        ]);
    }

    /**
     * @Route("/{id}/reset_user/{user_id}", name="reset_user")
     * @ParamConverter("user", class="AppBundle:User", options={"id" = "user_id"})
     */
    public function resetUserAction(Club $club, User $user)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You need to be an admin to do this!');

        $em = $this->get('doctrine')->getManager();
        $user->skill_checked = false;
        $user->status = 'new';
        $user->payment_status = null;
        $user->setMedicalCertificateName(null);
        $em->persist($user);
        $em->flush();

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
        $users = $em->getRepository(User::class)->findByStatus(['new', 'in_waiting_list']);

        $userEmails = array_map(function ($user) {
            return $user->getEmail();
        }, $users);
        
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
        
        $em->getRepository(Club::class)->openLottery();

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
        $users = $em->getRepository(User::class)->findByStatus(['member']);

        foreach($users as $user)
        {
            $user->status = 'waiting_for_documents';
            $user->payment_status = null;
            $user->last_year_medical_certificate = $user->getMedicalCertificateName();
            $user->setMedicalCertificateName(null);
            $em->persist($user);
        }

        $em->flush();

        
        $userEmails = array_map(function ($user) {
            return $user->getEmail();
        }, $users);
        
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
        $em->getRepository(Club::class)->closeLottery();

        $users = $em->getRepository(User::class)->findByStatus('in_lottery');

        if (count($users) === 0)
            throw new Exception('No users in lottery');
        
        $winners = array_rand($users, min(count($users), $club->maxWinners));

        // Necessary when there is only one user in the lottery
        if (!is_array($winners))
            $winners = [$winners];

        foreach($users as $i => $user)
        {
            if (in_array($i, $winners))
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
        $em->getRepository(Club::class)->closeLottery();

        $users = $em->getRepository(User::class)->findByStatus('in_lottery');

        $winnerEmails = [];
        $loserEmails = [];
        
        foreach($users as $user)
        {
            if ($user->temporary_lottery_status === 'selected')
            {
                $user->status = 'waiting_for_documents';
                $winnerEmails[] = $user->getEmail();
            }
            elseif ($user->temporary_lottery_status === 'not_selected')
            {
                $user->status = 'in_waiting_list';
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
        $users = $em->getRepository(User::class)->findAll();

        $formatedUsers = array_map(function ($user) {
            return [
                'last_name' => strtoupper($user->last_name),
                'firt_name' => strtolower($user->first_name),
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
                'licence_id' => $user->licence_id,
                'licence_type' => 'OMNI',
            ];
        }, $users);

        $out = fopen('php://output', 'w');
        array_map(function ($user) use ($out) {
            fputcsv($out, $user, ';');
        }, $formatedUsers);
        fclose($out);
        
        $response = new Response('');
        $response->headers->set('Content-Type', 'text/csv');

        return $response;
    }

}