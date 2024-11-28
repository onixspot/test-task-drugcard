<?php

namespace App\Filter;

class PagesFilter implements FilterInterface
{
    public function __construct(
        private int $offset = 0,
        private ?int $limit = null,
    )
    {
    }

    public function withOffset(int $offset): PagesFilter
    {
        return (clone $this)->setOffset($offset);
    }

    private function setOffset(int $offset): PagesFilter
    {
        $this->offset = $offset;

        return $this;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): PagesFilter
    {
        $this->limit = $limit;

        return $this;
    }


}