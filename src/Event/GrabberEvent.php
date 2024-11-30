<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class GrabberEvent extends Event
{
    public function __construct(
        private mixed $data,
        private readonly ?string $resourceClass = null,
    ) {
    }

    public function withData(mixed $data): GrabberEvent|static
    {
        $clone = clone $this;
        $clone->data = $data;

        return $clone;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getResourceClass(): ?string
    {
        return $this->resourceClass;
    }
}