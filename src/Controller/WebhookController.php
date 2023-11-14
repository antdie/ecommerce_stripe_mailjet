<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\CartManager;
use App\Service\MailjetManager;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/webhook')]
class WebhookController extends AbstractController
{
    #[Route('/stripe', name: 'app_webhook_stripe')]
    public function index(EntityManagerInterface $entityManager, WorkflowInterface $orderStateMachine, MailjetManager $mailjetManager): Response
    {
        $response = new Response();

        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = $this->getParameter('app.stripe_endpoint');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(UnexpectedValueException $e) {
            // Invalid payload
            return $response->setStatusCode(400);
        } catch(SignatureVerificationException $e) {
            // Invalid signature
            return $response->setStatusCode(400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $checkoutSessionId = $event->data->object->id;

                $order = $entityManager->getRepository(Order::class)->findOneByCheckoutSessionId($checkoutSessionId);

                if (!$order) {
                    return $response->setStatusCode(400);
                }

                if ($orderStateMachine->can($order, 'to_paid')) {
                    $orderStateMachine->apply($order, 'to_paid');
                    $entityManager->flush();
                    $mailjetManager->send($order->getCustomer()->getEmail(), (string) $order->getCustomer(), 'Successfull order', 'Lorem ipsum dolor fs lghke a sqd nfgsk qdqs q fdqs sd.');
                }
                break;
            case 'checkout.session.expired':
                $checkoutSessionId = $event->data->object->id;

                $order = $entityManager->getRepository(Order::class)->findOneByCheckoutSessionId($checkoutSessionId);

                if (!$order) {
                    return $response->setStatusCode(400);
                }

                if ($orderStateMachine->can($order, 'to_expired')) {
                    $orderStateMachine->apply($order, 'to_expired');
                    $entityManager->flush();
                }
                break;
            // ... handle other event types
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        return $response->setStatusCode(200);
    }
}
