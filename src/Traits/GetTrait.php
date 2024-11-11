<?php

namespace Rice\LSharding\Traits;

use Carbon\Carbon;

trait GetTrait
{
    public function getOriginalTable(): string
    {
        return $this->table;
    }

    public function getTables(): array
    {
        if (self::$tables) {
            return self::$tables;
        }
        $tables    = [];
        $startTime = Carbon::parse('2024-09-01');
        while ($startTime < Carbon::now()) {
            $tables[] = $this->table . '_' . $startTime->format($this->suffixFormat);
            $startTime->addMonthNoOverflow();
        }

        return self::$tables = $tables;
    }

    /**
     * @param $parameters
     * @return mixed|null
     */
    private function getSubFieldValue($parameters)
    {
        if (is_object($parameters)) {
            return null;
        }

        return $parameters[$this->shardingKey] ?? $this->attributes[$this->shardingKey] ?? null;
    }

    /**
     * @param $parameters
     * @return string
     */
    private function getSuffix($parameters): string
    {
        $subFieldValue = $this->getSubFieldValue($parameters);

        // 创建且未赋值分表字段
        if (!$this->exists && empty($subFieldValue)) {
            return Carbon::now()->format($this->suffixFormat);
        }

        // 创建或更新使用指定字段分表
        return Carbon::parse($subFieldValue)->format($this->suffixFormat);
    }
}
