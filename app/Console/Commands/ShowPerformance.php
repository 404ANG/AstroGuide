<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PerformanceLog;
use App\Services\PerformanceService;

class ShowPerformance extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'app:show-performance {--days=1 : 显示最近几天的数据}';

    /**
     * 命令描述
     */
    protected $description = '显示系统性能数据';

    /**
     * 执行命令
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("显示最近 {$days} 天的性能数据...\n");

        try {
            // 获取性能统计
            $stats = PerformanceLog::getPerformanceStats($days);
            
            if ($stats) {
                $this->info('📊 性能统计概览:');
                $this->table(
                    ['指标', '数值'],
                    [
                        ['总请求数', $stats->total_requests],
                        ['平均执行时间', round($stats->avg_execution_time, 2) . 'ms'],
                        ['最大执行时间', round($stats->max_execution_time, 2) . 'ms'],
                        ['最小执行时间', round($stats->min_execution_time, 2) . 'ms'],
                        ['平均内存使用', $stats->avg_memory_usage ? $this->formatBytes($stats->avg_memory_usage) : 'N/A'],
                        ['平均查询数量', $stats->avg_query_count ? round($stats->avg_query_count, 1) : 'N/A'],
                    ]
                );
            }

            // 获取慢请求
            $slowRequests = PerformanceLog::getSlowRequests(1000, 5);
            if ($slowRequests->count() > 0) {
                $this->warn("\n🐌 慢请求 (执行时间 > 1000ms):");
                $this->table(
                    ['路由', '方法', '执行时间', '内存使用', '查询数量', '时间'],
                    $slowRequests->map(function ($log) {
                        return [
                            $log->route_name ?? 'N/A',
                            $log->method,
                            round($log->execution_time, 2) . 'ms',
                            $this->formatBytes($log->memory_usage),
                            $log->query_count,
                            $log->created_at->format('Y-m-d H:i:s'),
                        ];
                    })->toArray()
                );
            }

            // 获取路由性能排名
            $routeRanking = PerformanceLog::getRoutePerformanceRanking($days, 5);
            if ($routeRanking->count() > 0) {
                $this->info("\n🏆 路由性能排名:");
                $this->table(
                    ['路由名称', '平均执行时间', '请求数量', '平均内存', '平均查询数'],
                    $routeRanking->map(function ($route) {
                        return [
                            $route->route_name,
                            round($route->avg_execution_time, 2) . 'ms',
                            $route->request_count,
                            $this->formatBytes($route->avg_memory_usage),
                            round($route->avg_query_count, 1),
                        ];
                    })->toArray()
                );
            }

            // 显示性能服务数据
            $this->info("\n🔧 系统性能指标:");
            $performanceService = new PerformanceService();
            $metrics = $performanceService->getPerformanceMetrics();
            $cacheStatus = $performanceService->getCacheStatus();
            
            $this->table(
                ['指标', '数值'],
                [
                    ['缓存命中率', $metrics['cache_hit_rate'] . '%'],
                    ['数据库查询', $metrics['db_queries']],
                    ['页面加载时间', $metrics['page_load_time'] . 's'],
                    ['内存使用', $metrics['memory_usage']],
                    ['磁盘使用', $metrics['disk_usage']],
                ]
            );

            $this->info("\n💾 缓存状态:");
            foreach ($cacheStatus as $name => $status) {
                $icon = $status['status'] ? '✅' : '❌';
                $this->line("  {$icon} {$name}: {$status['size']} - " . ($status['status'] ? '正常' : '异常'));
            }

        } catch (\Exception $e) {
            $this->error('获取性能数据失败: ' . $e->getMessage());
            return 1;
        }

        return 0;
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