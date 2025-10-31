<?php

namespace Rice\LSharding;

use Rice\LSharding\Traits\GetTrait;
use Rice\LSharding\Traits\SetTrait;
use Rice\LSharding\Traits\FiledTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Rice\LSharding\Traits\OverwriteTrait;

abstract class Sharding extends Model
{
    use SetTrait;
    use GetTrait;
    use FiledTrait;
    use OverwriteTrait;

    /**
     * 分片数据源或真实表的后缀格式.
     *
     * @return mixed
     */
    abstract public function suffixPattern();

    /**
     * 分片字段
     *
     * @return string
     */
    abstract public function shardingColumn(): string;

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

    /**
     * 是否查询旧表（旧数据未做迁移）
     *
     * @return bool
     */
    public function queryOldTable(): bool
    {
        return false;
    }
}
