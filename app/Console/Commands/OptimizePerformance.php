<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use App\Models\Site;
use App\Models\Category;
use App\Models\ActivityLog;

class OptimizePerformance extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'app:optimize-performance';

    /**
     * 命令描述
     */
    protected $description = '优化应用性能：清理缓存、预热缓存、优化数据库';

    /**
     * 执行命令
     */
    public function handle()
    {
        $this->info('开始性能优化...');

        // 1. 清理所有缓存
        $this->info('清理缓存...');
        Cache::flush();
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');

        // 2. 预热缓存
        $this->info('预热缓存...');
        $this->warmupCache();

        // 3. 优化配置
        $this->info('优化配置...');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        $this->info('性能优化完成！');
    }

    /**
     * 预热缓存
     */
    private function warmupCache()
    {
        // 预热统计数据
        $stats = [
            'total_sites' => Site::count(),
            'total_categories' => Category::count(),
            'total_visits' => ActivityLog::sum('visits'),
            'today_visits' => ActivityLog::whereDate('created_at', today())->sum('visits'),
        ];
        Cache::put('dashboard_stats_' . date('Y-m-d'), $stats, 1800);

        // 预热热门网站
        $popularSites = Site::with('category')
            ->orderBy('visits', 'desc')
            ->limit(5)
            ->get();
        Cache::put('popular_sites', $popularSites, 1800);

        // 预热最近网站
        $recentSites = Site::with('category')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        Cache::put('recent_sites', $recentSites, 1800);

        // 预热分类数据
        $categories = Category::orderBy('sort_order')->get();
        Cache::put('categories_for_sites', $categories, 3600);

        $this->info('缓存预热完成');
    }
} 