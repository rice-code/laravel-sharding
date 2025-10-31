<?php

namespace Rice\LSharding;

use Rice\LSharding\Algorithms\DatetimeAlgorithm;
use Rice\LSharding\Algorithms\HashModAlgorithm;

abstract class HashModSharding extends Sharding
{
    public function __construct(array $attributes = [])
    {
        $this->algorithm = new HashModAlgorithm($this);
        parent::__construct($attributes);
    }
    /**
     * 分片数量
     *
     * @return int
     */
    abstract public function count(): int;
}
