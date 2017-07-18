<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\User;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        
        return $this->render('default/index.html.twig', [
            'user' => $user,
        ]);
    }
    
    /**
     * @Route("/charge", name="charge")
     */
    public function chargeAction(Request $request)
    {
        $token = $request->get('stripeToken');

        if ($token === null)
            throw new \Exception('Null stripe token');
        
        \Stripe\Stripe::setApiKey($this->getParameter('stripe_test_token'));
        $charge = \Stripe\Charge::create([
            'amount' => 80 * 100,
            'currency' => 'eur',
            'source' => $token
        ]);

        $user = $this->getUser();
        $em = $this->get('doctrine')->getManager();
        $user->stripe_charge_id = $charge['id'];
        $user->status = 'waiting_payment_validation';
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }
    
    /**
     * @Route("/stripe", name="stripe")
     */
    public function stripeAction(Request $request)
    {
        \Stripe\Stripe::setApiKey($this->getParameter('stripe_test_token'));
        $endpoint_secret = $this->getParameter('stripe_endpoint_secret');

        $payload = $request->getContent();
        $sig_header = $request->headers->get("stripe_signature");
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            die('lol');
            http_response_code(400); // PHP 5.4 or greater
            exit();
        } catch(\Stripe\Error\SignatureVerification $e) {
                        die($e);
            // Invalid signature
            http_response_code(400); // PHP 5.4 or greater
            exit();
        }

        if ($event['type'] !== 'charge.succeeded')
            die('wrong event');
        
        $charge_id = $event['data']['object']['id'];

        $repo = $this->get('doctrine')->getRepository(User::class);

        $user = $repo->findOneBy([
            'stripe_charge_id' => $charge_id, 
        ]);

        if ($user === null)
            die('No user found with this charge id.');
        
        $user->status = 'waiting_skill_check';

        $em = $this->get('doctrine')->getManager();

        $em->persist($user);
        $em->flush();
        
        return $this->json([
            'success' => true,
        ]);
    }

}
