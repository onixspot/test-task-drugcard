<?php

namespace App\Message;

readonly class ProductSynchronizationMessage
{
    public function __construct(
        private string $source,
        private ?string $uri = null,
        private ?int $offset = null,
        private ?int $limit = null,
    ) {
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getSource(): string
    {
        return $this->source;
    }

}