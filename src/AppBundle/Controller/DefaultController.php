<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\User;
use AppBundle\Entity\Club;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        if ($user->status === 'waiting_for_documents' && $user->paidAndUploaded())
        {
            $user->status = $user->skill_checked ? 'member' : 'waiting_skill_check';
            $em = $this->get('doctrine')->getManager();
            $em->persist($user);
            $em->flush();
        }

        if ($user->basic_info_filled !== $user->basicInfoFilled())
        {
            $em = $this->get('doctrine')->getManager();
            $user->basic_info_filled = $user->basicInfoFilled();
            $em->persist($user);
            $em->flush();
        }

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
        
        return $this->render('default/index.html.twig', [
            'user' => $user,
            'club' => $this->get('doctrine')->getRepository(Club::class)->find(1), // 1 == 3MQ
            'repartition' => $repartition,
        ]);
    }
    
}
