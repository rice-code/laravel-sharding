<?php

namespace Rice\LSharding\Traits;

trait SetTrait
{
    public function setSuffix($suffix = null): void
    {
        $this->suffix = $suffix;
    }
}
