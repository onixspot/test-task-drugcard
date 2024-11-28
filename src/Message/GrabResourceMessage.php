<?php

namespace App\Message;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final readonly class GrabResourceMessage
{
    /**
     * @param string $resourceClass
     * @param string|null $uri
     * @param array{ offset?: int, limit?: int }|null $pagination
     * @param array $filters
     */
    public function __construct(
        public string $resourceClass,
        public ?string $uri = null,
        public ?array $pagination = null,
        public array $filters = [],
    ) {
    }

    public function getFilters(): Collection
    {
        return new ArrayCollection($this->filters);
    }
}
