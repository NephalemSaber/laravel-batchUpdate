# laravel批量更新mysql

## 环境要求
* PHP > 7.0
* composer > 2.0
* laravel > 5.5

# 安装
```
composer require NephalemSaber/laravel-batchUpdate -vvv
```

## 使用
```php
use Sabercode\LaravelBatchUpdate\BatchUpdate;
/*
 * string $table 表名
 * string $whereField 值不同条件字段名 如 id 
 * array $data 更新数据二维数组 必须包含条件字段 如
 * [
 *    ['id'=>1,'name'=>2,'age'=>3]
 *    ['id'=>2,'name'=>3,'age'=>4]
 * ]
 * array $whereSame 值相同条件字段数组一维数组 默认为空数组 如
 *    ['a'=>7,'b'=>'users']
 * */
BatchUpdate::doUpdate($table,$data,$whereField,$whereSame);
```

## License
[MIT](LICENSE)

