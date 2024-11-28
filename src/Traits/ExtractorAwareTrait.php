<?php

namespace App\Traits;

use App\Extractor\Extractor;
use App\Extractor\ExtractorInterface;
use App\Resource\ResourceInterface;
use Closure;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\DomCrawler\Crawler;

trait ExtractorAwareTrait
{
    private ?string $xpath = null;

    private ?Closure $handler = null;

    public function __construct(
        private ?ResourceInterface $resource = null,
        #[AutowireDecorated]
        private readonly ?ExtractorInterface $inner = null,
    ) {
        if (!$this->getInner()) {
            $this->handler = $this->getResource()->resolveProduct(...);
        } else {
            $this->xpath = $this->getResource()?->shiftXPath();
        }
    }

    /**
     * @return ResourceInterface|null
     */
    public function getResource(): ?ResourceInterface
    {
        return $this->resource ??= $this->getInner()?->getResource();
    }

    public function getInner(): ?ExtractorInterface
    {
        return $this->inner;
    }

    public function extract(Crawler $crawler, ?int $flags = null): array|Crawler
    {
        try {
            $result = match (true) {
                !$this->xpath && $this->inner => $this->inner->extract($crawler, $flags),
                $this->xpath && $this->inner => $crawler->filterXPath($this->xpath)->each(
                    fn($crawler) => $this->inner->extract($crawler, $flags)
                ),
                $this->xpath !== null => $crawler->filterXPath($this->xpath),
                is_callable($this->handler) => ($this->handler)($crawler)
            };

            if (is_array($result) && count($result) === 1) {
                $result = $result[0];
            }

            return $result;
        } catch (\Throwable $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getXpath(): ?string
    {
        return $this->xpath;
    }

    public function setXpath(string $xpath): self
    {
        $this->xpath = $xpath;

        return $this;
    }
}