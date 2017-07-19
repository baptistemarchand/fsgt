<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\Club;
use AppBundle\Entity\User;

/**
 * @Route("/club")
 */
class ClubController extends Controller
{
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

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'club' => $club,
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
                     $this->renderView('email/lottery_open.html.twig'),
                     'text/html'
                 );

        if (false) // FIXME
            $this->get('mailer')->send($message);

        $em->getRepository(Club::class)->openLottery();

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
            throw new \Exception('No users in lottery');
        
        $maxWinners = 4;
        $winners = array_rand($users, min(count($users), $maxWinners));

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

        foreach($users as $user)
        {
            if ($user->temporary_lottery_status === 'selected')
                $user->status = 'waiting_for_documents';
            else
                $user->status = 'in_waiting_list';
            
            $user->temporary_lottery_status = null;
            $em->persist($user);
        }

        $em->flush();

        return $this->redirectToRoute('admin_panel', [
            'id' => $club->id,
        ]);
    }

}