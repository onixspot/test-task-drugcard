<?php

namespace App\Synchronizer;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
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

    public function synchronize(Collection $collection): void
    {
        try {
            foreach ($collection->toArray() as $product) {
                $origin = $this->productRepository->findOneBy(['reference' => $product['reference']]);

                $context = [];
                if ($origin instanceof Product) {
                    $context = [
                        ...$context,
                        AbstractNormalizer::OBJECT_TO_POPULATE => $origin,
                    ];
                }

                $product = $this->denormalizer->denormalize($product, Product::class, null, $context);
                $this->entityManager->persist($product);
            }

            $this->entityManager->flush();
        } catch (\Throwable $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}