@extends('admin.layout')

@section('title', '性能监控')

@section('content')
{{-- 
    服务器实时监控页面 - 动态可视化版本
    
    功能说明：
    1. 实时性能指标展示（缓存命中率、数据库查询、页面加载时间）
    2. 服务器实时监控（CPU、内存、磁盘使用情况）
    3. 动态图表展示（实时数据 + 历史趋势）
    4. 缓存状态监控
    5. 性能优化建议
    6. 一键优化操作
    
    技术实现：
    - 前端：Chart.js 图表库 + 轮询更新
    - 后端：PerformanceService 服务类
    - 数据：实时系统状态 + 模拟历史数据
    - 更新频率：5秒/次
--}}

<style>
/* 图表容器样式优化 */
.chart-container {
    position: relative;
    background: linear-gradient(135deg, rgba(31, 41, 55, 0.8) 0%, rgba(17, 24, 39, 0.8) 100%);
    border: 1px solid rgba(75, 85, 99, 0.2);
    border-radius: 0.75rem;
    overflow: hidden;
}

.chart-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.3), transparent);
}

/* 图表标题样式 */
.chart-title {
    font-weight: 600;
    color: #D1D5DB;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(75, 85, 99, 0.2);
}

/* 数据标签样式 */
.data-label {
    font-size: 0.75rem;
    color: #9CA3AF;
    font-weight: 500;
}

.data-value {
    font-size: 0.75rem;
    font-weight: 600;
}

/* 响应式图表高度 */
@media (max-width: 1024px) {
    .chart-container {
        height: 180px !important;
    }
}

@media (max-width: 768px) {
    .chart-container {
        height: 160px !important;
    }
}

/* 图表悬停效果 */
.chart-container:hover {
    border-color: rgba(59, 130, 246, 0.4);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

/* 加载动画 */
.chart-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #9CA3AF;
    font-size: 0.875rem;
}

