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

    public function __construct()
    {
        $this->algorithm = new DatetimeAlgorithm($this);
        parent::__construct();
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
     * 分片数据源或真实表的后缀格式.
     *
     * @return mixed
     */
    abstract public function suffixPattern();

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
