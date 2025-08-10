@extends('admin.layout')

@section('title', '仪表板')
@section('page-title', '仪表板')

@section('content')
<div class="space-y-6">
    <!-- 站点信息 -->
    <div class="glass-card rounded-xl p-4 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">{{ $settings['site_name'] ?? 'AstroGuide' }}</h2>
            <p class="text-gray-400 text-sm mt-1">{{ $settings['site_description'] ?? '发现和整理您喜爱的网站' }}</p>
        </div>
        @if(!empty($settings['site_icon']))
        <img src="{{ $settings['site_icon'] }}" alt="站点图标" class="w-10 h-10 rounded object-cover" />
        @endif
    </div>
    <!-- 统计卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">总网站数</p>
                    <p class="text-3xl font-bold text-white">{{ $stats['total_sites'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">总分类数</p>
                    <p class="text-3xl font-bold text-white">{{ $stats['total_categories'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">总访问量</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($stats['total_visits']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">今日访问</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($stats['today_visits']) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- 热门网站和最近活动 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 热门网站 -->
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold">热门网站</h3>
                <a href="{{ route('admin.sites') }}" class="text-primary-500 hover:text-primary-400 text-sm">查看全部</a>
            </div>
            
            <div class="space-y-4">
                @foreach($popularSites as $site)
                <div class="flex items-center space-x-4 p-3 rounded-lg bg-gray-800/50">
                    <img src="{{ $site->logo }}" alt="{{ $site->name }}" class="w-8 h-8 rounded">
                    <div class="flex-1">
                        <p class="font-medium">{{ $site->name }}</p>
                        <p class="text-sm text-gray-400">{{ $site->category->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold">{{ number_format($site->visits) }}</p>
                        <p class="text-xs text-gray-400">访问</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- 最近添加 -->
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold">最近添加</h3>
                <a href="{{ route('admin.sites') }}" class="text-primary-500 hover:text-primary-400 text-sm">查看全部</a>
            </div>
            
            <div class="space-y-4">
                @foreach($recentSites as $site)
                <div class="flex items-center space-x-4 p-3 rounded-lg bg-gray-800/50">
                    <img src="{{ $site->logo }}" alt="{{ $site->name }}" class="w-8 h-8 rounded">
                    <div class="flex-1">
                        <p class="font-medium">{{ $site->name }}</p>
                        <p class="text-sm text-gray-400">{{ $site->category->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400">{{ $site->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection 