<?php

namespace App\Source;

use App\Entity\Product;
use App\Grabber\SourceInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\UriResolver;

class GoldiUASource implements SourceInterface
{
    public const URI = 'https://goldi.ua/catalog/zinocij-odag';

    public function __construct(
        private readonly ?string $uri = null,
        private readonly array $uriVariables = [],
    ) {
    }

    public function targetEntity(): string
    {
        return Product::class;
    }

    public function resolvePagination(Crawler $crawler): array
    {
        return $crawler
            ->filterXPath('//ul[@class="pagination"]/li[not(@class) or @class="active"]/a[@href]')
            ->extract(['href']);
    }

    public function defineScope(Crawler $crawler): Crawler
    {
        return $crawler->filterXPath('//*[@id="main"]//section//div[@class="layout-box__content"]');
    }

    public function resolveItems(Crawler $scope): Crawler
    {
        return $scope->filterXPath('*/div[@class="layout-box__card"]/div[@class="product"]');
    }

    public function parseItem(Crawler $item): array
    {
        return [
            'name' => $this->parseName($item),
            'price' => $this->parsePrice($item),
            'reference' => $this->parseReference($item),
            'imageReference' => $this->parseImageReference($item),
        ];
    }

    private function parseName(Crawler $item): string
    {
        return $item->filterXPath('//div[@class="product__name"]/a')->text();
    }

    private function parsePrice(Crawler $item): string
    {
        return number_format((float) $item->filterXPath('//div[@class="price"]')->text(), 2, '.', '');
    }

    private function parseReference(Crawler $item): ?string
    {
        return UriResolver::resolve($this->getReference($item)->attr('href'), $this->getUri());
    }

    private function getReference(Crawler $item): Crawler
    {
        return $item->filterXPath('*/a[contains(@class, "product__image")]');
    }

    public function getUri(): string
    {
        return $this->uri ? UriResolver::resolve($this->uri, self::URI) : self::URI;
    }

    private function parseImageReference(Crawler $item): ?string
    {
        return UriResolver::resolve($this->getReference($item)->filterXPath('*/img')->attr('src'), $this->getUri());
    }

    public function getUriVariables(): array
    {
        return $this->uriVariables;
    }

}