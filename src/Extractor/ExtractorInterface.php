<?php

namespace App\Extractor;

use Symfony\Component\DomCrawler\Crawler;

interface ExtractorInterface
{
    public function extract(Crawler $crawler, ?int $flags = null): array|Crawler;

//    public function then(ExtractorInterface $extractor): ExtractorInterface;
}