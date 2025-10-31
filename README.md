# laravel-sharding

Laravel 分表工具包 (Laravel Sharding Toolkit)

开箱即用的分表组件包，不用侵入业务代码直接使用。提供多种分片算法，满足不同业务场景的分表需求。

## 注意事项

> 小成本方案，为中小型企业进行赋能，企业有钱的话可以使用成熟的 `TiDB`, `Apache Doris` 等方案，避免
> 出现性能问题。本库使用 union all + 子查询的方式进行查询，避免大数据量查询(临时表大数据量性能很差)

> 该包还没在生产环境经受考验，要使用时可以现在测试环境跑一下，避免出现问题

## 功能特性

- [x] 支持 `Model` 级别的 `insert`, `save`, `update`, `delete` 调用 
- [x] 支持 `order by`, `group by` 调用
- [x] 支持 `Model` 级别的数据分表查询
- [x] 多种分表算法支持
- [x] 解决 MySQL distinct 语法在分表统计不准确问题
- [x] 完善的测试用例

## 实现原理

1. `Illuminate\Database\Eloquent\Model` -> `Rice\LSharding\Sharding` 继承 `Model` 类
2. 重写 `__construct`, `__set`, `getTable`, `forwardCallTo`, `newEloquentBuilder` 方法
3. 继承 `Rice\LSharding\Sharding` 实现对应的分表类和算法

## 分片算法使用教程

### 1. 时间分片 (DatetimeSharding)

**说明**：根据时间范围进行分表，适用于按时间增长的数据，如日志、订单等。

**使用示例**：

```php
<?php

namespace App\Models;

use Rice\LSharding\DatetimeSharding;

class OrderLog extends DatetimeSharding
{
    protected $table = 'order_logs';

    // 开始时间（确定第一张分表的时间点）
    public function lower()
    {
        return '2024-01-01 00:00:00';
    }

    // 结束时间（null表示当前时间）
    public function upper()
    {
        return null;
    }

    // 分表后缀格式（使用Carbon的format格式）
    // ym 表示按月分表，如 order_logs_2401, order_logs_2402
    public function suffixPattern()
    {
        return 'ym';
    }
    
    // 分片字段（默认为created_at）
    public function shardingColumn()
    {
        return 'created_at';
    }
}
```

**适用场景**：日志记录、订单历史、交易记录等按时间顺序增长的数据。

### 2. 取模分片 (ModSharding)

**说明**：根据字段值进行取模运算，将数据均匀分布到多个表中。

**使用示例**：

```php
<?php

namespace App\Models;

use Rice\LSharding\ModSharding;

class UserData extends ModSharding
{
    protected $table = 'user_data';
    
    // 分片数量
    public function shardingCount()
    {
        return 10; // 分成10张表
    }
    
    // 分片字段
    public function shardingColumn()
    {
        return 'user_id';
    }
    
    // 分片后缀格式
    public function suffixPattern()
    {
        return '_%d'; // 生成 user_data_0, user_data_1 格式
    }
}
```

**适用场景**：用户数据、配置信息等需要均匀分布且查询频繁的场景。

### 3. Hash取模分片 (HashModSharding)

**说明**：对字段值进行Hash计算后再取模，适合非数值类型字段的分片。

**使用示例**：

```php
<?php

namespace App\Models;

use Rice\LSharding\HashModSharding;

class ProductData extends HashModSharding
{
    protected $table = 'product_data';
    
    // 分片数量
    public function shardingCount()
    {
        return 8;
    }
    
    // 分片字段（可以是字符串类型）
    public function shardingColumn()
    {
        return 'product_code'; // 产品编码，字符串类型
    }
    
    // 分片后缀格式
    public function suffixPattern()
    {
        return '_%d';
    }
}
```

**适用场景**：商品数据、设备信息等使用字符串作为主键或标识的数据。

### 4. 自动时间间隔分片 (AutoIntervalSharding)

**说明**：根据自定义时间间隔动态调整分片，支持不同粒度的时间分片。

**使用示例**：

```php
<?php

namespace App\Models;

use Rice\LSharding\AutoIntervalSharding;

class ActivityLog extends AutoIntervalSharding
{
    protected $table = 'activity_logs';
    
    // 时间分片下界值
    public function lower(): string
    {
        return '2024-01-01';
    }
    
    // 分片时间间隔
    // 支持格式：1d(天), 1w(周), 1m(月), 1y(年)
    public function interval(): string
    {
        return '1w'; // 每周一个分片
    }
    
    // 分片键时间格式
    public function dateTimeFormat(): string
    {
        return 'YW'; // 年和周数
    }
    
    // 分片字段
    public function shardingColumn(): string
    {
        return 'log_time';
    }
    
    // 分片后缀格式
    public function suffixPattern(): string
    {
        return '_%s';
    }
}
```

**适用场景**：需要灵活时间间隔的数据，如活动记录、监控数据等。

### 5. 边界范围分片 (BoundaryRangeSharding)

