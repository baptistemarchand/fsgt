<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\User;
use AppBundle\Entity\Club;

class StripeController extends Controller
{
    
    /**
     * @Route("/charge", name="stripe_charge")
     */
    public function chargeAction(Request $request)
    {
        $token = $request->get('stripeToken');

        if ($token === null)
            throw new \Exception('Null stripe token');
        
        $use_live_stripe = $this->getParameter('use_live_stripe');

        $user = $this->getUser();
        
        \Stripe\Stripe::setApiKey($this->getParameter($use_live_stripe ? 'stripe_live_token' : 'stripe_test_token'));
        $charge = \Stripe\Charge::create([
            'amount' => ($user->has_discount ? 60 : 80) * 100,
            'currency' => 'eur',
            'source' => $token
        ]);


        $em = $this->get('doctrine')->getManager();
        $user->stripe_charge_id = $charge['id'];
        $user->payment_status = 'processing';
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }
    
    /**
     * @Route("/stripe/{mode}", name="stripe")
     */
    public function stripeAction(Request $request, string $mode)
    {
        try {
        \Stripe\Stripe::setApiKey($this->getParameter('stripe_test_token'));
        $endpoint_secret = $this->getParameter($mode === 'live' ? 'stripe_endpoint_secret_live' : 'stripe_endpoint_secret_test');

        $payload = $request->getContent();
        $sig_header = $request->headers->get("stripe_signature");
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            throw new \Exception($e->getMessage());
        } catch(\Stripe\Error\SignatureVerification $e) {
            throw new \Exception($e->getMessage());
        }

        if ($event['type'] !== 'charge.succeeded')
            return $this->json([
                'message' => 'type not handled',
                'type' => $event['type'],
            ]);
        
        $charge_id = $event['data']['object']['id'];

        $repo = $this->get('doctrine')->getRepository(User::class);

        $user = $repo->findOneBy([
            'stripe_charge_id' => $charge_id, 
        ]);

        if ($user === null)
            throw new \Exception('No user found with this charge id.');

        $user->payment_status = 'paid';
        
        if ($user->paidAndUploaded())
            $user->status = $user->skill_checked ? 'member' : 'waiting_skill_check';

        $em = $this->get('doctrine')->getManager();

        $em->persist($user);
        $em->flush();

        $message = (new \Swift_Message('Les Trois Mousquetons - Paiement validé'))
                 ->setFrom('contact@troismousquetons.com')
                 ->setBcc($user->getEmail())
                 ->setBody(
                     $this->renderView('email/charge_succeeded.txt.twig')
                 );
        
        $this->get('mailer')->send($message);

        
        return $this->json([
            'success' => true,
        ]);
        } catch (\Exception $e){
            return $this->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
