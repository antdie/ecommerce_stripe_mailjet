<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }

    #[Route('/product/{category}/{slug}/{id}', name: 'app_product')]
    public function show($category, $slug, $id, EntityManagerInterface $entityManager): Response
    {
        $product = $entityManager->getRepository(Product::class)->findOneBy([
            'slug' => $slug,
            'id' => $id
        ]);

        if (!$product || $product->getCategory()->getSlug() !== $category) {
            throw $this->createNotFoundException('Product or category not found');
        }

        $products = $entityManager->getRepository(Product::class)->findByHomepage(true, null, 3);

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'products' => $products,
            'controller_name' => 'ProductController',
        ]);
    }
}
