<?php

namespace Rice\LSharding\Algorithms;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Rice\LSharding\AutoIntervalSharding;

class AutoIntervalAlgorithm extends Algorithm
{
    public function getTables(): array
    {
        /**
         * @var AutoIntervalSharding $model
         */
        $model = $this->model;

        $tables = $model->queryOldTable() ? [$model->getOriginalTable()] : [];
        $dateRanges = $this->filterKeys();
        
        if ($dateRanges) {
            [$startDate, $endDate] = $dateRanges;
            $interval = $model->interval();
            
            while ($startDate->lte($endDate)) {
                $tables[] = $model->getShardingTable($this->getSuffix([$model->shardingColumn() => $startDate]));
                $startDate = $this->addInterval($startDate, $interval);
            }
        }

        return $tables;
    }

    protected function filterKeys()
    {
        /**
         * @var AutoIntervalSharding $model
         */
        $model = $this->model;
        $startDate = Carbon::parse($model->lower());
        $endDate = Carbon::now(); // 默认使用当前时间
        
        if ($wheres = ($this->builder->getQuery()->wheres ?? [])) {
            foreach ($wheres as $where) {
                // 分片字段
                if (Str::after($where['column'], '.') === $model->shardingColumn()) {
                    if ('>' === $where['operator'] || '>=' === $where['operator']) {
                        $startDate = Carbon::parse($where['value']);
                    }
                    if ('<' === $where['operator'] || '<=' === $where['operator']) {
                        $endDate = Carbon::parse($where['value']);
                    }
                }
            }
        }

        return [$startDate, $endDate];
    }

    public function getSuffix($parameters): ?string
    {
        /**
         * @var AutoIntervalSharding $model
         */
        $model = $this->model;
        $shardingValue = $model->getShardingValue($parameters);

        // 未获取到分片值，使用当前时间
        if (is_null($shardingValue)) {
            return Carbon::now()->format($model->dateTimeFormat());
        }

        // 格式化日期时间
        try {
            $date = Carbon::parse($shardingValue);
            return $date->format($model->dateTimeFormat());
        } catch (\Exception $e) {
            return Carbon::now()->format($model->dateTimeFormat());
        }
    }

    /**
     * 根据时间间隔表达式增加时间
     */
    private function addInterval(Carbon $date, string $interval): Carbon
    {
        $value = (int)Str::before($interval, substr($interval, -1));
        $unit = strtolower(substr($interval, -1));
        
        switch ($unit) {
            case 'd':
                return $date->addDays($value);
            case 'w':
                return $date->addWeeks($value);
            case 'm':
                return $date->addMonths($value);
            case 'y':
                return $date->addYears($value);
            default:
                throw new \InvalidArgumentException("Invalid interval unit: {$unit}");
        }
    }
}