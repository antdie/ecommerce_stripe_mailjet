<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CartManager
{
    private EntityManagerInterface $entityManager;

    private $session;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->session = $requestStack->getSession();
    }

    public function get(): array
    {
        $cart = [];

        $sessionCart = $this->session->get('cart', []);
        $products = $this->entityManager->getRepository(Product::class)->findById(array_keys($sessionCart));
        $formattedProducts = [];
        foreach ($products as $product) {
            $formattedProducts[$product->getId()] = $product;
        }
        foreach ($sessionCart as $id => $value) {
            if (!array_key_exists($id, $formattedProducts)) {
                $this->delete($id);
                continue;
            }

            $cart[] = [
                'product' => $formattedProducts[$id],
                'quantity' => $value
            ];
        }

        return $cart;
    }

    public function add($id)
    {
        $cart = $this->session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }

    public function decrease($id)
    {
        $cart = $this->session->get('cart', []);

        if ($cart[$id] > 1) {
            $cart[$id]--;
        } else {
            unset($cart[$id]);
        }

        $this->session->set('cart', $cart);
    }

    public function delete($id)
    {
        $cart = $this->session->get('cart', []);

        unset($cart[$id]);

        $this->session->set('cart', $cart);
    }

    public function remove()
    {
        return $this->session->remove('cart');
    }
}
