<?php

namespace App\Grabber;

use App\Source\GoldiUASource;
use UnhandledMatchError;

class SourceFactory
{
    /**
     * @template T
     * @param class-string<T> $class
     * @param                 ...$rest
     *
     * @return T
     */
    public function createSource(string $class, ...$rest): SourceInterface
    {
        try {
            return match ($class) {
                GoldiUASource::class => new GoldiUASource(...$rest),
            };
        } catch (UnhandledMatchError) {
            throw new \RuntimeException('Unhandled class: '.$class);
        }
    }
}