# laravel-sharding

Laravel 分表工具包 (Laravel Sharding Toolkit)

开箱即用的分表组件包，不用侵入业务代码直接使用。

#注意点

> 小成本方案，为中小型企业进行赋能，企业有钱的话可以使用成熟的 `TiDB`, `Apache Doris` 等方案，避免
> 出现性能问题。本库使用 union all + 子查询的方式进行查询，避免大数据量查询(临时表大数据量性能很差)

> 该包还没在生产环境经受考验，要使用时可以现在测试环境跑一下，避免出现问题


# 待办

- [x] 支持 `Model` 级别的 `insert`, `save`, `update`, `delete` 调用 
- [x] 支持 `Model` 级别的数据跨表查询
- [ ] MySQL distinct 语法在分表统计不准确问题
- [ ] 测试用例