**说明**：根据预定义的边界范围进行分片，适合数据分布不均匀的场景。

**使用示例**：

```php
<?php

namespace App\Models;

use Rice\LSharding\BoundaryRangeSharding;

class CustomerData extends BoundaryRangeSharding
{
    protected $table = 'customer_data';
    
    // 定义边界范围
    public function getBoundaryRanges(): array
    {
        // 返回边界数组，如 [1000, 5000, 10000]
        // 会分成四个区间：<1000, 1000-5000, 5000-10000, >10000
        return [1000, 5000, 10000];
    }
    
    // 分片字段
    public function shardingColumn(): string
    {
        return 'customer_level'; // 根据客户等级分片
    }
    
    // 分片后缀格式
    public function suffixPattern(): string
    {
        return '_%d';
    }
}
```

**适用场景**：会员等级、消费金额等有明显区间特征的数据。

### 6. 卷积分片 (VolumeRangeSharding)

**说明**：按照固定数据量进行分片，每个分片存储指定数量的数据。

**使用示例**：

```php
<?php

namespace App\Models;

use Rice\LSharding\VolumeRangeSharding;

class LargeData extends VolumeRangeSharding
{
    protected $table = 'large_data';
    
    // 每个分片的容量
    public function shardingVolume(): int
    {
        return 100000; // 每个分片10万条数据
    }
    
    // 起始值
    public function startValue(): int
    {
        return 1;
    }
    
    // 最大值
    public function maxValue(): int
    {
        return 1000000; // 最大100万条数据
    }
    
    // 分片字段
    public function shardingColumn(): string
    {
        return 'id';
    }
    
    // 分片后缀格式
    public function suffixPattern(): string
    {
        return '_%d';
    }
}
```

**适用场景**：大数据量存储，需要按固定数量分片的场景。

### 7. 行表达式分片 (InlineSharding)

**说明**：使用表达式动态计算分片，可以实现复杂的分片策略。

**使用示例**：

```php
<?php

namespace App\Models;

use Rice\LSharding\InlineSharding;

class OrderData extends InlineSharding
{
    protected $table = 'order_data';
    
    // 行表达式
    public function inlineExpression(): string
    {
        // user_id % 4 + 1 表示将用户ID取模后+1，分成4个分片
        return '${user_id % 4 + 1}';
    }
    
    // 分片表列表
    public function shardingTables(): array
    {
        return ['1', '2', '3', '4'];
    }
    
    // 分片字段
    public function shardingColumn(): string
    {
        return 'user_id';
    }
    
    // 分片后缀格式
    public function suffixPattern(): string
    {
        return '_%s';
    }
}
```

**适用场景**：需要复杂分片逻辑的数据，如多维度分片。

### 8. 复合分片 (ComplexSharding)

**说明**：基于多个字段进行复合分片，提供更灵活的数据分布策略。

**使用示例**：

```php
<?php

namespace App\Models;

use Rice\LSharding\ComplexSharding;

class MultiDimensionData extends ComplexSharding
{
    protected $table = 'multi_data';
    
    // 分片字段列表
    public function shardingColumns(): array
    {
        return ['region_id', 'type_id'];
    }
    
    // 计算分片键
    public function calculateShardingKey(array $params): string
    {
        // 组合多个字段计算分片键
        $regionId = $params['region_id'] ?? 0;
        $typeId = $params['type_id'] ?? 0;
        
        // 可以根据业务需求设计复杂的计算逻辑
        return md5($regionId . '-' . $typeId);
    }
    
    // 分片数量
    public function shardingCount(): int
    {
        return 16;
    }
    
    // 分片后缀格式
    public function suffixPattern(): string
    {
        return '_%d';
    }
}
```

**适用场景**：需要多维度数据分布的场景，如按地区和类型联合分片。

### 9. Hint分片 (HintSharding)

**说明**：通过外部指定分片值，灵活控制数据路由。

**使用示例**：

```php
<?php

namespace App\Models;

use Rice\LSharding\HintSharding;

class DynamicData extends HintSharding
{
    protected $table = 'dynamic_data';
    
    // 分片字段
    public function shardingColumn(): string
    {
        return 'shard_key';
    }
    
    // 分片后缀格式
    public function suffixPattern(): string
    {
        return '_%s';
    }
}

// 使用方式
DynamicData::setCurrentSharding('202401'); // 设置当前分片
$results = DynamicData::where('status', 1)->get();
DynamicData::clearCurrentSharding(); // 清除分片设置
```

**适用场景**：需要手动指定分片的场景，如管理后台按日期查询。

## 快速上手

1. 选择适合的分片算法
2. 创建模型类继承相应的分片类
3. 实现必要的抽象方法
4. 像使用普通Eloquent模型一样使用分片模型

## 注意事项

1. 在查询时尽量包含分片字段，以提高查询效率
2. 避免跨多个分片的复杂查询，可能会影响性能
3. 分表设计需要提前规划，考虑未来的数据增长
4. 对于大数据量查询，建议使用更专业的分布式数据库解决方案


