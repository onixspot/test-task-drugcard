<?php

namespace App\Message;

use Internal\Grabber\WebResourceInterface;

interface GrabberMessageInterface
{
    public function __construct(WebResourceInterface|string $webResourceClass, ?string $uri = null);

    public function getWebResourceClass(): string;

    public function getUri(): ?string;
}