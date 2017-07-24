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

        if ($user->status === 'waiting_documents' && $user->payment_status === 'paid' && $user->getMedicalCertificateName())
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
    
    /**
     * @Route("/charge", name="stripe_charge")
     */
    public function chargeAction(Request $request)
    {
        $token = $request->get('stripeToken');

        if ($token === null)
            throw new \Exception('Null stripe token');
        
        $use_live_stripe = $this->getParameter('use_live_stripe');

        \Stripe\Stripe::setApiKey($this->getParameter($use_live_stripe ? 'stripe_live_token' : 'stripe_test_token'));
        $charge = \Stripe\Charge::create([
            'amount' => 0.5 * 100, // FIXME
            'currency' => 'eur',
            'source' => $token
        ]);

        $user = $this->getUser();
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

        if ($user->getMedicalCertificateName())
            $user->status = $user->skill_checked ? 'member' : 'waiting_skill_check';

        $user->payment_status = 'paid';
        
        $em = $this->get('doctrine')->getManager();

        $em->persist($user);
        $em->flush();

        $message = (new \Swift_Message('Les Trois Mousquetons - Paiement validé'))
                 ->setFrom('contact@troismousquetons.com')
                 ->setBcc($user->getEmail())
                 ->setBody(
                     $this->renderView('email/charge_succeeded.txt.twig'),
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
