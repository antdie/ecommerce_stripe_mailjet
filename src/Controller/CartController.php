<?php

namespace App\Controller;

use App\Service\CartManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    private $cartManager;

    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    #[Route('', name: 'app_cart')]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $this->cartManager->get(),
            'controller_name' => 'CartController',
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add')]
    public function add($id): Response
    {
        $this->cartManager->add($id);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/decrease/{id}', name: 'app_cart_decrease')]
    public function decrease($id): Response
    {
        $this->cartManager->decrease($id);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/delete/{id}', name: 'app_cart_delete')]
    public function delete($id): Response
    {
        $this->cartManager->delete($id);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/remove', name: 'app_cart_remove')]
    public function remove(): Response
    {
        $this->cartManager->remove();

        return $this->redirectToRoute('app_products');
    }
}
