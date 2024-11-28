<?php

namespace App\Synchronizer;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ProductSynchronizer implements SynchronizerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private readonly ProductRepository $productRepository,
        private readonly ObjectNormalizer $normalizer,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function synchronize(callable $iteratorProvider): void
    {
        /** @var Generator<Product> $products */
        $products = $iteratorProvider();

        foreach ($products as $product) {
            $origin = $this->productRepository->findOneBy(['reference' => $product->getReference()]);
            $context = [];

            if ($origin instanceof Product) {
                $context = [
                    ...$context,
                    AbstractNormalizer::OBJECT_TO_POPULATE => $origin,
                ];
            }

            $normalizedProduct = $this->normalizer->normalize($product, null, [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);
            $product = $this->denormalizer->denormalize($normalizedProduct, Product::class, null, $context);
            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();
    }
}