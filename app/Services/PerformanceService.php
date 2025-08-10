<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\Site;
use App\Models\Category;
use App\Models\ActivityLog;
use App\Models\PerformanceLog;

class PerformanceService
{
    /**
     * 获取系统性能指标
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'cache_hit_rate' => $this->calculateCacheHitRate(),
            'db_queries' => $this->getAverageQueryCount(),
            'page_load_time' => $this->getAveragePageLoadTime(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
        ];
    }

    /**
     * 实时系统状态（CPU/内存/磁盘/系统信息）
     */
    public function getSystemStats(): array
    {
        $stats = [
            'php_version' => PHP_VERSION,
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'server_time' => date('Y-m-d H:i:s'),
            'cpu_load' => $this->getCpuLoad(),
            'memory' => $this->getMemoryDetails(),
            'disk' => $this->getDiskDetails(),
            'uptime' => $this->getUptime(),
        ];
        return $stats;
    }

    /**
     * 获取性能历史数据
     */
    public function getPerformanceHistory(int $hours = 24): array
    {
        $history = [];
        $now = time();
        
        // 生成历史数据点（每小时一个数据点）
        for ($i = $hours; $i >= 0; $i--) {
            $timestamp = $now - ($i * 3600);
            $time = date('H:i', $timestamp);
            
            // 模拟历史数据（实际项目中可以从数据库或缓存中获取）
            $cpuLoad = $this->generateHistoricalCpuLoad($i);
            $memoryUsage = $this->generateHistoricalMemoryUsage($i);
            $diskUsage = $this->generateHistoricalDiskUsage($i);
            
            $history[] = [
                'time' => $time,
                'timestamp' => $timestamp,
                'cpu_load' => $cpuLoad,
                'memory_usage' => $memoryUsage,
                'disk_usage' => $diskUsage,
            ];
        }
        
        return $history;
    }

    /**
     * 生成历史CPU负载数据
     */
    private function generateHistoricalCpuLoad(int $hoursAgo): float
    {
        // 模拟CPU负载变化：工作时间较高，夜间较低
        $hour = (date('H') - $hoursAgo + 24) % 24;
        $baseLoad = 0.3; // 基础负载
        
        // 工作时间（9-18点）负载较高
        if ($hour >= 9 && $hour <= 18) {
            $baseLoad += 0.4 + (sin($hour * 0.5) * 0.2);
        } else {
            $baseLoad += 0.1 + (sin($hour * 0.3) * 0.1);
        }
        
        // 添加随机波动
        $baseLoad += (mt_rand(-10, 10) / 100);
        
        return max(0.1, min(2.0, round($baseLoad, 2)));
    }

    /**
     * 生成历史内存使用数据
     */
    private function generateHistoricalMemoryUsage(int $hoursAgo): float
    {
        // 模拟内存使用率变化
        $hour = (date('H') - $hoursAgo + 24) % 24;
        $baseUsage = 45; // 基础使用率45%
        
        // 工作时间内存使用率较高
        if ($hour >= 9 && $hour <= 18) {
            $baseUsage += 20 + (sin($hour * 0.4) * 10);
        } else {
            $baseUsage += 5 + (sin($hour * 0.2) * 5);
        }
        
        // 添加随机波动
        $baseUsage += mt_rand(-5, 5);
        
        return max(30, min(85, round($baseUsage, 1)));
    }

    /**
     * 生成历史磁盘使用数据
     */
    private function generateHistoricalDiskUsage(int $hoursAgo): float
    {
        // 磁盘使用率相对稳定，缓慢增长
        $baseUsage = 60; // 基础使用率60%
        
        // 随时间缓慢增长
        $baseUsage += ($hoursAgo * 0.1);
        
        // 添加小幅随机波动
        $baseUsage += mt_rand(-2, 2);
        
        return max(55, min(75, round($baseUsage, 1)));
    }

    private function getCpuLoad(): array
    {
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : null;
        $one = $five = $fifteen = null;
        if (is_array($load) && count($load) >= 3) {
            [$one,$five,$fifteen] = $load;
        }
        return [
            '1m' => $one,
            '5m' => $five,
            '15m' => $fifteen,
        ];
    }

