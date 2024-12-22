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
    #[Route('/product', name: 'product_index', methods:['get'])]
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): JsonResponse
    {
        $productsQuery = $entityManager
            ->getRepository(Product::class)
            ->createQueryBuilder('b')
            ->select('b.id, b.title, b.quantity, b.price')
            ->getQuery()
            ->getArrayResult();

        $page = $request->query->getInt('page', 1);

        $productList = $paginator->paginate(
            $productsQuery,
            $page,
            5
        );

        return $this->json($productList);
    }

    #[Route('/product', name: 'product_create', methods:['post'])]
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

    #[Route('/product/{id}', name: 'product_show', methods:['get'])]
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

    #[Route('/product/{id}', name: 'product_update', methods:['put', 'patch'])]
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

    #[Route('/product/{id}', name: 'product_delete', methods:['delete'])]
    public function delete(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            return $this->json('No product found for id ' . $id, 404);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return $this->json('Deleted a product successfully with id ' . $id);
    }
}
