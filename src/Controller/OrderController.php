<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderFormType;
use App\Service\CartManager;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/order')]
class OrderController extends AbstractController
{
    #[Route('', name: 'app_order')]
    public function index(CartManager $cartManager, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $cart = $cartManager->get();
        if (!$cart) {
            return $this->redirectToRoute('app_cart');
        }
        if (!$user->getAddresses()) {
            return $this->redirectToRoute('app_account_address_add');
        }

        $form = $this->createForm(OrderFormType::class, null, [
            'user' => $user,
            'address_add_url' => $this->generateUrl('app_account_address_add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $carrier = $form->get('carrier')->getData();
            $address = $form->get('address')->getData();

            $address_formatted = $address->getLastname().' '.$address->getFirstname();
            if ($address->getCompany()) {
                $address_formatted .= '<br>'.$address->getCompany();
            }
            $address_formatted .= '<br>'.$address->getAddress();
            $address_formatted .= '<br>'.$address->getCode().' '.$address->getCity().' '.$address->getCountry();
            $address_formatted .= '<br>'.$address->getPhone();

            $order = new Order();
            $order->setCustomer($user);
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setCarrierName($carrier->getName());
            $order->setCarrierPrice($carrier->getPrice());
            $order->setAddress($address_formatted);
            $entityManager->persist($order);

            $stripe_products = [];
            foreach ($cart as $product) {
                $orderDetails = new OrderDetails();
                $orderDetails->setInvoice($order);
                $orderDetails->setName($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $entityManager->persist($orderDetails);

                $stripe_products[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => $product['product']->getPrice(),
                        'product_data' => [
                            'name' => $product['product']->getName(),
                            'images' => [$this->generateUrl('app_index', [], UrlGeneratorInterface::ABSOLUTE_URL).'uploads/'.$product['product']->getImage()],
                        ],
                    ],
                    'quantity' => $product['quantity'],
                ];
            }

            $stripe_products[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $carrier->getPrice(),
                    'product_data' => [
                        'name' => $carrier->getName(),
                        'description' => $carrier->getDescription(),
                    ],
                ],
                'quantity' => 1,
            ];

            // Stripe https://stripe.com/docs/checkout/quickstart
            Stripe::setApiKey($this->getParameter('app.stripe_public'));

            $checkout_session = Session::create([
                'customer_email' => $this->getUser()->getEmail(),
                'line_items' => [
                    $stripe_products
                ],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('app_order_success', [], UrlGeneratorInterface::ABSOLUTE_URL).'/{CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->generateUrl('app_order_error', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            $order->setCheckoutSessionId($checkout_session->id);
            $entityManager->flush();

            return $this->redirect($checkout_session->url, 303);
        }

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cartManager->get(),
            'controller_name' => 'OrderController',
        ]);
    }

    #[Route('/success/{checkoutSessionId}', name: 'app_order_success', defaults: ['checkoutSessionId' => ''])]
    public function success($checkoutSessionId, EntityManagerInterface $entityManager, CartManager $cartManager): Response
    {
        $order = $entityManager->getRepository(Order::class)->findOneByCheckoutSessionId($checkoutSessionId);

        if (!$order || $order->getCustomer() !== $this->getUser() || $order->getState() !== 'paid') {
            throw $this->createNotFoundException('Order not found');
        }

        $cartManager->remove();

        return $this->render('order/paid.html.twig', [
            'order' => $order,
            'controller_name' => 'OrderController',
        ]);
    }

    #[Route('/error', name: 'app_order_error')]
    public function error(): Response
    {
        return $this->render('order/error.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }
}
