<?php

namespace Rice\LSharding;

use Rice\LSharding\Algorithms\HintAlgorithm;

/**
 * Hint分片策略
 * 允许直接指定分片表，不依赖SQL条件
 */
abstract class HintSharding extends Sharding
{
    /**
     * 当前请求的分片信息
     *
     * @var array
     */
    protected static $currentShardingInfo = [];

    public function __construct(array $attributes = [])
    {
        $this->algorithm = new HintAlgorithm($this);
        parent::__construct($attributes);
    }

    /**
     * 设置当前请求的分片信息
     *
     * @param string|array $shardingValue
     */
    public static function setCurrentSharding($shardingValue)
    {
        $className = get_called_class();
        static::$currentShardingInfo[$className] = $shardingValue;
    }

    /**
     * 获取当前请求的分片信息
     *
     * @return string|array|null
     */
    public static function getCurrentSharding()
    {
        $className = get_called_class();
        return static::$currentShardingInfo[$className] ?? null;
    }

    /**
     * 清除当前请求的分片信息
     */
    public static function clearCurrentSharding()
    {
        $className = get_called_class();
        unset(static::$currentShardingInfo[$className]);
    }
}