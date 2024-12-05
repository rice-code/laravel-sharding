<?php

namespace Rice\LSharding\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Rice\LSharding\EloquentBuilder;

trait OverwriteTrait
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->suffixStrategy();
    }

    public function __set($key, $value)
    {
        parent::__set($key, $value);
        $this->suffixStrategy();
    }

    public function getTable(): string
    {
        if ($this->suffix) {
            if (Str::endsWith($this->table, $this->suffix)) {
                return $this->table;
            }

            return $this->table . '_' . $this->suffix;
        }

        return parent::getTable();
    }

    protected function forwardCallTo($object, $method, $parameters)
    {
        $params                = $parameters[0] ?? [];
        if (in_array($method, ['insert'], true)) {
            // 二维数组(非关联数组)
            if (!Arr::isAssoc($params)) {
                $groups  = [];
                $results = [];
                foreach ($params as $param) {
                    $subTableName            = $this->table . '_' . $this->algorithm->getSuffix($param);
                    $groups[$subTableName][] = $param;
                }
                foreach ($groups as $subTableName => $subTableParams) {
                    $newObject = clone $object;
                    $newObject->from($subTableName);
                    $results[] = parent::forwardCallTo($newObject, $method, $subTableParams);
                }

                return $results;
            }
        }

        $this->setSuffix($this->algorithm->getSuffix($params));
        $object->from($this->getTable());

        return parent::forwardCallTo($object, $method, $parameters);
    }

    public function newEloquentBuilder($query)
    {
        return new EloquentBuilder($query);
    }
}
