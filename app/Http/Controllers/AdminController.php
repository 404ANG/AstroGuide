<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Category;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Services\PerformanceService;
use App\Services\WebsiteInfoService;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * 导入书签 - 表单
     */
    public function importBookmarksForm(): View
    {
        $categories = Category::orderBy('sort_order')->get();
        return view('admin.import-bookmarks', compact('categories'));
    }

    /**
     * 导入书签 - 处理
     */
    public function importBookmarksProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:html,htm,txt',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $categoryId = $request->input('category_id');
        $mapFolders = $request->boolean('map_folders');
        $smartDedupe = $request->boolean('smart_dedupe', true);
        $fetchIcon = $request->boolean('fetch_icon', true);

        // 解析分组（文件夹）与链接：匹配 Netscape 书签格式中的 <H3> 文件夹，<A HREF> 书签
        // 简单栈解析：遇到 H3 入栈为当前 folder，遇到 A 记录到当前 folder
        $lines = preg_split("/\r?\n/", $content);
        $stack = [];
        $currentFolder = null;
        $items = [];
        foreach ($lines as $line) {
            if (preg_match('/<h3[^>]*>(.*?)<\/h3>/i', $line, $hm)) {
                $currentFolder = trim(strip_tags($hm[1]));
                array_push($stack, $currentFolder);
                continue;
            }
            if (stripos($line, '</dl>') !== false && !empty($stack)) {
                array_pop($stack);
                $currentFolder = end($stack) ?: null;
                continue;
            }
            if (preg_match('/<a[^>]*href=\"([^\"]+)\"[^>]*>(.*?)<\/a>/i', $line, $am)) {
                $items[] = [
                    'url' => trim($am[1]),
                    'name' => trim(strip_tags($am[2])) ?: trim($am[1]),
                    'folder' => $currentFolder
                ];
            }
        }

        // 智能去重辅助：规范化 URL（忽略协议/WWW/末尾斜杠）
        $normalize = function(string $url) {
            $u = preg_replace('/^https?:\/\//i', '', $url);
            $u = preg_replace('/^www\./i', '', $u);
            $u = rtrim($u, '/');
            return strtolower($u);
        };

        $count = 0;
        foreach ($items as $it) {
            $url = $it['url'];
            if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
            $name = $it['name'];
            $folder = $it['folder'];

            // 分类映射：按 folder 名创建/查找分类
            $catId = $categoryId;
            if ($mapFolders && $folder) {
                $cat = Category::firstOrCreate(['name' => $folder], [
                    'description' => '', 'color' => '#3B82F6', 'sort_order' => 0, 'is_active' => true
                ]);
                $catId = $cat->id;
            }
            if (!$catId) { $catId = Category::first()?->id; }

            // 智能去重：先按规范化 URL 查找
            $exists = null;
            if ($smartDedupe) {
                $norm = $normalize($url);
                $exists = Site::all()->first(function($s) use($normalize, $norm) {
                    return $normalize($s->url) === $norm;
                });
            } else {
                $exists = Site::where('url', $url)->first();
            }
            if ($exists) { continue; }

            // 自动获取网站信息
            $websiteInfo = app(WebsiteInfoService::class)->getWebsiteInfo($url);
            $description = '';
            $logo = null;
            
            if ($websiteInfo['success']) {
                $description = $websiteInfo['description'];
                $logo = $websiteInfo['icon'];
                
                // 如果书签名称为空或太短，使用网站标题
                if (strlen($name) < 3 && !empty($websiteInfo['title'])) {
                    $name = $websiteInfo['title'];
                }
            }
            
            // 如果自动获取失败，使用Google Favicon服务作为备选
            if (empty($logo) && $fetchIcon) {
                $host = parse_url($url, PHP_URL_HOST);
                if ($host) { 
                    $logo = "https://www.google.com/s2/favicons?domain={$host}&sz=64"; 
                }
            }

            Site::create([
                'name' => $name,
                'url' => $url,
                'description' => $description,
                'logo' => $logo,
                'category_id' => $catId,
                'sort_order' => 0,
                'visits' => 0,
                'is_active' => true,
            ]);
            $count++;
        }

        return back()->with('success', "导入完成：共导入 {$count} 个网站");
    }
    /**
     * 显示管理后台首页
     */
    public function dashboard(): View
    {
        // 使用缓存优化统计数据
        $cacheKey = 'dashboard_stats_' . date('Y-m-d');
        
        $stats = cache()->remember($cacheKey, 1800, function () {
            return [
                'total_sites' => Site::count(),
                'total_categories' => Category::count(),
                'total_visits' => ActivityLog::sum('visits'),
                'today_visits' => ActivityLog::whereDate('created_at', today())->sum('visits'),
            ];
        });

        // 活跃天数数据（类似GitHub贡献图）
        $activityData = $this->getActivityData();

        // 热门网站和最近网站使用缓存
        $popularSites = cache()->remember('popular_sites', 1800, function () {
            return Site::with('category')
                ->orderBy('visits', 'desc')
                ->limit(5)
                ->get();
        });

        $recentSites = cache()->remember('recent_sites', 1800, function () {
            return Site::with('category')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        });

        $settings = SystemSetting::getAllSettings();
        return view('admin.dashboard', compact('stats', 'activityData', 'popularSites', 'recentSites', 'settings'));
    }

    /**
     * 显示网站管理页面
     */
    public function sites(): View
    {
        $sites = Site::with('category')->orderBy('created_at','desc')->paginate(15);
        
        // 缓存分类数据
        $categories = cache()->remember('categories_for_sites', 3600, function () {
            return Category::orderBy('sort_order')->get();
        });
        
        return view('admin.sites', compact('sites', 'categories'));
    }

    /**
     * 显示分类管理页面
     */
    public function categories(): View
    {
        $categories = cache()->remember('categories_with_count', 1800, function () {
            return Category::withCount('sites')->orderBy('sort_order')->paginate(15);
        });
        
        return view('admin.categories', compact('categories'));
    }

    /**
     * 显示活动统计页面
     */
    public function activity(): View
    {
        $activityData = $this->getActivityData();
        return view('admin.activity', compact('activityData'));
    }

    /**
     * 获取活动数据 - 优化版本
     */
    private function getActivityData(): array
    {
        // 使用缓存避免重复查询
        $cacheKey = 'activity_data_' . date('Y-m-d');
        
        return cache()->remember($cacheKey, 3600, function () {
            // 获取过去一年的活跃天数数据 - 使用单次查询优化
            $startDate = Carbon::now()->subYear()->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
            
            // 一次性查询所有数据，避免循环中的数据库查询
            $activityLogs = ActivityLog::selectRaw('DATE(created_at) as date, SUM(visits) as total_visits')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->get()
                ->keyBy('date');
            
            $activityData = [];
            $currentDate = Carbon::now()->subYear();
            $endDateObj = Carbon::now();
            
            // 生成完整的日期范围数据
            while ($currentDate <= $endDateObj) {
                $dayKey = $currentDate->format('Y-m-d');
                $visits = $activityLogs->get($dayKey)?->total_visits ?? 0;
                
                $activityData[$dayKey] = [
                    'date' => $dayKey,
                    'visits' => $visits,
                    'level' => $this->getActivityLevel($visits)
                ];
                
                $currentDate->addDay();
            }
            
            return $activityData;
        });
    }

    /**
     * 获取活动等级
     */
    private function getActivityLevel(int $visits): int
    {
        if ($visits == 0) return 0;
        if ($visits <= 5) return 1;
        if ($visits <= 10) return 2;
        if ($visits <= 20) return 3;
        return 4;
    }

    /**
     * 添加新网站
     */
    public function storeSite(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'logo' => 'nullable|string',
            'sort_order' => 'nullable|integer'
        ]);

        $data = $request->all();
        
        // 如果用户没有填写描述或图标，尝试自动获取
        if (empty($data['description']) || empty($data['logo'])) {
            $websiteInfo = app(WebsiteInfoService::class)->getWebsiteInfo($data['url']);
            
            if ($websiteInfo['success']) {
                // 自动填充描述
                if (empty($data['description']) && !empty($websiteInfo['description'])) {
                    $data['description'] = $websiteInfo['description'];
                }
                
                // 自动填充图标
                if (empty($data['logo']) && !empty($websiteInfo['icon'])) {
                    $data['logo'] = $websiteInfo['icon'];
                }
                
                // 如果用户没有填写名称，使用网站标题
                if (empty($data['name']) && !empty($websiteInfo['title'])) {
                    $data['name'] = $websiteInfo['title'];
                }
            }
        }

        Site::create($data);
        return redirect()->back()->with('success', '网站添加成功');
    }

    /**
     * 更新网站信息
     */
    public function updateSite(Request $request, Site $site)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'logo' => 'nullable|string',
            'sort_order' => 'nullable|integer'
        ]);

        $site->update($request->all());
        return redirect()->back()->with('success', '网站更新成功');
    }

    /**
     * 删除网站
     */
    public function deleteSite(Site $site)
    {
        $site->delete();
        return redirect()->back()->with('success', '网站删除成功');
    }

    /**
     * 显示系统设置页面
     */
    public function settings(): View
    {
        $settings = SystemSetting::getAllSettings();
        return view('admin.settings', compact('settings'));
    }

    /**
     * 更新系统设置
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'site_icon' => 'nullable|image|mimes:ico,png,jpg,jpeg|max:2048',
            'admin_avatar' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'footer_navigation' => 'nullable|array',
            'social_media' => 'nullable|array',
        ]);

        // 基本信息
        SystemSetting::setValue('site_name', $request->site_name, 'string', '网站名称');
        SystemSetting::setValue('site_description', $request->site_description, 'string', '网站描述');

        if ($request->hasFile('site_icon')) {
            $iconPath = $request->file('site_icon')->store('public/icons', 'public');
            $iconUrl = Storage::url($iconPath);
            SystemSetting::setValue('site_icon', $iconUrl, 'image', '网站图标');
        }
        if ($request->hasFile('admin_avatar')) {
            $avatarPath = $request->file('admin_avatar')->store('public/avatars', 'public');
            $avatarUrl = Storage::url($avatarPath);
            SystemSetting::setValue('admin_avatar', $avatarUrl, 'image', '管理员头像');
        }

        // 固定页脚导航（支持删除）：personal_page / website_navigation / blog / about_us（兼容旧配置）
        $footerInput = $request->input('footer_navigation', []);
        $footerKeys = ['personal_page','website_navigation','blog','about_us'];
        $footerNav = [];
        foreach ($footerKeys as $key) {
            $row = $footerInput[$key] ?? [];
            // 检查是否被标记为删除
            if (isset($row['deleted']) && $row['deleted'] == '1') {
                continue; // 跳过被删除的项
            }
            $name = trim($row['name'] ?? '');
            $url = trim($row['url'] ?? '');
            if ($name !== '' && $url !== '') {
                $footerNav[$key] = ['name' => $name, 'url' => $url];
            }
        }
        SystemSetting::setValue('footer_navigation', json_encode($footerNav, JSON_UNESCAPED_UNICODE), 'json', '页脚导航链接（旧）');

        // 新：页脚导航动态列表
        $footerLinksInput = $request->input('footer_links', []);
        $footerLinks = [];
        if (is_array($footerLinksInput)) {
            foreach ($footerLinksInput as $row) {
                $name = trim($row['name'] ?? '');
                $url = trim($row['url'] ?? '');
                if ($name !== '' && $url !== '') {
                    $footerLinks[] = ['name' => $name, 'url' => $url];
                }
            }
        }
        SystemSetting::setValue('footer_links', json_encode($footerLinks, JSON_UNESCAPED_UNICODE), 'json', '页脚导航链接（新）');

        // 固定社交媒体（支持删除、不可自定义图标）兼容旧配置：github / bilibili / zhihu / wechat / email
        $socialInput = $request->input('social_media', []);
        $socialKeys = ['github','bilibili','zhihu','wechat','email'];
        $socialMedia = [];
        foreach ($socialKeys as $key) {
            $row = $socialInput[$key] ?? [];
            // 检查是否被标记为删除
            if (isset($row['deleted']) && $row['deleted'] == '1') {
                continue; // 跳过被删除的项
            }
            $name = trim($row['name'] ?? '');
            $url = trim($row['url'] ?? '');
            if ($name !== '' && $url !== '') {
                $socialMedia[$key] = ['name' => $name, 'url' => $url];
            }
        }
        SystemSetting::setValue('social_media', json_encode($socialMedia, JSON_UNESCAPED_UNICODE), 'json', '社交媒体链接（旧）');

        // 新：社交媒体动态列表
        $socialLinksInput = $request->input('social_links', []);
        $socialLinks = [];
        if (is_array($socialLinksInput)) {
            foreach ($socialLinksInput as $row) {
                $name = trim($row['name'] ?? '');
                $url = trim($row['url'] ?? '');
                if ($name !== '' && $url !== '') {
                    $socialLinks[] = ['name' => $name, 'url' => $url];
                }
            }
        }
        SystemSetting::setValue('social_links', json_encode($socialLinks, JSON_UNESCAPED_UNICODE), 'json', '社交媒体链接（新）');

        // 轮播图与标题：清洗空项后保存
        $carouselInput = $request->input('carousel_items', []);
        $carousel = [];
        if (is_array($carouselInput)) {
            foreach ($carouselInput as $row) {
                $image = trim($row['image'] ?? '');
                $title = trim($row['title'] ?? '');
                $subtitle = trim($row['subtitle'] ?? '');
                $link = trim($row['link'] ?? '');
                if ($image !== '' || $title !== '' || $subtitle !== '') {
                    $carousel[] = compact('image','title','subtitle','link');
                }
            }
        }
        SystemSetting::setValue('carousel_items', json_encode($carousel, JSON_UNESCAPED_UNICODE), 'json', '首页轮播配置');

        // 备案
        if ($request->filled('icp_number')) {
            SystemSetting::setValue('icp_number', $request->icp_number, 'string', 'ICP备案号');
        }
        SystemSetting::setValue('icp_url', $request->input('icp_url', 'https://beian.miit.gov.cn/'), 'string', '备案跳转链接');

        // 版权（如未设置，维持默认值）
        if ($request->filled('copyright_info')) {
            SystemSetting::setValue('copyright_info', $request->copyright_info, 'string', '版权信息');
        }

        return redirect()->back()->with('success', '系统设置更新成功');
    }

    /**
     * 获取系统设置（API）
     */
    public function getSettings()
    {
        $settings = SystemSetting::getAllSettings();
        return response()->json($settings);
    }

    /**
     * 显示性能监控页面
     */
    public function performance(): View
    {
        $performanceService = new PerformanceService();
        
        // 获取实时性能数据
        $metrics = $performanceService->getPerformanceMetrics();
        $cacheStatus = $performanceService->getCacheStatus();
        $optimizationTips = $performanceService->getOptimizationTips();
        $systemStats = $performanceService->getSystemStats();
        
        return view('admin.performance', compact(
            'metrics', 
            'cacheStatus', 
            'optimizationTips',
            'systemStats'
        ));
    }

    /**
     * 实时系统状态 JSON
     */
    public function systemStats()
    {
        $performanceService = new PerformanceService();
        return response()->json($performanceService->getSystemStats());
    }

    /**
     * 性能历史数据 JSON
     */
    public function performanceHistory(Request $request)
    {
        $hours = (int) $request->input('hours', 24); // 默认获取24小时数据
        $hours = max(1, min(168, $hours)); // 限制在1-168小时之间
        
        $performanceService = new PerformanceService();
        $history = $performanceService->getPerformanceHistory($hours);
        
        return response()->json($history);
    }

    /**
     * 缓存状态 JSON
     */
    public function cacheStatus()
    {
        $performanceService = new PerformanceService();
        $cacheStatus = $performanceService->getCacheStatus();
        
        return response()->json($cacheStatus);
    }

    /**
     * 性能指标 JSON
     */
    public function performanceMetrics()
    {
        $performanceService = new PerformanceService();
        $metrics = $performanceService->getPerformanceMetrics();
        
        return response()->json($metrics);
    }

    /**
     * 优化建议 JSON
     */
    public function optimizationTips()
    {
        $performanceService = new PerformanceService();
        $tips = $performanceService->getOptimizationTips();
        
        return response()->json($tips);
    }

    /**
     * 慢查询详情 JSON
     */
    public function slowQueries()
    {
        $performanceService = new PerformanceService();
        $slowQueries = $performanceService->getSlowQueriesDetails();
        
        return response()->json($slowQueries);
    }

    /**
     * 数据库性能统计 JSON
     */
    public function databaseStats()
    {
        $performanceService = new PerformanceService();
        $stats = $performanceService->getDatabasePerformanceStats();
        
        return response()->json($stats);
    }

    /**
     * 优化数据库索引
     */
    public function optimizeIndexes()
    {
        try {
            // 执行数据库索引优化命令
            $output = [];
            $returnCode = 0;
            
            exec('php artisan db:optimize-indexes --force', $output, $returnCode);
            
            if ($returnCode === 0) {
                return response()->json([
                    'success' => true, 
                    'message' => '数据库索引优化成功',
                    'output' => implode("\n", $output)
                ]);
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => '数据库索引优化失败',
                    'output' => implode("\n", $output)
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => '数据库索引优化失败：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 清理缓存
     */
    public function clearCache()
    {
        try {
            $performanceService = new PerformanceService();
            $result = $performanceService->clearAllCaches();
            
            if ($result) {
                return response()->json(['success' => true, 'message' => '缓存清理成功']);
            } else {
                return response()->json(['success' => false, 'message' => '缓存清理失败']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * 预热缓存
     */
    public function warmupCache()
    {
        try {
            $performanceService = new PerformanceService();
            $result = $performanceService->warmupCache();
            
            if ($result) {
                return response()->json(['success' => true, 'message' => '缓存预热成功']);
            } else {
                return response()->json(['success' => false, 'message' => '缓存预热失败']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * 性能优化
     */
    public function optimizePerformance()
    {
        try {
            $performanceService = new PerformanceService();
            $result = $performanceService->optimizePerformance();
            
            if ($result) {
                return response()->json(['success' => true, 'message' => '性能优化完成']);
            } else {
                return response()->json(['success' => false, 'message' => '性能优化失败']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * 批量自动获取网站信息（为缺失描述或图标的网站补全）
     */
    public function bulkFetchSiteInfo(Request $request)
    {
        $limit = (int) $request->input('limit', 50);
        $onlyEmpty = $request->boolean('only_empty', true);

        $query = Site::query();
        if ($onlyEmpty) {
            $query->where(function($q){
                $q->whereNull('description')->orWhere('description','')
                  ->orWhereNull('logo')->orWhere('logo','');
            });
        }
        $sites = $query->orderBy('updated_at','asc')->limit(max(1, min(200, $limit)))->get();

        $service = app(WebsiteInfoService::class);
        $updated = 0; $failed = 0;

        foreach ($sites as $site) {
            try {
                $info = $service->getWebsiteInfo($site->url);
                $data = [];
                if (($info['success'] ?? false) === true) {
                    if (empty($site->description) && !empty($info['description'])) {
                        $data['description'] = $info['description'];
                    }
                    if (empty($site->logo) && !empty($info['icon'])) {
                        $data['logo'] = $info['icon'];
                    }
                }
                if (!empty($data)) {
                    $site->fill($data);
                    $site->save();
                    $updated++;
                }
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'failed' => $failed,
            'total' => $sites->count(),
        ]);
    }
} 