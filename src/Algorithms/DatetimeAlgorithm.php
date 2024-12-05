<?php

namespace Rice\LSharding\Algorithms;

use Carbon\Carbon;
use Rice\LSharding\Sharding;
use Rice\LSharding\DatetimeSharding;

class DatetimeAlgorithm implements Algorithm
{
    protected Sharding $sharding;
    protected static array $tables = [];

    public function __construct(DatetimeSharding $sharding)
    {
        $this->sharding = $sharding;
    }

    public function getTables(): array
    {
        if (self::$tables) {
            return self::$tables;
        }
        $tables         = [];
        $lower          = Carbon::parse($this->sharding->lower());
        $upper          = Carbon::parse($this->sharding->upper());
        $intervalUnit   = $this->sharding->intervalUnit();
        $intervalAmount = $this->sharding->intervalAmount();
        while ($lower < $upper) {
            $tables[]       = $this->sharding->getOriginalTable() . '_' . $lower->format($this->sharding->suffixPattern());
            switch ($intervalUnit) {
                case DatetimeSharding::UNITS['HOURS']:
                    $lower->addRealHours($intervalAmount);

                    break;
                case DatetimeSharding::UNITS['DAYS']:
                    $lower->addRealDays($intervalAmount);

                    break;
                case DatetimeSharding::UNITS['MONTHS']:
                    $lower->addMonthsNoOverflow($intervalAmount);

                    break;
                case DatetimeSharding::UNITS['YEARS']:
                    $lower->addYearsNoOverflow($intervalAmount);

                    break;
                default:
                    throw new \Exception('未定义的单位');
            }
        }

        return self::$tables = $tables;
    }

    public function getSuffix($parameters): string
    {
        $shardingValue = $this->sharding->getShardingValue($parameters);

        // 创建且未赋值分表字段
        if (!$this->sharding->exists && empty($shardingValue)) {
            return Carbon::now()->format($this->sharding->suffixPattern());
        }

        // 创建或更新使用指定字段分表
        return Carbon::parse($shardingValue)->format($this->sharding->suffixPattern());
    }
}