    private function getMemoryDetails(): array
    {
        // Linux: /proc/meminfo
        $total = $free = $available = null;
        if (is_readable('/proc/meminfo')) {
            $data = @file('/proc/meminfo');
            if ($data) {
                foreach ($data as $line) {
                    if (strpos($line, 'MemTotal:') === 0) { $total = (int) filter_var($line, FILTER_SANITIZE_NUMBER_INT) * 1024; }
                    if (strpos($line, 'MemAvailable:') === 0) { $available = (int) filter_var($line, FILTER_SANITIZE_NUMBER_INT) * 1024; }
                    if (strpos($line, 'MemFree:') === 0) { $free = (int) filter_var($line, FILTER_SANITIZE_NUMBER_INT) * 1024; }
                }
            }
        }
        // Fallback：无法获取总内存时，使用 PHP memory_limit 作为参考
        $used = null;
        if ($total) {
            $used = $total - ($available ?? $free ?? 0);
        } else {
            $limit = $this->parseIniBytes(ini_get('memory_limit'));
            $curr = memory_get_usage(true);
            $total = $limit ?: null;
            $used = $curr;
        }
        return [
            'total' => $total ? $this->formatBytes($total) : null,
            'used' => $used ? $this->formatBytes($used) : null,
            'free' => ($total && $used !== null) ? $this->formatBytes(max(0, $total - $used)) : ($free ? $this->formatBytes($free) : null),
            'usage_percent' => ($total && $used !== null && $total > 0) ? round($used / $total * 100, 1) : null,
        ];
    }

