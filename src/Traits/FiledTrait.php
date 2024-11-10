<?php

namespace Rice\LSharding\Traits;

trait FiledTrait
{
    /**
     * 分片键.
     *
     * @var string
     */
    protected string $shardingKey  = 'created_at';
    /**
     * 分片格式.
     *
     * @var string
     */
    protected string $suffixFormat = 'ym';

    protected ?string $suffix      = null;
    protected static array $tables = [];
}
