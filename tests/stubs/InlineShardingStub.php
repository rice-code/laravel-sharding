<?php

use Rice\LSharding\InlineSharding;

class InlineShardingStub extends InlineSharding
{
    protected $table = 'test_table';
    
    public function inlineExpression(): string
    {
        return '${user_id % 4 + 1}';
    }
    
    public function shardingTables(): array
    {
        return ['1', '2', '3', '4'];
    }
    
    public function shardingColumn(): string
    {
        return 'user_id';
    }
    
    public function suffixPattern(): string
    {
        return '%s';
    }
}