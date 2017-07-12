<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
;

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
}
