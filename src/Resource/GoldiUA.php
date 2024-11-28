<?php

namespace App\Resource;

use App\Metadata\Resource;
use App\Traits\ResourceAwareTrait;
use Symfony\Component\DomCrawler\Crawler;

#[Resource(
    uri: 'https://goldi.ua/catalog/zinocij-odag',
    paginationXPath: '//ul[@class="pagination"]/li[not(@class) or @class="active"]/a[@href]',
    xpathChain: [
        '//*[@id="main"]//section//div[@class="layout-box__content"]',
        '*/div[@class="layout-box__card"]/div[@class="product"]',
    ]
)]
class GoldiUA implements ResourceInterface
{
    use ResourceAwareTrait;

    public function resolveProduct(Crawler $crawler): array
    {
        $anchor = $crawler->filterXPath('*/a[contains(@class, "product__image")]');

        return [
            'name' => $crawler->filterXPath('//div[@class="product__name"]/a')->text(),
            'price' => number_format((float)$crawler->filterXPath('//div[@class="price"]')->text(), 2, '.', ''),
            'reference' => static::resolveUri($anchor),
            'imageReference' => static::resolveUri($anchor->filterXPath('*/img')),
        ];
    }

}