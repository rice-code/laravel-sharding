<?php

namespace Rice\LSharding;

use Rice\LSharding\Traits\GetTrait;
use Rice\LSharding\Traits\SetTrait;
use Rice\LSharding\Traits\FiledTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Rice\LSharding\Traits\OverwriteTrait;

class Sharding extends Model
{
    use SetTrait;
    use GetTrait;
    use FiledTrait;
    use OverwriteTrait;

    public function suffixStrategy(array $parameters = []): void
    {
        $this->setSuffix($this->algorithm->getSuffix($parameters));
    }

    public static function suffix($suffix = null): Builder
    {
        $instance = new static();
        $instance->setSuffix($suffix);

        return $instance->newQuery();
    }
}
