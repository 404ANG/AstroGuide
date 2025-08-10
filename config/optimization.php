<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 性能优化配置
    |--------------------------------------------------------------------------
    |
    | 这里配置各种性能优化选项
    |
    */

    // 缓存配置
    'cache' => [
        // 启用查询缓存
        'enable_query_cache' => env('ENABLE_QUERY_CACHE', true),
        
        // 查询缓存时间（秒）
        'query_cache_ttl' => env('QUERY_CACHE_TTL', 1800),
        
        // 启用视图缓存
        'enable_view_cache' => env('ENABLE_VIEW_CACHE', true),
        
        // 启用路由缓存
        'enable_route_cache' => env('ENABLE_ROUTE_CACHE', true),
        
        // 启用配置缓存
        'enable_config_cache' => env('ENABLE_CONFIG_CACHE', true),
    ],

    // 数据库优化
    'database' => [
        // 启用查询日志
        'enable_query_log' => env('ENABLE_QUERY_LOG', true), // 开发环境启用
        
        // 最大查询时间（毫秒）
        'max_query_time' => env('MAX_QUERY_TIME', 5000),
        
        // 启用连接池
        'enable_connection_pool' => env('ENABLE_CONNECTION_POOL', false),
        
        // 连接池大小
        'connection_pool_size' => env('CONNECTION_POOL_SIZE', 10),
    ],

    // 前端优化
    'frontend' => [
        // 启用资源压缩
        'enable_compression' => env('ENABLE_COMPRESSION', true),
        
        // 启用资源合并
        'enable_concatenation' => env('ENABLE_CONCATENATION', true),
        
        // 启用懒加载
        'enable_lazy_loading' => env('ENABLE_LAZY_LOADING', true),
        
        // 启用预加载
        'enable_preloading' => env('ENABLE_PRELOADING', true),
    ],

    // 监控配置
    'monitoring' => [
        // 启用性能监控
        'enable_performance_monitoring' => env('ENABLE_PERFORMANCE_MONITORING', true),
        
        // 监控采样率（0-1）
        'monitoring_sampling_rate' => env('MONITORING_SAMPLING_RATE', 0.1),
        
        // 启用慢查询监控
        'enable_slow_query_monitoring' => env('ENABLE_SLOW_QUERY_MONITORING', true),
        
        // 慢查询阈值（毫秒）
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000),
    ],

    // 自动优化
    'auto_optimization' => [
        // 启用自动缓存清理
        'enable_auto_cache_cleanup' => env('ENABLE_AUTO_CACHE_CLEANUP', true),
        
        // 缓存清理间隔（小时）
        'cache_cleanup_interval' => env('CACHE_CLEANUP_INTERVAL', 24),
        
        // 启用自动索引优化
        'enable_auto_index_optimization' => env('ENABLE_AUTO_INDEX_OPTIMIZATION', false),
        
        // 启用自动统计更新
        'enable_auto_stats_update' => env('ENABLE_AUTO_STATS_UPDATE', true),
    ],
]; 