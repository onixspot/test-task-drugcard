<?php

namespace App\Traits;

use App\Extractor\ExtractorInterface;
use ArrayIterator;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\UriResolver;
use UnexpectedValueException;

trait ResourceAwareTrait
{
    private const array URI_ELEMENT_ATTRIBUTE = [
        'a' => 'href',
        'img' => 'src',
    ];

    private ArrayIterator $selectors;

    private ?ExtractorInterface $extractor = null;

    public function __construct(
        private readonly string $uri,
        array $selectors,
        private readonly string $paginationXPath
    ) {
        $this->selectors = new ArrayIterator(array_reverse($selectors));
    }

    public static function resolveUri(Crawler $node): ?string
    {
        try {
            return UriResolver::resolve(
                $node->attr(
                    match ($node->nodeName()) {
                        'a' => 'href',
                        'img' => 'src',
                    }
                ),
                $node->getUri()
            );
        } catch (UnexpectedValueException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function shiftXPath(): ?string
    {
        $iterator = $this->getSelectors();
        if (!$iterator->valid()) {
            return null;
        }

        $current = $iterator->current();
        $iterator->next();

        return $current;
    }

    public function getSelectors(): ArrayIterator
    {
        return $this->selectors;
    }

    public function getExtractor(): ExtractorInterface
    {
        return $this->extractor;
    }

    public function setExtractor(ExtractorInterface $extractor): self
    {
        $this->extractor = $extractor;

        return $this;
    }

    public function getPaginationXPath(): string
    {
        return $this->paginationXPath;
    }

    public function getUri(): string
    {
        return $this->uri;
    }
}