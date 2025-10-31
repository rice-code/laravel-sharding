<?php

namespace Rice\LSharding;

use Rice\LSharding\Algorithms\DatetimeAlgorithm;

abstract class DatetimeSharding extends Sharding
{
    public const UNITS = [
        'HOURS'  => 1,
        'DAYS'   => 2,
        'WEEKS'  => 3,
        'MONTHS' => 4,
        'YEARS'  => 5,
    ];

    public function __construct(array $attributes = [])
    {
        $this->algorithm = new DatetimeAlgorithm($this);
        parent::__construct($attributes);
    }
    /**
     * 时间分片下界值
     *
     * @return mixed
     */
    abstract public function lower();

    /**
     * 时间分片上界值
     *
     * @return mixed
     */
    abstract public function upper();

    /**
     *
     * @return string
     */
    public function shardingColumn(): string
    {
        return 'created_at';
    }

    /**
     * 分片键时间间隔，超过该时间间隔将进入下一分片.
     *
     * @return int
     */
    public function intervalAmount(): int
    {
        return 1;
    }

    /**
     * 分片键时间间隔单位
     *
     * @return int
     */
    public function intervalUnit(): int
    {
        return self::UNITS['MONTHS'];
    }
}
