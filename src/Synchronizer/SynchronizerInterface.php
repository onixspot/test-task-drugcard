<?php

namespace App\Synchronizer;

interface SynchronizerInterface
{
    public function synchronize(callable $iteratorProvider): void;

}