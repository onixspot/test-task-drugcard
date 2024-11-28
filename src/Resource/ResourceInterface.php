<?php

namespace App\Resource;

use App\Extractor\ExtractorInterface;
use ArrayIterator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DomCrawler\Crawler;

#[AutoconfigureTag(ResourceInterface::class)]
interface ResourceInterface
{
    public static function resolveUri(Crawler $node): ?string;

    public function resolveProduct(Crawler $crawler): array;

    public function shiftXPath(): ?string;

    public function getSelectors(): ArrayIterator;

    public function getExtractor(): ExtractorInterface;

    public function setExtractor(ExtractorInterface $extractor): self;

    public function getPaginationXPath(): string;

    public function getUri(): string;
}