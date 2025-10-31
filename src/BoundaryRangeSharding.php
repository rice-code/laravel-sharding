<?php

namespace Rice\LSharding;

use InvalidArgumentException;
use Rice\LSharding\Algorithms\BoundaryRangeAlgorithm;

abstract class BoundaryRangeSharding extends Sharding
{
    public function __construct(array $attributes = [])
    {
        $this->algorithm = new BoundaryRangeAlgorithm($this);

        parent::__construct($attributes);
    }

    /**
     * 范围区间配置（按分表键划分）
     * 格式：[
     *     ['start' => 1, 'end' => 10000, 'suffix' => '0'],   // 分表键在 1-10000 对应表后缀 0
     *     ['start' => 10001, 'end' => 20000, 'suffix' => '1'], // 分表键在 10001-20000 对应表后缀 1
     *     ...
     * ]
     * @return array
     */
    abstract function boundaries(): array;
}