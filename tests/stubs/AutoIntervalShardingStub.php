<?php

use Rice\LSharding\AutoIntervalSharding;

class AutoIntervalShardingStub extends AutoIntervalSharding
{
    protected $table = 'test_table';
    
    public function lower(): string
    {
        return '2023-01-01';
    }
    
    public function interval(): string
    {
        return '1m';
    }
    
    public function dateTimeFormat(): string
    {
        return 'Ym';
    }
    
    public function suffixPattern(): string
    {
        return '_%s';
    }
    
    public function getTableSuffix(array $params = []): string
    {
        // 获取格式化后的日期值
        $dateValue = $this->formatDateTime($params[$this->shardingColumn()]);
        // 应用后缀模式
        return sprintf($this->suffixPattern(), $dateValue);
    }
    
    protected function formatDateTime($date): string
    {
        if (!$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }
        return $date->format($this->dateTimeFormat());
    }
    
    public function shardingColumn(): string
    {
        return 'created_at';
    }
}