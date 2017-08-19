<?php
declare(strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        if ($user->basic_info_filled !== $user->basicInfoFilled())
        {
            $user->basic_info_filled = $user->basicInfoFilled();

            $em = $this->get('doctrine')->getManager();
            $em->persist($user);
            $em->flush();
        }

        $repartition = $user->main_club->getUserRepartition($this->get('state_machine.workflow'));

        return $this->render('default/index.html.twig', [
            'user' => $user,
            'repartition' => $repartition,
            'errors' => $request->get('errors'),
            'places' => array_keys($repartition),
        ]);
    }

}