    private function getDiskDetails(): array
    {
        $path = storage_path();
        $total = @disk_total_space($path) ?: 0;
        $free = @disk_free_space($path) ?: 0;
        $used = max(0, $total - $free);
        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'usage_percent' => $total > 0 ? round($used / $total * 100, 1) : null,
        ];
    }

    private function getUptime(): ?string
    {
        // Linux /proc/uptime
        if (is_readable('/proc/uptime')) {
            $c = @file_get_contents('/proc/uptime');
            if ($c !== false) {
                $secs = (int) floatval(explode(' ', trim($c))[0] ?? 0);
                $d = intdiv($secs, 86400); $secs %= 86400;
                $h = intdiv($secs, 3600); $secs %= 3600;
                $m = intdiv($secs, 60);
                return sprintf('%d天 %d小时 %d分钟', $d, $h, $m);
            }
        }
        return null;
    }

    private function parseIniBytes($val): ?int
    {
        if ($val === false || $val === null || $val === '') return null;
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $num = (int)$val;
        switch ($last) {
            case 'g': $num *= 1024;
            // no break
            case 'm': $num *= 1024;
            // no break
            case 'k': $num *= 1024;
        }
        return $num;
    }

    /**
     * 获取缓存状态
     */
    public function getCacheStatus(): array
    {
        return [
            '应用缓存' => [
                'size' => $this->getCacheSize('framework/cache/data'),
                'status' => $this->isCacheWorking()
            ],
            '视图缓存' => [
                'size' => $this->getCacheSize('framework/views'),
                'status' => $this->isViewCacheWorking()
            ],
            '路由缓存' => [
                'size' => $this->getCacheSize('framework/cache'),
                'status' => $this->isRouteCacheWorking()
            ],
            '配置缓存' => [
                'size' => $this->getCacheSize('framework/cache'),
                'status' => $this->isConfigCacheWorking()
            ],
        ];
    }

    /**
     * 获取优化建议
     */
    public function getOptimizationTips(): array
    {
        $tips = [];
        
        // 检查数据库索引
        if (!$this->hasDatabaseIndexes()) {
            $tips[] = [
                'title' => '添加数据库索引',
                'description' => '建议为常用查询字段添加索引，提升查询性能',
                'priority' => 'high'
            ];
        }
        
        // 检查缓存配置
        if (!$this->isCacheOptimized()) {
            $tips[] = [
                'title' => '优化缓存配置',
                'description' => '当前缓存配置可能不够优化，建议调整缓存策略',
                'priority' => 'medium'
            ];
        }
        
        // 检查查询优化
        if ($this->hasSlowQueries()) {
            $tips[] = [
                'title' => '优化慢查询',
                'description' => '检测到慢查询，建议优化相关数据库查询',
                'priority' => 'high'
            ];
        }
        
        // 如果没有问题，显示优化状态
        if (empty($tips)) {
            $tips[] = [
                'title' => '系统运行良好',
                'description' => '当前系统性能良好，已实施多项优化措施',
                'priority' => 'low'
            ];
        }
        
        return $tips;
    }

    /**
     * 清理所有缓存
     */
    public function clearAllCaches(): bool
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            
            return true;
        } catch (\Exception $e) {
            \Log::error('清理缓存失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 预热缓存
     */
    public function warmupCache(): bool
    {
        try {
            // 预热常用数据
            $this->warmupDashboardCache();
            $this->warmupSiteCache();
            $this->warmupCategoryCache();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('预热缓存失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 执行性能优化
     */
    public function optimizePerformance(): bool
    {
        try {
            // 清理缓存
            $this->clearAllCaches();
            
            // 预热缓存
            $this->warmupCache();
            
            // 优化配置
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            
            return true;
        } catch (\Exception $e) {
            \Log::error('性能优化失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 计算缓存命中率
     */
    private function calculateCacheHitRate(): float
    {
        $hits = Cache::get('cache_hits', 0);
        $misses = Cache::get('cache_misses', 0);
        
        if ($hits + $misses === 0) {
            return 85.5; // 默认值
        }
        
        return round(($hits / ($hits + $misses)) * 100, 1);
    }

    /**
     * 获取平均查询数量
     */
    private function getAverageQueryCount(): int
    {
        try {
            $stats = PerformanceLog::getPerformanceStats(1); // 最近1天的数据
            return $stats ? round($stats->avg_query_count) : 12;
        } catch (\Exception $e) {
            return 12; // 默认值
        }
    }

    /**
     * 获取平均页面加载时间
     */
    private function getAveragePageLoadTime(): float
    {
        try {
            $stats = PerformanceLog::getPerformanceStats(1); // 最近1天的数据
            return $stats ? round($stats->avg_execution_time / 1000, 2) : 0.45; // 转换为秒
        } catch (\Exception $e) {
            return 0.45; // 默认值
        }
    }

    /**
     * 获取内存使用情况
     */
    private function getMemoryUsage(): string
    {
        $memory = memory_get_usage(true);
        return $this->formatBytes($memory);
    }

    /**
     * 获取磁盘使用情况
     */
    private function getDiskUsage(): string
    {
        $path = storage_path();
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;
        
        return $this->formatBytes($used);
    }

    /**
     * 获取缓存大小
     */
    private function getCacheSize(string $path): string
    {
        $fullPath = storage_path($path);
        
        if (!is_dir($fullPath)) {
            return '0 B';
        }
        
        $size = 0;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($fullPath));
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $this->formatBytes($size);
    }

    /**
     * 检查缓存是否工作
     */
    private function isCacheWorking(): bool
    {
        try {
            Cache::put('test_key', 'test_value', 1);
            $value = Cache::get('test_key');
            Cache::forget('test_key');
            return $value === 'test_value';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 检查视图缓存是否工作
     */
    private function isViewCacheWorking(): bool
    {
        return is_dir(storage_path('framework/views'));
    }

    /**
     * 检查路由缓存是否工作
     */
    private function isRouteCacheWorking(): bool
    {
        return file_exists(base_path('bootstrap/cache/routes-v7.php')) || file_exists(base_path('bootstrap/cache/routes.php'));
    }

    /**
     * 检查配置缓存是否工作
     */
    private function isConfigCacheWorking(): bool
    {
        return file_exists(base_path('bootstrap/cache/config.php'));
    }

    /**
     * 检查是否有数据库索引
     */
    private function hasDatabaseIndexes(): bool
    {
        try {
            $indexes = DB::select("PRAGMA index_list(activity_logs)");
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 检查缓存是否优化
     */
    private function isCacheOptimized(): bool
    {
        return config('cache.default') === 'file' && 
               config('session.driver') === 'file' && 
               config('queue.default') === 'sync';
    }

    /**
     * 检查是否有慢查询
     */
    private function hasSlowQueries(): bool
    {
        try {
            return PerformanceLog::where('execution_time', '>', 1000)
                ->where('created_at', '>=', now()->subHour())
                ->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取慢查询详情
     */
    public function getSlowQueriesDetails(): array
    {
        try {
            $slowQueries = PerformanceLog::where('execution_time', '>', 1000)
                ->where('created_at', '>=', now()->subDay())
                ->orderBy('execution_time', 'desc')
                ->limit(10)
                ->get();

            $details = [];
            foreach ($slowQueries as $query) {
                $details[] = [
                    'url' => $query->url,
                    'route_name' => $query->route_name,
                    'method' => $query->method,
                    'execution_time' => $query->execution_time,
                    'memory_usage' => $query->memory_usage,
                    'query_count' => $query->query_count,
                    'created_at' => $query->created_at->format('Y-m-d H:i:s'),
                    'optimization_suggestions' => $this->getQueryOptimizationSuggestions($query)
                ];
            }

            return $details;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取查询优化建议
     */
    private function getQueryOptimizationSuggestions($query): array
    {
        $suggestions = [];

        // 执行时间过长
        if ($query->execution_time > 5000) {
            $suggestions[] = [
                'type' => 'critical',
                'title' => '执行时间过长',
                'description' => '该请求执行时间超过5秒，建议检查数据库索引和查询优化',
                'action' => 'review_database_indexes'
            ];
        } elseif ($query->execution_time > 2000) {
            $suggestions[] = [
                'type' => 'warning',
                'title' => '执行时间较长',
                'description' => '该请求执行时间超过2秒，建议优化查询逻辑',
                'action' => 'optimize_query_logic'
            ];
        }

        // 内存使用过高
        if ($query->memory_usage > 50000000) { // 50MB
            $suggestions[] = [
                'type' => 'warning',
                'title' => '内存使用过高',
                'description' => '该请求内存使用超过50MB，建议检查是否有内存泄漏',
                'action' => 'check_memory_leaks'
            ];
        }

        // 查询次数过多
        if ($query->query_count > 100) {
            $suggestions[] = [
                'type' => 'warning',
                'title' => '数据库查询次数过多',
                'description' => '该请求执行了超过100次数据库查询，建议使用Eager Loading',
                'action' => 'use_eager_loading'
            ];
        }

        // 特定路由的优化建议
        if (str_contains($query->route_name, 'dashboard')) {
            $suggestions[] = [
                'type' => 'info',
                'title' => '仪表板优化',
                'description' => '仪表板页面建议使用缓存减少数据库查询',
                'action' => 'implement_caching'
            ];
        }

        if (str_contains($query->route_name, 'sites') || str_contains($query->route_name, 'categories')) {
            $suggestions[] = [
                'type' => 'info',
                'title' => '列表页面优化',
                'description' => '列表页面建议添加分页和索引优化',
                'action' => 'add_pagination_indexes'
            ];
        }

        return $suggestions;
    }

    /**
     * 获取数据库性能统计
     */
    public function getDatabasePerformanceStats(): array
    {
        try {
            $stats = [
                'total_queries' => PerformanceLog::where('created_at', '>=', now()->subDay())->sum('query_count'),
                'avg_execution_time' => PerformanceLog::where('created_at', '>=', now()->subDay())->avg('execution_time'),
                'slow_queries_count' => PerformanceLog::where('execution_time', '>', 1000)
                    ->where('created_at', '>=', now()->subDay())->count(),
                'memory_usage_avg' => PerformanceLog::where('created_at', '>=', now()->subDay())->avg('memory_usage'),
                'top_slow_routes' => $this->getTopSlowRoutes(),
                'database_indexes' => $this->getDatabaseIndexesInfo()
            ];

            return $stats;
        } catch (\Exception $e) {
            return [
                'total_queries' => 0,
                'avg_execution_time' => 0,
                'slow_queries_count' => 0,
                'memory_usage_avg' => 0,
                'top_slow_routes' => [],
                'database_indexes' => []
            ];
        }
    }

    /**
     * 获取最慢的路由
     */
    private function getTopSlowRoutes(): array
    {
        try {
            return PerformanceLog::selectRaw('route_name, AVG(execution_time) as avg_time, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDay())
                ->groupBy('route_name')
                ->orderBy('avg_time', 'desc')
                ->limit(5)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取数据库索引信息
     */
    private function getDatabaseIndexesInfo(): array
    {
        try {
            $tables = ['activity_logs', 'performance_logs', 'sites', 'categories'];
            $indexes = [];

            foreach ($tables as $table) {
                try {
                    $tableIndexes = DB::select("PRAGMA index_list($table)");
                    $indexes[$table] = count($tableIndexes);
                } catch (\Exception $e) {
                    $indexes[$table] = 0;
                }
            }

            return $indexes;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 预热仪表板缓存
     */
    private function warmupDashboardCache(): void
    {
        $cacheKey = 'dashboard_stats_' . date('Y-m-d');
        Cache::remember($cacheKey, 1800, function () {
            return [
                'total_sites' => Site::count(),
                'total_categories' => Category::count(),
                'total_visits' => ActivityLog::sum('visits'),
                'today_visits' => ActivityLog::whereDate('created_at', today())->sum('visits'),
            ];
        });
    }

    /**
     * 预热网站缓存
     */
    private function warmupSiteCache(): void
    {
        Cache::remember('popular_sites', 1800, function () {
            return Site::with('category')->orderBy('visits', 'desc')->limit(5)->get();
        });
        
        Cache::remember('recent_sites', 1800, function () {
            return Site::with('category')->orderBy('created_at', 'desc')->limit(5)->get();
        });
    }

    /**
     * 预热分类缓存
     */
    private function warmupCategoryCache(): void
    {
        Cache::remember('categories_with_count', 1800, function () {
            return Category::withCount('sites')->orderBy('sort_order')->get();
        });
    }

    /**
     * 格式化字节数
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 