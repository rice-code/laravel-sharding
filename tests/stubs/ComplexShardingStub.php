<?php

use Rice\LSharding\ComplexSharding;

class ComplexShardingStub extends ComplexSharding
{
    protected $table = 'test_table';
    
    public function shardingColumns(): array
    {
        return ['user_id', 'order_id'];
    }
    
    public function calculateShardingKey(array $shardingValues): string
    {
        // 简单的复合分片策略：user_id的第一位 + order_id的第一位
        $userId = $shardingValues['user_id'];
        $orderId = $shardingValues['order_id'];
        return substr($userId, 0, 1) . substr($orderId, 0, 1);
    }
    
    public function shardingColumn(): string
    {
        return 'user_id'; // 默认使用第一个分片列
    }
    
    public function suffixPattern(): string
    {
        return '_%s';
    }
}