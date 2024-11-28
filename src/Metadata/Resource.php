<?php

namespace App\Metadata;


use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Resource
{
    public function __construct(
        public readonly string $uri,
        public readonly string $paginationXPath,
        public readonly array $xpathChain,
    ) {
    }
}