<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
     * @Route("/open_lottery", name="open_lottery")
     */
    public function openLotteryAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You need to be an admin to do this!');
        
        $em = $this->get('doctrine')->getManager();
        $users = $em->getRepository(User::class)->findAll();

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
        
        $this->get('mailer')->send($message);

        
        return $this->redirectToRoute('admin_panel');
    }

}