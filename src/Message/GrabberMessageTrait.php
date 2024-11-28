<?php

namespace App\Message;

use Internal\Grabber\WebResourceInterface;

trait GrabberMessageTrait
{
    public function __construct(
        private readonly WebResourceInterface|string $webResourceClass,
        private readonly ?string $uri = null,
    ) {
    }

    public function getWebResourceClass(): string
    {
        if ($this->webResourceClass instanceof WebResourceInterface) {
            return $this->webResourceClass::class;
        }

        return $this->webResourceClass;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }
}