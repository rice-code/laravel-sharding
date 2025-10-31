<?php

use Rice\LSharding\HintSharding;

class HintShardingStub extends HintSharding
{
    protected $table = 'test_table';
    
    public function shardingColumn(): string
    {
        return 'id';
    }
    
    public function suffixPattern(): string
    {
        return '_%s';
    }
}