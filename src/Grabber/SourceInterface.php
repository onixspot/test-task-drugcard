<?php

namespace App\Grabber;

use Symfony\Component\DomCrawler\Crawler;

interface SourceInterface
{
    public function getUri(): string;

    public function targetEntity(): string;

    public function resolvePagination(Crawler $crawler): array;

    public function defineScope(Crawler $crawler): Crawler;

    public function resolveItems(Crawler $scope): Crawler;

    public function parseItem(Crawler $item): mixed;
}