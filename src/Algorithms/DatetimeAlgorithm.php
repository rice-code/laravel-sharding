<?php

namespace Rice\LSharding\Algorithms;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Rice\LSharding\DatetimeSharding;

class DatetimeAlgorithm extends Algorithm
{
    public function getTables(): array
    {
        /**
         * @var $model DatetimeSharding
         */
        $model = $this->model;
        $tables          = $model->queryOldTable() ? [$model->getOriginalTable()] : [];
        [$lower, $upper] = $this->filterKeys();
        $intervalUnit    = $model->intervalUnit();
        $intervalAmount  = $model->intervalAmount();
        while ($lower < $upper) {
            $tables[]       = $model->getShardingTable($this->getSuffix([$model->shardingColumn() => $lower]));
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

        return $tables;
    }

    protected function filterKeys()
    {
        /**
         * @var $model DatetimeSharding
         */
        $model = $this->model;
        $lower = Carbon::parse($model->lower());
        $upper = Carbon::parse($model->upper());
        if ($wheres = ($this->builder->getQuery()->wheres ?? [])) {
            foreach ($wheres as $where) {
                // 分片字段
                if (Str::after($where['column'], '.') === $model->shardingColumn()) {
                    if ('>' === $where['operator']) {
                        $lower = Carbon::parse($where['value']);
                    }
                    if ('<' === $where['operator']) {
                        $upper = Carbon::parse($where['value']);
                    }
                }
            }
        }

        return [$lower, $upper];
    }

    public function getSuffix($parameters): ?string
    {
        /**
         * @var $model DatetimeSharding
         */
        $model = $this->model;
        $shardingValue = $model->getShardingValue($parameters);

        // 创建且未赋值分表字段
        if (!$model->exists && empty($shardingValue)) {
            return Carbon::now()->format($model->suffixPattern());
        }

        // 时间格式不正确时，返回当前时间
        try {
            // 创建或更新使用指定字段分表
            return Carbon::parse($shardingValue)->format($model->suffixPattern());
        } catch (\Exception $e) {
            return Carbon::now()->format($model->suffixPattern());
        }
    }
}
