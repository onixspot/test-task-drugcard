<?php

namespace App\Extractor;

use App\Resource\ResourceInterface;
use App\Traits\ExtractorAwareTrait;
use Override;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\DomCrawler\Crawler;

class PaginationExtractor implements ExtractorInterface
{
    use ExtractorAwareTrait;

    public const int PAGINATION_ONLY = 1;

    public function __construct(
        private ?ResourceInterface $resource = null,
        #[AutowireDecorated]
        private readonly ?ExtractorInterface $inner = null
    ) {
        $this->setXpath($this->getResource()?->getPaginationXPath());
    }

    #[Override] public function extract(Crawler $crawler, ?int $flags = null): array|Crawler
    {
        if ($flags & self::PAGINATION_ONLY) {
            return $crawler->filterXPath($this->getXpath())->extract(['href']);
        }

        return $this->getInner()?->extract($crawler, $flags);
    }
}