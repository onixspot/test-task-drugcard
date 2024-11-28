<?php

namespace App\Grabber;

use App\Filter\FilterInterface;
use App\Resource\ResourceInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Messenger\MessageBusInterface;

interface GrabberInterface
{
    public function grab(): void;

    public function getMessageBus(): MessageBusInterface;

    public function getResource(): ResourceInterface;

    public function addFilter(FilterInterface $filter): GrabberInterface;

    /**
     * @return Collection<FilterInterface>
     */
    public function getFilters(): Collection;
}