.chart-loading::after {
    content: '';
    width: 20px;
    height: 20px;
    border: 2px solid #374151;
    border-top: 2px solid #3B82F6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-left: 0.5rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<div class="space-y-6">
    <!-- 性能指标概览 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">缓存命中率</p>
                    <p id="cache-hit-rate" class="text-2xl font-bold text-green-400">{{ number_format($metrics['cache_hit_rate'], 1) }}%</p>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">数据库查询</p>
                    <p id="db-queries" class="text-2xl font-bold text-blue-400">{{ $metrics['db_queries'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">页面加载时间</p>
                    <p id="page-load-time" class="text-2xl font-bold text-purple-400">{{ number_format($metrics['page_load_time'], 2) }}s</p>
                </div>
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- 服务器实时监控 - 动态可视化 -->
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold">服务器实时监控</h3>
            <div class="flex items-center space-x-4">
            <span id="sys-time" class="text-xs text-gray-400">{{ $systemStats['server_time'] ?? '' }}</span>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse" id="status-indicator"></div>
                    <span class="text-xs text-gray-400" id="status-text">实时更新中</span>
                </div>
            </div>
        </div>
        
        <!-- 实时图表区域 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- CPU 负载图表 -->
            <div class="chart-container p-4">
                <h4 class="chart-title">CPU 负载</h4>
                <div class="relative" style="height: 200px;">
                    <canvas id="cpuChart"></canvas>
                </div>
                <div class="mt-3 space-y-1">
                    <div class="flex justify-between">
                        <span class="data-label">1分钟:</span>
                        <span class="data-value text-green-400" id="cpu-1m">{{ $systemStats['cpu_load']['1m'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="data-label">5分钟:</span>
                        <span class="data-value text-yellow-400" id="cpu-5m">{{ $systemStats['cpu_load']['5m'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="data-label">15分钟:</span>
                        <span class="data-value text-red-400" id="cpu-15m">{{ $systemStats['cpu_load']['15m'] ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- 内存使用图表 -->
            <div class="chart-container p-4">
                <h4 class="chart-title">内存使用率</h4>
                <div class="relative" style="height: 200px;">
                    <canvas id="memoryChart"></canvas>
                </div>
                <div class="mt-3 space-y-1">
                    <div class="flex justify-between">
                        <span class="data-label">总计:</span>
                        <span class="data-value text-blue-400" id="mem-total">{{ $systemStats['memory']['total'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="data-label">已用:</span>
                        <span class="data-value text-orange-400" id="mem-used">{{ $systemStats['memory']['used'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="data-label">使用率:</span>
                        <span class="data-value text-purple-400" id="mem-percent">{{ $systemStats['memory']['usage_percent'] ?? '-' }}%</span>
                    </div>
                </div>
            </div>

            <!-- 磁盘使用图表 -->
            <div class="chart-container p-4">
                <h4 class="chart-title">磁盘使用率</h4>
                <div class="relative" style="height: 200px;">
                    <canvas id="diskChart"></canvas>
                </div>
                <div class="mt-3 space-y-1">
                    <div class="flex justify-between">
                        <span class="data-label">总计:</span>
                        <span class="data-value text-blue-400" id="disk-total">{{ $systemStats['disk']['total'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="data-label">已用:</span>
                        <span class="data-value text-orange-400" id="disk-used">{{ $systemStats['disk']['used'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="data-label">使用率:</span>
                        <span class="data-value text-purple-400" id="disk-percent">{{ $systemStats['disk']['usage_percent'] ?? '-' }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 系统信息 -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-800/30 rounded-lg">
            <div class="text-center">
                <p class="text-xs text-gray-400">操作系统</p>
                <p class="text-sm font-medium">{{ $systemStats['os'] ?? php_uname('s') }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-400">PHP 版本</p>
                <p class="text-sm font-medium">{{ $systemStats['php_version'] ?? PHP_VERSION }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-400">运行时间</p>
                <p class="text-sm font-medium" id="uptime">{{ $systemStats['uptime'] ?? '-' }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-400">更新频率</p>
                <p class="text-sm font-medium">5秒</p>
            </div>
        </div>
    </div>

    <!-- 历史趋势图表 -->
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold">历史趋势分析</h3>
            <div class="flex items-center space-x-4">
                <select id="historyRange" class="px-3 py-1 bg-gray-800 border border-gray-600 rounded text-sm">
                    <option value="6">最近6小时</option>
                    <option value="12">最近12小时</option>
                    <option value="24" selected>最近24小时</option>
                    <option value="48">最近48小时</option>
                    <option value="168">最近7天</option>
                </select>
                <button id="refreshHistory" class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm transition-colors">
                    刷新历史数据
                </button>
            </div>
        </div>
        
        <!-- 历史趋势图表容器 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="chart-container p-4">
                <h4 class="chart-title">CPU负载趋势</h4>
                <div class="relative" style="height: 250px;">
                    <canvas id="cpuHistoryChart"></canvas>
                </div>
            </div>
            <div class="chart-container p-4">
                <h4 class="chart-title">内存使用趋势</h4>
                <div class="relative" style="height: 250px;">
                    <canvas id="memoryHistoryChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="mt-6 chart-container p-4">
            <h4 class="chart-title">磁盘使用趋势</h4>
            <div class="relative" style="height: 200px;">
                <canvas id="diskHistoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 慢查询分析 -->
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold">慢查询分析</h3>
            <div class="flex items-center space-x-4">
                <button id="refreshSlowQueries" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-sm transition-colors">
                    刷新慢查询数据
                </button>
                <button id="analyzeDatabase" class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm transition-colors">
                    数据库性能分析
                </button>
                <button id="optimizeIndexes" class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded text-sm transition-colors">
                    优化数据库索引
                </button>
            </div>
        </div>
        
        <!-- 慢查询统计卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-800/50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">总查询数</p>
                        <p id="total-queries" class="text-2xl font-bold text-blue-400">-</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-800/50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">慢查询数</p>
                        <p id="slow-queries-count" class="text-2xl font-bold text-red-400">-</p>
                    </div>
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-800/50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">平均执行时间</p>
                        <p id="avg-execution-time" class="text-2xl font-bold text-yellow-400">-</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-800/50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">平均内存使用</p>
                        <p id="avg-memory-usage" class="text-2xl font-bold text-purple-400">-</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 慢查询详情表格 -->
        <div class="bg-gray-800/50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-300 mb-3">最近慢查询详情</h4>
            <div id="slow-queries-table" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left py-2 text-gray-400">路由</th>
                            <th class="text-left py-2 text-gray-400">执行时间</th>
                            <th class="text-left py-2 text-gray-400">内存使用</th>
                            <th class="text-left py-2 text-gray-400">查询次数</th>
                            <th class="text-left py-2 text-gray-400">时间</th>
                            <th class="text-left py-2 text-gray-400">优化建议</th>
                        </tr>
                    </thead>
                    <tbody id="slow-queries-tbody">
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500">加载中...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 缓存状态 -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-lg font-bold mb-4">缓存状态</h3>
        <div id="cache-status-container" class="space-y-4">
            @foreach($cacheStatus as $key => $status)
            <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg">
                <span class="text-sm">{{ $key }}</span>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-400">{{ $status['size'] }}</span>
                    <span class="px-2 py-1 text-xs rounded-full {{ $status['status'] ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                        {{ $status['status'] ? '正常' : '异常' }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- 性能优化建议 -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-lg font-bold mb-4">性能优化建议</h3>
        <div id="optimization-tips-container" class="space-y-3">
            @foreach($optimizationTips as $tip)
            <div class="flex items-start space-x-3 p-3 bg-gray-800/50 rounded-lg">
                <div class="p-1 bg-blue-500/20 rounded-full mt-0.5">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium">{{ $tip['title'] }}</p>
                    <p class="text-xs text-gray-400">{{ $tip['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- 操作按钮 -->
    <div class="flex space-x-4">
        <button onclick="clearCache()" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">
            清理缓存
        </button>
        <button onclick="warmupCache()" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
            预热缓存
        </button>
        <button onclick="optimizePerformance()" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors">
            性能优化
        </button>
    </div>
</div>

<!-- 引入 Chart.js 图表库 -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// 图表配置和初始化
let cpuChart, memoryChart, diskChart;
let cpuData = [], memoryData = [], diskData = [];
let timeLabels = [];

// 历史图表
let cpuHistoryChart, memoryHistoryChart, diskHistoryChart;

// 图表加载状态
let chartsLoaded = false;

// 显示图表加载状态
function showChartLoading(chartId) {
    const container = document.getElementById(chartId).parentElement;
    if (container) {
        container.innerHTML = '<div class="chart-loading">加载图表数据中...</div>';
    }
}

// 隐藏图表加载状态
function hideChartLoading(chartId) {
    const container = document.getElementById(chartId).parentElement;
    if (container && container.querySelector('.chart-loading')) {
        container.innerHTML = `<canvas id="${chartId}"></canvas>`;
    }
}

// 初始化图表
function initCharts() {
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#374151',
                borderWidth: 1,
                cornerRadius: 6,
                displayColors: false
            }
        },
        scales: {
            x: {
                display: false,
                grid: { display: false }
            },
            y: {
                display: true,
                position: 'right',
                grid: { 
                    color: 'rgba(75, 85, 99, 0.1)',
                    drawBorder: false
                },
                ticks: { 
                    color: '#9CA3AF',
                    font: { size: 10 },
                    maxTicksLimit: 5,
                    callback: function(value) {
                        return value.toFixed(1);
                    }
                },
                border: { display: false }
            }
        },
        elements: {
            point: { 
                radius: 2, 
                hoverRadius: 4,
                backgroundColor: '#10B981'
            },
            line: { 
                tension: 0.4,
                borderWidth: 2
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    };

    // CPU 负载图表
    const cpuCtx = document.getElementById('cpuChart').getContext('2d');
    cpuChart = new Chart(cpuCtx, {
        type: 'line',
        data: {
            labels: timeLabels,
            datasets: [{
                label: '1分钟负载',
                data: cpuData,
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: chartOptions
    });

    // 内存使用图表
    const memoryCtx = document.getElementById('memoryChart').getContext('2d');
    memoryChart = new Chart(memoryCtx, {
        type: 'line',
        data: {
            labels: timeLabels,
            datasets: [{
                label: '内存使用率',
                data: memoryData,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                ...chartOptions.scales,
                y: {
                    ...chartOptions.scales.y,
                    ticks: { 
                        ...chartOptions.scales.y.ticks,
                        callback: function(value) {
                            return value.toFixed(0) + '%';
                        }
                    }
                }
            }
        }
    });

    // 磁盘使用图表
    const diskCtx = document.getElementById('diskChart').getContext('2d');
    diskChart = new Chart(diskCtx, {
        type: 'line',
        data: {
            labels: timeLabels,
            datasets: [{
                label: '磁盘使用率',
                data: diskData,
                borderColor: '#8B5CF6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                ...chartOptions.scales,
                y: {
                    ...chartOptions.scales.y,
                    ticks: { 
                        ...chartOptions.scales.y.ticks,
                        callback: function(value) {
                            return value.toFixed(0) + '%';
                        }
                    }
                }
            }
        }
    });
}

// 初始化历史图表
function initHistoryCharts() {
    const historyChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#374151',
                borderWidth: 1,
                cornerRadius: 6,
                displayColors: false
            }
        },
        scales: {
            x: {
                display: true,
                grid: { 
                    color: 'rgba(75, 85, 99, 0.1)',
                    drawBorder: false
                },
                ticks: { 
                    color: '#9CA3AF', 
                    maxTicksLimit: 8,
                    font: { size: 10 }
                },
                border: { display: false }
            },
            y: {
                display: true,
                position: 'right',
                grid: { 
                    color: 'rgba(75, 85, 99, 0.1)',
                    drawBorder: false
                },
                ticks: { 
                    color: '#9CA3AF',
                    font: { size: 10 },
                    maxTicksLimit: 6,
                    callback: function(value) {
                        return value.toFixed(1);
                    }
                },
                border: { display: false }
            }
        },
        elements: {
            point: { 
                radius: 3, 
                hoverRadius: 5,
                backgroundColor: '#10B981'
            },
            line: { 
                tension: 0.3,
                borderWidth: 2
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    };

    // CPU历史图表
    const cpuHistoryCtx = document.getElementById('cpuHistoryChart').getContext('2d');
    cpuHistoryChart = new Chart(cpuHistoryCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'CPU负载',
                data: [],
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: historyChartOptions
    });

    // 内存历史图表
    const memoryHistoryCtx = document.getElementById('memoryHistoryChart').getContext('2d');
    memoryHistoryChart = new Chart(memoryHistoryCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: '内存使用率',
                data: [],
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            ...historyChartOptions,
            scales: {
                ...historyChartOptions.scales,
                y: {
                    ...historyChartOptions.scales.y,
                    ticks: { 
                        ...historyChartOptions.scales.y.ticks,
                        callback: function(value) {
                            return value.toFixed(0) + '%';
                        }
                    }
                }
            }
        }
    });

    // 磁盘历史图表
    const diskHistoryCtx = document.getElementById('diskHistoryChart').getContext('2d');
    diskHistoryChart = new Chart(diskHistoryCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: '磁盘使用率',
                data: [],
                borderColor: '#8B5CF6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            ...historyChartOptions,
            scales: {
                ...historyChartOptions.scales,
                y: {
                    ...historyChartOptions.scales.y,
                    ticks: { 
                        ...historyChartOptions.scales.y.ticks,
                        callback: function(value) {
                            return value.toFixed(0) + '%';
                        }
                    }
                }
            }
        }
    });
}

// 加载历史数据
function loadHistoryData(hours = 24) {
    fetch(`{{ route('admin.performance.history') }}?hours=${hours}`)
        .then(response => response.json())
        .then(data => {
            const labels = data.map(item => item.time);
            const cpuData = data.map(item => item.cpu_load);
            const memoryData = data.map(item => item.memory_usage);
            const diskData = data.map(item => item.disk_usage);

            // 更新CPU历史图表
            cpuHistoryChart.data.labels = labels;
            cpuHistoryChart.data.datasets[0].data = cpuData;
            cpuHistoryChart.update();

            // 更新内存历史图表
            memoryHistoryChart.data.labels = labels;
            memoryHistoryChart.data.datasets[0].data = memoryData;
            memoryHistoryChart.update();

            // 更新磁盘历史图表
            diskHistoryChart.data.labels = labels;
            diskHistoryChart.data.datasets[0].data = diskData;
            diskHistoryChart.update();
        })
        .catch(error => {
            console.error('加载历史数据失败:', error);
        });
}

// 更新图表数据
function updateCharts(data) {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('zh-CN', { hour12: false });
    
    // 添加新数据点
    timeLabels.push(timeStr);
    cpuData.push(parseFloat(data.cpu_load?.['1m'] || 0));
    memoryData.push(parseFloat(data.memory?.usage_percent || 0));
    diskData.push(parseFloat(data.disk?.usage_percent || 0));
    
    // 保持最近30个数据点
    if (timeLabels.length > 30) {
        timeLabels.shift();
        cpuData.shift();
        memoryData.shift();
        diskData.shift();
    }
    
    // 更新图表
    cpuChart.data.labels = timeLabels;
    cpuChart.data.datasets[0].data = cpuData;
    cpuChart.update('none');
    
    memoryChart.data.labels = timeLabels;
    memoryChart.data.datasets[0].data = memoryData;
    memoryChart.update('none');
    
    diskChart.data.labels = timeLabels;
    diskChart.data.datasets[0].data = diskData;
    diskChart.update('none');
}

// 更新状态指示器
function updateStatusIndicator(success) {
    const indicator = document.getElementById('status-indicator');
    const statusText = document.getElementById('status-text');
    
    if (success) {
        indicator.className = 'w-2 h-2 bg-green-400 rounded-full animate-pulse';
        statusText.textContent = '实时更新中';
        statusText.className = 'text-xs text-gray-400';
    } else {
        indicator.className = 'w-2 h-2 bg-red-400 rounded-full';
        statusText.textContent = '连接异常';
        statusText.className = 'text-xs text-red-400';
    }
}

// 缓存操作函数
function clearCache() {
    if (confirm('确定要清理所有缓存吗？')) {
        // 显示加载状态
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '清理中...';
        button.disabled = true;
        
        fetch('/admin/performance/clear-cache', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 显示成功提示
                    showNotification('缓存清理成功！', 'success');
                    // 刷新缓存状态数据
                    refreshCacheStatus();
                    // 刷新性能指标
                    refreshPerformanceMetrics();
                } else {
                    showNotification('缓存清理失败：' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('操作失败：网络错误', 'error');
            })
            .finally(() => {
                // 恢复按钮状态
                button.textContent = originalText;
                button.disabled = false;
            });
    }
}

function warmupCache() {
    if (confirm('确定要预热缓存吗？')) {
        // 显示加载状态
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '预热中...';
        button.disabled = true;
        
        fetch('/admin/performance/warmup-cache', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('缓存预热成功！', 'success');
                    // 刷新缓存状态数据
                    refreshCacheStatus();
                    // 刷新性能指标
                    refreshPerformanceMetrics();
                } else {
                    showNotification('缓存预热失败：' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('操作失败：网络错误', 'error');
            })
            .finally(() => {
                // 恢复按钮状态
                button.textContent = originalText;
                button.disabled = false;
            });
    }
}

function optimizePerformance() {
    if (confirm('确定要执行性能优化吗？')) {
        // 显示加载状态
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '优化中...';
        button.disabled = true;
        
        fetch('/admin/performance/optimize', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('性能优化完成！', 'success');
                    // 刷新缓存状态数据
                    refreshCacheStatus();
                    // 刷新性能指标
                    refreshPerformanceMetrics();
                    // 刷新优化建议
                    refreshOptimizationTips();
                } else {
                    showNotification('性能优化失败：' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('操作失败：网络错误', 'error');
            })
            .finally(() => {
                // 恢复按钮状态
                button.textContent = originalText;
                button.disabled = false;
            });
    }
}

// 显示通知消息
function showNotification(message, type = 'info') {
    // 创建通知元素
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    // 添加到页面
    document.body.appendChild(notification);
    
    // 显示动画
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // 自动隐藏
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// 刷新缓存状态
function refreshCacheStatus() {
    fetch('{{ route('admin.performance.cache-status') }}')
        .then(response => response.json())
        .then(data => {
            // 更新缓存状态显示
            updateCacheStatusDisplay(data);
        })
        .catch(error => {
            console.error('刷新缓存状态失败:', error);
        });
}

// 刷新性能指标
function refreshPerformanceMetrics() {
    fetch('{{ route('admin.performance.metrics') }}')
        .then(response => response.json())
        .then(data => {
            // 更新性能指标显示
            updateMetricsDisplay(data);
        })
        .catch(error => {
            console.error('刷新性能指标失败:', error);
        });
}

// 刷新优化建议
function refreshOptimizationTips() {
    fetch('{{ route('admin.performance.optimization-tips') }}')
        .then(response => response.json())
        .then(data => {
            // 更新优化建议显示
            updateOptimizationTipsDisplay(data);
        })
        .catch(error => {
            console.error('刷新优化建议失败:', error);
        });
}

// 加载慢查询数据
function loadSlowQueriesData() {
    fetch('{{ route('admin.performance.slow-queries') }}')
        .then(response => response.json())
        .then(data => {
            updateSlowQueriesTable(data);
        })
        .catch(error => {
            console.error('加载慢查询数据失败:', error);
        });
}

// 加载数据库性能统计
function loadDatabaseStats() {
    fetch('{{ route('admin.performance.database-stats') }}')
        .then(response => response.json())
        .then(data => {
            updateDatabaseStats(data);
        })
        .catch(error => {
            console.error('加载数据库统计失败:', error);
        });
}

// 优化数据库索引
function optimizeDatabaseIndexes() {
    if (confirm('确定要优化数据库索引吗？这可能需要一些时间。')) {
        // 显示加载状态
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '优化中...';
        button.disabled = true;
        
        fetch('/admin/performance/optimize-indexes', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('数据库索引优化成功！', 'success');
                    // 刷新缓存状态数据
                    refreshCacheStatus();
                    // 刷新性能指标
                    refreshPerformanceMetrics();
                    // 刷新优化建议
                    refreshOptimizationTips();
                } else {
                    showNotification('数据库索引优化失败：' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('操作失败：网络错误', 'error');
            })
            .finally(() => {
                // 恢复按钮状态
                button.textContent = originalText;
                button.disabled = false;
            });
    }
}

// 更新慢查询表格
function updateSlowQueriesTable(slowQueries) {
    const tbody = document.getElementById('slow-queries-tbody');
    if (!tbody) return;
    
    if (slowQueries.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">暂无慢查询数据</td></tr>';
        return;
    }
    
    let html = '';
    slowQueries.forEach(query => {
        const executionTime = (query.execution_time / 1000).toFixed(2);
        const memoryUsage = (query.memory_usage / 1024 / 1024).toFixed(1);
        
        html += `
            <tr class="border-b border-gray-700 hover:bg-gray-800/30">
                <td class="py-2">
                    <div class="text-xs text-gray-300">${query.route_name}</div>
                    <div class="text-xs text-gray-500">${query.method} ${query.url}</div>
                </td>
                <td class="py-2">
                    <span class="px-2 py-1 text-xs rounded-full ${executionTime > 5 ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400'}">
                        ${executionTime}s
                    </span>
                </td>
                <td class="py-2 text-xs text-gray-300">${memoryUsage} MB</td>
                <td class="py-2 text-xs text-gray-300">${query.query_count}</td>
                <td class="py-2 text-xs text-gray-400">${query.created_at}</td>
                <td class="py-2">
                    ${query.optimization_suggestions.map(suggestion => `
                        <div class="text-xs mb-1">
                            <span class="px-1 py-0.5 rounded text-xs ${
                                suggestion.type === 'critical' ? 'bg-red-500/20 text-red-400' :
                                suggestion.type === 'warning' ? 'bg-yellow-500/20 text-yellow-400' :
                                'bg-blue-500/20 text-blue-400'
                            }">${suggestion.title}</span>
                        </div>
                    `).join('')}
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// 更新数据库统计
function updateDatabaseStats(stats) {
    // 更新统计卡片
    document.getElementById('total-queries').textContent = stats.total_queries.toLocaleString();
    document.getElementById('slow-queries-count').textContent = stats.slow_queries_count;
    document.getElementById('avg-execution-time').textContent = (stats.avg_execution_time / 1000).toFixed(2) + 's';
    document.getElementById('avg-memory-usage').textContent = (stats.memory_usage_avg / 1024 / 1024).toFixed(1) + ' MB';
}

// 更新缓存状态显示
function updateCacheStatusDisplay(cacheStatus) {
    const container = document.getElementById('cache-status-container');
    if (!container) return;
    
    // 清空现有内容
    container.innerHTML = '';
    
    // 重新生成缓存状态内容
    Object.entries(cacheStatus).forEach(([key, status]) => {
        const statusHtml = `
            <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg">
                <span class="text-sm">${key}</span>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-400">${status.size}</span>
                    <span class="px-2 py-1 text-xs rounded-full ${status.status ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'}">
                        ${status.status ? '正常' : '异常'}
                    </span>
                </div>
            </div>
        `;
        container.innerHTML += statusHtml;
    });
}

// 更新性能指标显示
function updateMetricsDisplay(metrics) {
    // 更新缓存命中率
    const cacheHitRateElement = document.getElementById('cache-hit-rate');
    if (cacheHitRateElement) {
        cacheHitRateElement.textContent = `${metrics.cache_hit_rate.toFixed(1)}%`;
    }
    
    // 更新数据库查询
    const dbQueriesElement = document.getElementById('db-queries');
    if (dbQueriesElement) {
        dbQueriesElement.textContent = metrics.db_queries;
    }
    
    // 更新页面加载时间
    const pageLoadTimeElement = document.getElementById('page-load-time');
    if (pageLoadTimeElement) {
        pageLoadTimeElement.textContent = `${metrics.page_load_time.toFixed(2)}s`;
    }
}

// 更新优化建议显示
function updateOptimizationTipsDisplay(tips) {
    const container = document.getElementById('optimization-tips-container');
    if (!container) return;
    
    // 清空现有内容
    container.innerHTML = '';
    
    // 重新生成优化建议内容
    tips.forEach(tip => {
        const tipHtml = `
            <div class="flex items-start space-x-3 p-3 bg-gray-800/50 rounded-lg">
                <div class="p-1 bg-blue-500/20 rounded-full mt-0.5">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium">${tip.title}</p>
                    <p class="text-xs text-gray-400">${tip.description}</p>
                </div>
            </div>
        `;
        container.innerHTML += tipHtml;
    });
}

// 实时数据轮询
function startRealTimeMonitoring() {
    const updateData = () => {
        fetch('{{ route('admin.performance.system-stats') }}', { 
            headers: { 'Accept': 'application/json' } 
        })
        .then(response => response.json())
        .then(data => {
            // 更新文本显示
            document.getElementById('sys-time').textContent = data.server_time || '';
            document.getElementById('cpu-1m').textContent = (data.cpu_load && data.cpu_load['1m'] !== null) ? data.cpu_load['1m'] : '-';
            document.getElementById('cpu-5m').textContent = (data.cpu_load && data.cpu_load['5m'] !== null) ? data.cpu_load['5m'] : '-';
            document.getElementById('cpu-15m').textContent = (data.cpu_load && data.cpu_load['15m'] !== null) ? data.cpu_load['15m'] : '-';
            document.getElementById('mem-total').textContent = (data.memory && data.memory.total) || '-';
            document.getElementById('mem-used').textContent = (data.memory && data.memory.used) || '-';
            document.getElementById('mem-percent').textContent = (data.memory && (data.memory.usage_percent ?? '-')) + '%';
            document.getElementById('disk-total').textContent = (data.disk && data.disk.total) || '-';
            document.getElementById('disk-used').textContent = (data.disk && data.disk.used) || '-';
            document.getElementById('disk-percent').textContent = (data.disk && (data.disk.usage_percent ?? '-')) + '%';
            document.getElementById('uptime').textContent = data.uptime || '-';
            
            // 更新图表
            updateCharts(data);
            
            // 更新状态指示器
            updateStatusIndicator(true);
        })
        .catch(error => {
            console.error('获取系统状态失败:', error);
            updateStatusIndicator(false);
        });
    };
    
    // 立即执行一次
    updateData();
    
    // 每5秒更新一次
    setInterval(updateData, 5000);
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    // 显示加载状态
    console.log('正在初始化性能监控系统...');
    
    // 初始化图表
    try {
        initCharts();
        initHistoryCharts(); // 初始化历史图表
        chartsLoaded = true;
        console.log('图表初始化完成');
    } catch (error) {
        console.error('图表初始化失败:', error);
    }
    
    // 加载默认历史数据
    loadHistoryData();

    // 开始实时监控
    startRealTimeMonitoring();
    
    // 加载慢查询数据
    loadSlowQueriesData();
    loadDatabaseStats();
    
    // 添加历史数据范围选择事件监听器
    document.getElementById('historyRange').addEventListener('change', function() {
        const hours = parseInt(this.value);
        loadHistoryData(hours);
    });
    
    // 添加刷新历史数据按钮事件监听器
    document.getElementById('refreshHistory').addEventListener('click', function() {
        const hours = parseInt(document.getElementById('historyRange').value);
        loadHistoryData(hours);
        
        // 显示刷新提示
        this.textContent = '刷新中...';
        this.disabled = true;
        setTimeout(() => {
            this.textContent = '刷新历史数据';
            this.disabled = false;
        }, 1000);
    });

    // 刷新慢查询数据按钮事件监听器
    document.getElementById('refreshSlowQueries').addEventListener('click', function() {
        loadSlowQueriesData();
        this.textContent = '刷新中...';
        this.disabled = true;
        setTimeout(() => {
            this.textContent = '刷新慢查询数据';
            this.disabled = false;
        }, 1000);
    });

    // 刷新数据库性能统计按钮事件监听器
    document.getElementById('analyzeDatabase').addEventListener('click', function() {
        loadDatabaseStats();
        this.textContent = '刷新中...';
        this.disabled = true;
        setTimeout(() => {
            this.textContent = '数据库性能分析';
            this.disabled = false;
        }, 1000);
    });

    // 优化数据库索引按钮事件监听器
    document.getElementById('optimizeIndexes').addEventListener('click', function() {
        if (confirm('确定要优化数据库索引吗？这可能需要一些时间。')) {
            optimizeDatabaseIndexes();
            this.textContent = '优化中...';
            this.disabled = true;
            setTimeout(() => {
                this.textContent = '优化数据库索引';
                this.disabled = false;
            }, 5000); // 给更多时间，因为索引优化可能需要较长时间
        }
    });
    
    // 初始化完成提示
    setTimeout(() => {
        if (chartsLoaded) {
            showNotification('性能监控系统已就绪', 'success');
        }
    }, 2000);
});
</script>
@endsection 