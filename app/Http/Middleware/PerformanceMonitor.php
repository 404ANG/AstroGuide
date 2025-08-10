<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\PerformanceLog;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    /**
     * 处理传入的请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 记录请求开始时间
        $startTime = microtime(true);
        
        // 记录内存使用开始
        $startMemory = memory_get_usage();
        
        // 记录数据库查询数量开始
        $startQueries = $this->getQueryCount();
        
        // 继续处理请求
        $response = $next($request);
        
        // 计算性能指标
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        $endQueries = $this->getQueryCount();
        
        $executionTime = ($endTime - $startTime) * 1000; // 转换为毫秒
        $memoryUsage = $endMemory - $startMemory;
        $queryCount = $endQueries - $startQueries;
        
        // 记录性能数据
        $this->recordPerformance($request, $executionTime, $memoryUsage, $queryCount);
        
        // 记录到数据库（异步）
        $this->logToDatabase($request, $executionTime, $memoryUsage, $queryCount);
        
        // 如果执行时间过长，记录警告
        if ($executionTime > config('optimization.monitoring.slow_query_threshold', 1000)) {
            Log::warning('慢请求检测', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => $executionTime . 'ms',
                'memory_usage' => $this->formatBytes($memoryUsage),
                'query_count' => $queryCount
            ]);
        }
        
        return $response;
    }
    
    /**
     * 获取当前数据库查询数量
     */
    private function getQueryCount(): int
    {
        if (app()->bound('db')) {
            return count(\DB::getQueryLog());
        }
        return 0;
    }
    
    /**
     * 记录性能数据
     */
    private function recordPerformance(Request $request, float $executionTime, int $memoryUsage, int $queryCount): void
    {
        $route = $request->route();
        $routeName = $route ? $route->getName() : 'unknown';
        
        // 缓存性能数据
        $cacheKey = 'performance_' . md5($routeName . $request->method());
        
        $performanceData = Cache::get($cacheKey, [
            'count' => 0,
            'total_time' => 0,
            'total_memory' => 0,
            'total_queries' => 0,
            'min_time' => PHP_FLOAT_MAX,
            'max_time' => 0,
            'last_updated' => now()
        ]);
        
        // 更新统计数据
        $performanceData['count']++;
        $performanceData['total_time'] += $executionTime;
        $performanceData['total_memory'] += $memoryUsage;
        $performanceData['total_queries'] += $queryCount;
        $performanceData['min_time'] = min($performanceData['min_time'], $executionTime);
        $performanceData['max_time'] = max($performanceData['max_time'], $executionTime);
        $performanceData['last_updated'] = now();
        
        // 缓存1小时
        Cache::put($cacheKey, $performanceData, 3600);
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
    
    /**
     * 记录性能数据到数据库
     */
    private function logToDatabase(Request $request, float $executionTime, int $memoryUsage, int $queryCount): void
    {
        try {
            $route = $request->route();
            $routeName = $route ? $route->getName() : null;
            
            PerformanceLog::create([
                'route_name' => $routeName,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'execution_time' => $executionTime,
                'memory_usage' => $memoryUsage,
                'query_count' => $queryCount,
                'additional_data' => [
                    'user_agent' => $request->userAgent(),
                    'ip' => $request->ip(),
                    'referer' => $request->header('referer'),
                ]
            ]);
        } catch (\Exception $e) {
            // 静默处理错误，避免影响正常请求
            Log::error('记录性能日志失败: ' . $e->getMessage());
        }
    }
} 