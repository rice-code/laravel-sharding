<?php

namespace Rice\LSharding;

use Rice\LSharding\Algorithms\DatetimeAlgorithm;
use Rice\LSharding\Algorithms\ModAlgorithm;

abstract class ModSharding extends Sharding
{
    public function __construct(array $attributes = [])
    {
        $this->algorithm = new ModAlgorithm($this);
        parent::__construct($attributes);
    }
    /**
     * 分片数量
     *
     * @return int
     */
    abstract public function count(): int;
}
