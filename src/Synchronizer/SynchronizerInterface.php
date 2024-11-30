<?php

namespace App\Synchronizer;

use Doctrine\Common\Collections\Collection;

interface SynchronizerInterface
{
    public function synchronize(Collection $collection): void;

}