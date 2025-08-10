<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 注册应用服务
     */
    public function register()
    {
        //
    }

    /**
     * 启动应用服务
     */
    public function boot()
    {
        // 在开发环境下启用查询日志
        if (config('app.debug') && config('optimization.database.enable_query_log', false)) {
            DB::listen(function ($query) {
                $sql = $query->sql;
                $bindings = $query->bindings;
                $time = $query->time;
                
                // 如果查询时间过长，记录警告
                if ($time > config('optimization.database.max_query_time', 5000)) {
                    Log::warning('慢查询检测', [
                        'sql' => $sql,
                        'bindings' => $bindings,
                        'time' => $time . 'ms'
                    ]);
                }
                
                // 记录所有查询（仅在调试模式下）
                Log::info('数据库查询', [
                    'sql' => $sql,
                    'bindings' => $bindings,
                    'time' => $time . 'ms'
                ]);
            });
        }
    }
} 