<?php

namespace Rice\LSharding\Algorithms;

interface Algorithm
{
    public function getTables(): array;
    public function getSuffix($parameters): string;
}
