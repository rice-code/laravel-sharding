<?php

namespace Rice\LSharding;

use Illuminate\Database\Eloquent\Builder;

class EloquentBuilder extends Builder
{
    public function getModels($columns = ['*'])
    {
        if ($this->model instanceof Sharding) {

            return (new ShardingBuilder($this->model, $this->query))->getModels();
        }

        return parent::getModels($columns);
    }
}
