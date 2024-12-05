<?php

namespace Rice\LSharding\Traits;

use Rice\LSharding\Algorithms\Algorithm;

trait FiledTrait
{
    /**
     * 分片键.
     *
     * @var string
     */
    protected string $shardingKey  = 'created_at';

    protected ?string $suffix      = null;
    protected Algorithm $algorithm;
}
