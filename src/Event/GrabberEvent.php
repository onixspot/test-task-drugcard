<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class GrabberEvent extends Event
{
    public function __construct(
        private mixed $data,
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
}