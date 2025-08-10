<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerformanceLog extends Model
{
    use HasFactory;

    /**
     * 可填充的属性
     */
    protected $fillable = [
        'route_name',
        'method',
        'url',
        'execution_time',
        'memory_usage',
        'query_count',
        'additional_data',
    ];

    /**
     * 类型转换
     */
    protected $casts = [
        'execution_time' => 'float',
        'memory_usage' => 'integer',
        'query_count' => 'integer',
        'additional_data' => 'array',
    ];

    /**
     * 获取性能统计
     */
    public static function getPerformanceStats($days = 7)
    {
        return static::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('
                AVG(execution_time) as avg_execution_time,
                MAX(execution_time) as max_execution_time,
                MIN(execution_time) as min_execution_time,
                AVG(memory_usage) as avg_memory_usage,
                AVG(query_count) as avg_query_count,
                COUNT(*) as total_requests
            ')
            ->first();
    }

    /**
     * 获取慢请求
     */
    public static function getSlowRequests($threshold = 1000, $limit = 10)
    {
        return static::where('execution_time', '>', $threshold)
            ->orderBy('execution_time', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取路由性能排名
     */
    public static function getRoutePerformanceRanking($days = 7, $limit = 10)
    {
        return static::where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('route_name')
            ->selectRaw('
                route_name,
                AVG(execution_time) as avg_execution_time,
                COUNT(*) as request_count,
                AVG(memory_usage) as avg_memory_usage,
                AVG(query_count) as avg_query_count
            ')
            ->groupBy('route_name')
            ->orderBy('avg_execution_time', 'desc')
            ->limit($limit)
            ->get();
    }
} 