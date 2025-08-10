@extends('admin.layout')

@section('title', '活跃统计')
@section('page-title', '活跃天数统计')

@section('content')
<div class="space-y-6">
    <!-- 活跃天数图表（固定宽度 915px） -->
    <div class="glass-card rounded-xl p-4">
        <h3 class="text-base font-bold mb-2">活跃天数统计</h3>
        @php
            use Carbon\Carbon;
            use Carbon\CarbonInterface;
            $rows = 7; $weeks = 53;
            $end = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
            $start = $end->copy()->subWeeks($weeks - 1);
            // 顶部月份标签
            $monthLabels = [];
            for ($c = 0; $c < $weeks; $c++) {
                $colStart = $start->copy()->addWeeks($c);
                $label = '';
                for ($r = 0; $r < $rows; $r++) {
                    $d = $colStart->copy()->addDays($r);
                    if ($d->day === 1) { $label = $d->format('M'); break; }
                }
                if ($c > 0 && $label !== '' && (end($monthLabels)['text'] ?? '') === $label) { $label = ''; }
                $monthLabels[] = ['text' => $label];
            }
            // 左侧星期标签
            $weekdayMap = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
            $leftLabels = [];
            for ($r = 0; $r < $rows; $r++) {
                $w = $weekdayMap[$r];
                $leftLabels[] = in_array($w, ['Mon','Wed','Fri']) ? substr($w,0,1).strtolower(substr($w,1)) : '';
            }
        @endphp
        <div class="flex">
            <!-- 网格区域（固定宽度 915px） -->
            <div class="flex flex-col" style="width: 915px;">
                <!-- 月份标签（顶部） -->
                <div class="flex items-center" style="margin-left: 28px;">
                    <div class="flex gap-1">
                        @foreach($monthLabels as $ml)
                            <div class="w-3 h-4 text-[10px] text-gray-400">{{ $ml['text'] }}</div>
                        @endforeach
                    </div>
                </div>
                <!-- 网格主体（中部） -->
                <div class="flex mt-1" style="height: 200px;">
                    <div class="flex flex-col justify-between mr-1" style="width: 28px;">
                        @foreach($leftLabels as $text)
                            <div class="h-3.5 flex items-center justify-end pr-1 text-[10px] text-gray-400">{{ $text }}</div>
                        @endforeach
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <div class="flex gap-1">
                            @for($c = 0; $c < $weeks; $c++)
                                @php $colStart = $start->copy()->addWeeks($c); @endphp
                                <div class="flex flex-col gap-1">
                                    @for($r = 0; $r < $rows; $r++)
                                        @php
                                            $date = $colStart->copy()->addDays($r);
                                            $key  = $date->format('Y-m-d');
                                            $item = $activityData[$key] ?? ['visits'=>0, 'level'=>0];
                                            $level = $item['level'] ?? 0;
                                            $bg = match($level) {
                                                0 => 'bg-gray-700', 1 => 'bg-green-400', 2 => 'bg-green-500', 3 => 'bg-green-600', 4 => 'bg-green-700', default => 'bg-gray-700'
                                            };
                                        @endphp
                                        <div class="w-3 h-3 rounded-sm {{ $bg }} hover:scale-110 transition-transform" title="{{ $date->format('m月d日') }}: {{ $item['visits'] ?? 0 }} 次访问"></div>
                                    @endfor
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
                <!-- 说明 + 图例（底部） -->
                <div class="flex items-center justify-between mt-2 text-xs text-gray-400">
                    <span>过去一年（按周列、自上而下7天、等间距）</span>
                    <div class="flex items-center space-x-2">
                        <span>Less</span>
                        <div class="flex gap-1">
                            <span class="w-3 h-3 bg-gray-700 rounded-sm"></span>
                            <span class="w-3 h-3 bg-green-400 rounded-sm"></span>
                            <span class="w-3 h-3 bg-green-500 rounded-sm"></span>
                            <span class="w-3 h-3 bg-green-600 rounded-sm"></span>
                            <span class="w-3 h-3 bg-green-700 rounded-sm"></span>
                        </div>
                        <span>More</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 月度统计 -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold mb-6">月度访问统计</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $monthlyStats = [];
                $currentDate = Carbon::now()->subYear();
                $endDate = Carbon::now();
                
                while ($currentDate <= $endDate) {
                    $monthKey = $currentDate->format('Y-m');
                    $dateKey = $currentDate->format('Y-m-d');
                    $visits = $activityData[$dateKey]['visits'] ?? 0;
                    
                    if (!isset($monthlyStats[$monthKey])) {
                        $monthlyStats[$monthKey] = 0;
                    }
                    $monthlyStats[$monthKey] += $visits;
                    
                    $currentDate->addDay();
                }
            @endphp
            
            @foreach($monthlyStats as $month => $visits)
                <div class="p-4 rounded-lg bg-gray-800/50">
                    <p class="text-gray-400 text-sm">{{ Carbon::createFromFormat('Y-m', $month)->format('M Y') }}</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($visits) }}</p>
                    <p class="text-xs text-gray-400">次访问</p>
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
.grid-cols-53 {
    grid-template-columns: repeat(53, minmax(0, 1fr));
}

.activity-calendar {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.activity-calendar::-webkit-scrollbar {
    height: 8px;
}

.activity-calendar::-webkit-scrollbar-track {
    background: transparent;
}

.activity-calendar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 4px;
}

.activity-calendar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.7);
}
</style>
@endsection 