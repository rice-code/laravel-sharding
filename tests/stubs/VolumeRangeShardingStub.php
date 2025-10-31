<?php

use Rice\LSharding\VolumeRangeSharding;

class VolumeRangeShardingStub extends VolumeRangeSharding
{
    protected $table = 'test_table';
    
    public function shardingVolume(): int
    {
        return 1000;
    }
    
    public function startValue(): int
    {
        return 1;
    }
    
    public function maxValue(): ?int
    {
        return 10000;
    }
    
    public function shardingColumn(): string
    {
        return 'id';
    }
    
    public function suffixPattern(): string
    {
        return '_%d';
    }
}