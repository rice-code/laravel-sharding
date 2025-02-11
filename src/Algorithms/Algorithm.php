<?php

namespace Rice\LSharding\Algorithms;

use Rice\LSharding\Sharding;
use Illuminate\Database\Eloquent\Builder;

abstract class Algorithm
{
    public ?Builder $builder = null;
    public Sharding $model;

    public function __construct(Sharding $model)
    {
        $this->model = $model;
    }

    abstract public function getTables(): array;

    abstract public function getSuffix($parameters): ?string;

    abstract protected function filterKeys();
}
