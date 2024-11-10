<?php

namespace App\MessageHandler;

use App\Entity\Product;
use App\Grabber\Grabber;
use App\Message\ProductSynchronizationMessage;
use App\Repository\ProductRepository;
use App\Source\GoldiUAGrabber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProductSynchronizationMessageHandler
{
    public function __construct(
        private readonly Grabber $grabber,
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function __invoke(ProductSynchronizationMessage $message)
    {
        $collection = ($this->grabber)(
            source: new GoldiUAGrabber($message->getUri()),
            offset: $message->getOffset(),
            limit: $message->getLimit()
        );
        $collection->map(function (Product $product) {
            $currentProduct = $this->productRepository->findOneBy(['reference' => $product->getReference()]);
            if ($currentProduct !== null) {
                $currentProduct
                    ->setName($product->getName())
                    ->setPrice($product->getPrice())
                    ->setImageReference($product->getImageReference());
                $product = $currentProduct;
            }
            $this->entityManager->persist($product);

            return $product;
        });
        $this->entityManager->flush();
    }
}