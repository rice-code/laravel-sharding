# laravel-sharding

Laravel 分表工具包 (Laravel Sharding Toolkit)

开箱即用的分表组件包，不用侵入业务代码直接使用。

#注意点

> 小成本方案，为中小型企业进行赋能，企业有钱的话可以使用成熟的 `TiDB`, `Apache Doris` 等方案，避免
> 出现性能问题。本库使用 union all + 子查询的方式进行查询，避免大数据量查询(临时表大数据量性能很差)

> 该包还没在生产环境经受考验，要使用时可以现在测试环境跑一下，避免出现问题

# 快速上手

继承 `DatetimeSharding` 进行时间分表，然后根据业务实现以下三个方法，这样子就能够使用分表逻辑了。

```php
<?php

namespace App\Models;

use Rice\LSharding\DatetimeSharding;

class Users extends DatetimeSharding
{
    protected $table = 'users';

    // 开始值 （根据最开始出现的时间确认第一张分表）
    public function lower()
    {
        return '2024-09-01 00:00:00';
    }

    // 结束值 （null默认就是当前时间）
    public function upper()
    {
        return null;
    }

    // 分表后缀（Carbon的format）
    public function suffixPattern()
    {
        return 'ym';
    }
}
```

# 功能

- [x] 支持 `Model` 级别的 `insert`, `save`, `update`, `delete` 调用 
- [x] 支持 `order by`, `group by` 调用
- [x] 支持 `Model` 级别的数据分表查询
- [x] 时间分表算法
- [x] MySQL distinct 语法在分表统计不准确问题
- [ ] 测试用例

