<?php

namespace App\Components;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('product_filter')]
class ProductFilterComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public array $query = [];

    private CategoryRepository $categoryRepository;
    private ProductRepository $productRepository;

    public function __construct(CategoryRepository $categoryRepository, ProductRepository $productRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
    }

    public function getCategories(): array
    {
        return $this->categoryRepository->findNotEmpty();
    }

    public function getProducts(): array
    {
        // example method that returns an array of Products
        return $this->productRepository->searchByCategory($this->query);
    }
}
