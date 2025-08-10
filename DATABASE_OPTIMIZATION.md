# 数据库查询优化指南

## 慢查询检测与优化

### 检测到的慢查询问题

根据性能监控系统的分析，您的应用存在以下慢查询问题：

#### 1. 执行时间过长 (>5秒)
**问题描述：** 某些请求执行时间超过5秒，严重影响用户体验

**优化建议：**
- 检查数据库索引是否合理
- 优化查询语句，避免全表扫描
- 使用数据库查询缓存
- 考虑分页加载大量数据

#### 2. 执行时间较长 (>2秒)
**问题描述：** 请求执行时间在2-5秒之间，需要优化

**优化建议：**
- 使用Eager Loading避免N+1查询问题
- 优化JOIN查询
- 添加适当的数据库索引
- 使用查询缓存

#### 3. 内存使用过高 (>50MB)
**问题描述：** 单个请求内存使用超过50MB

**优化建议：**
- 检查是否有内存泄漏
- 优化大数据集的处理
- 使用流式处理替代一次性加载
- 及时释放不需要的变量

#### 4. 查询次数过多 (>100次)
**问题描述：** 单个请求执行了超过100次数据库查询

**优化建议：**
- 使用Eager Loading预加载关联数据
- 合并多个查询为单个查询
- 使用数据库事务减少连接开销
- 实现查询缓存机制

## 具体优化方案

### 1. 数据库索引优化

#### 建议添加的索引
```sql
-- 活动日志表索引
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);
CREATE INDEX idx_activity_logs_site_id ON activity_logs(site_id);

-- 性能日志表索引
CREATE INDEX idx_performance_logs_created_at ON performance_logs(created_at);
CREATE INDEX idx_performance_logs_execution_time ON performance_logs(execution_time);
CREATE INDEX idx_performance_logs_route_name ON performance_logs(route_name);

-- 网站表索引
CREATE INDEX idx_sites_category_id ON sites(category_id);
CREATE INDEX idx_sites_created_at ON sites(created_at);
CREATE INDEX idx_sites_visits ON sites(visits);

-- 分类表索引
CREATE INDEX idx_categories_sort_order ON categories(sort_order);
CREATE INDEX idx_categories_is_active ON categories(is_active);
```

### 2. 查询优化示例

#### 优化前（N+1查询问题）
```php
// 获取所有网站及其分类信息
$sites = Site::all();
foreach ($sites as $site) {
    echo $site->category->name; // 每次都会执行一次查询
}
```

#### 优化后（Eager Loading）
```php
// 一次性加载所有关联数据
$sites = Site::with('category')->get();
foreach ($sites as $site) {
    echo $site->category->name; // 不会产生额外查询
}
```

### 3. 缓存策略

#### 查询结果缓存
```php
// 缓存常用查询结果
$popularSites = Cache::remember('popular_sites', 1800, function () {
    return Site::with('category')
        ->orderBy('visits', 'desc')
        ->limit(10)
        ->get();
});
```

#### 页面缓存
```php
// 缓存整个页面响应
public function dashboard()
{
    return Cache::remember('dashboard_page', 300, function () {
        // 页面逻辑
        return view('admin.dashboard', $data);
    });
}
```

### 4. 分页优化

#### 大数据集分页
```php
// 使用游标分页替代偏移分页
$sites = Site::orderBy('id')
    ->cursorPaginate(20);

// 或者使用简单分页
$sites = Site::simplePaginate(20);
```

### 5. 数据库连接优化

#### 连接池配置
```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        PDO::ATTR_PERSISTENT => true, // 持久连接
    ]) : [],
],
```

## 监控与维护

### 1. 定期检查慢查询
- 使用性能监控系统定期检查慢查询
- 分析慢查询日志
- 优化频繁出现的慢查询

### 2. 数据库维护
```bash
# 定期优化数据库
php artisan db:optimize

# 清理过期日志
php artisan log:clear

# 重建索引
php artisan db:reindex
```

### 3. 性能测试
```bash
# 运行性能测试
php artisan test --filter=PerformanceTest

# 压力测试
php artisan test --filter=StressTest
```

## 常见问题解决

### 1. 内存泄漏
**症状：** 内存使用持续增长
**解决：** 
- 检查循环中的变量释放
- 使用生成器处理大数据集
- 及时关闭数据库连接

### 2. 连接池耗尽
**症状：** 数据库连接超时
**解决：**
- 增加连接池大小
- 使用连接复用
- 及时释放连接

### 3. 查询超时
**症状：** 查询执行时间过长
**解决：**
- 优化查询语句
- 添加适当索引
- 使用异步处理

## 最佳实践

### 1. 查询优化原则
- 只查询需要的字段
- 使用索引优化查询
- 避免在循环中查询数据库
- 合理使用缓存

### 2. 代码优化原则
- 使用Eager Loading
- 实现查询缓存
- 优化数据结构
- 定期清理无用数据

### 3. 监控原则
- 实时监控查询性能
- 定期分析慢查询
- 设置性能告警
- 持续优化改进

## 工具推荐

### 1. 查询分析工具
- Laravel Debugbar
- Laravel Telescope
- MySQL Slow Query Log
- Query Monitor

### 2. 性能测试工具
- Apache Bench (ab)
- Siege
- Artillery
- JMeter

### 3. 监控工具
- Laravel Horizon
- New Relic
- DataDog
- Prometheus + Grafana

通过实施这些优化措施，您的应用性能将得到显著提升，用户体验也会大大改善。 