<?php

declare(strict_types=1);

namespace App\Controller\Api;

use ApiPlatform\OpenApi\Model\Response;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class ProductController extends AbstractController
{
    #[Route('/products', name: 'product_index', methods:['get'])]
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): JsonResponse
    {
        $queryBuilder = $entityManager->getRepository(Product::class)->createQueryBuilder('p');

        // Отримання фільтрів з запиту
        $title = $request->query->get('title');
        $quantityMin = $request->query->getInt('quantity_min');
        $quantityMax = $request->query->getInt('quantity_max');
        $priceMin = $request->query->get('price_min');
        $priceMax = $request->query->get('price_max');

        // Додавання фільтрів до запиту
        if ($title) {
            $queryBuilder->andWhere('LOWER(p.title) LIKE LOWER(:title)')
                ->setParameter('title', '%' . $title . '%');
        }

        if ($quantityMin !== null && $quantityMin !== '') {
            $queryBuilder->andWhere('p.quantity >= :quantityMin')
                ->setParameter('quantityMin', (int) $quantityMin);
        }

        if ($quantityMax !== null && $quantityMax !== '') {
            $queryBuilder->andWhere('p.quantity <= :quantityMax')
                ->setParameter('quantityMax', (int) $quantityMax);
        }

        if ($priceMin !== null) {
            $queryBuilder->andWhere('p.price >= :priceMin')
                ->setParameter('priceMin', (float) $priceMin);
        }

        if ($priceMax !== null) {
            $queryBuilder->andWhere('p.price <= :priceMax')
                ->setParameter('priceMax', (float) $priceMax);
        }

        // Отримуємо запит
        $query = $queryBuilder->getQuery();

        // Пагінація
        $page = $request->query->getInt('page', 1);
        $products = $paginator->paginate($query, $page, 5);

        // Форматування результату
        $data = [
            'items' => $products->getItems(),
            'pagination' => [
                'currentPage' => $products->getCurrentPageNumber(),
                'itemsPerPage' => $products->getItemNumberPerPage(),
                'totalItems' => $products->getTotalItemCount(),
            ],
        ];

        return $this->json($data);
    }

    #[Route('/products', name: 'product_create', methods:['post'])]
    public function create(ValidatorInterface $validator, Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $product->setTitle($request->request->get('title'));
        $product->setQuantity($request->request->get('quantity'));
        $product->setPrice($request->request->get('price'));

        $entityManager->persist($product);
        $entityManager->flush();

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            return new Response((string) $errors);
        }

        return new Response('Успішно додано новий продукт з ID: '.$product->getId());
    }

    #[Route('/products/{id}', name: 'product_show', methods:['get'])]
    public function show(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            return $this->json('No product found for id ' . $id, 404);
        }

        $data =  [
            'id' => $product->getId(),
            'title' => $product->getTitle(),
            'quantity' => $product->getQuantity(),
            'price' => $product->getPrice(),
        ];

        return $this->json($data);
    }

    #[Route('/products/{id}', name: 'product_update', methods:['put', 'patch'])]
    public function update(EntityManagerInterface $entityManager, Request $request, int $id): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            return $this->json('No product found for id ' . $id, 404);
        }

        $product->setTitle($request->request->get('title'));
        $product->setQuantity($request->request->get('quantity'));
        $product->setPrice($request->request->get('price'));
        $entityManager->flush();

        $data =  [
            'id' => $product->getId(),
            'title' => $product->getName(),
            'quantity' => $product->getQuantity(),
            'price' => $product->getPrice(),
        ];

        return $this->json($data);
    }

    #[Route('/products/{id}', name: 'product_delete', methods:['delete'])]
    public function delete(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            return $this->json('No product found for id ' . $id, 404);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return $this->json('Deleted a product successfully with id ' . $id, 204,
            ['Content-Type' => 'application/json']);
    }
}
