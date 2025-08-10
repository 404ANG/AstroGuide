<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Category;
use App\Models\ActivityLog;
use App\Models\SystemSetting; // 新增：系统设置模型
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * 显示首页
     */
    public function index(Request $request): View
    {
        $query = Site::with('category');
        
        // 搜索功能
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        // 分类筛选
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }
        
        $sites = $query->orderBy('sort_order', 'asc')->get();
        // 预加载每个分类下的网站，按排序字段排列，便于分组展示
        $categories = Category::with(['sites' => function($q) {
            $q->orderBy('sort_order', 'asc');
        }])->withCount('sites')->get();

        // 读取系统设置并提供回退默认值（固定键顺序）
        $settings = SystemSetting::getAllSettings();

        // 页脚导航：完全依赖系统设置，没有配置时就不显示
        $footerLinks = [];
        $footerNavFromSettings = json_decode($settings['footer_navigation'] ?? '[]', true);
        if (is_array($footerNavFromSettings)) {
            foreach ($footerNavFromSettings as $key => $row) {
                $name = trim($row['name'] ?? '');
                $url = trim($row['url'] ?? '');
                if ($name !== '' && $url !== '') {
                    $footerLinks[] = ['name' => $name, 'url' => $url];
                }
            }
        }

        // 社交媒体：完全依赖系统设置，没有配置时就不显示
        $socialLinks = [];
        
        // 优先使用新的social_links字段，兼容旧的social_media字段
        $socialFromSettings = json_decode($settings['social_links'] ?? '[]', true);
        if (is_array($socialFromSettings)) {
            foreach ($socialFromSettings as $key => $row) {
                $name = trim($row['name'] ?? '');
                $url = trim($row['url'] ?? '');
                if ($name !== '' && $url !== '') {
                    $socialLinks[] = ['name' => $name, 'url' => $url];
                }
            }
        }
        
        // 如果没有新的social_links，则使用旧的social_media作为备选
        if (empty($socialLinks)) {
            $socialFromSettings = json_decode($settings['social_media'] ?? '[]', true);
            if (is_array($socialFromSettings)) {
                foreach ($socialFromSettings as $key => $row) {
                    $name = trim($row['name'] ?? '');
                    $url = trim($row['url'] ?? '');
                    if ($name !== '' && $url !== '') {
                        $socialLinks[] = ['name' => $name, 'url' => $url];
                    }
                }
            }
        }

        // 版权信息：默认显示要求的文案
        $copyright = $settings['copyright_info'] ?? 'Copyright©2025 · 404ANG';

        // 轮播：从设置读取，提供默认回退
        $carouselItems = (function() use ($settings) {
            try {
                $items = json_decode($settings['carousel_items'] ?? '[]', true) ?: [];
                // 过滤空项
                $filtered = [];
                foreach ($items as $it) {
                    $image = trim($it['image'] ?? '');
                    $title = trim($it['title'] ?? '');
                    $subtitle = trim($it['subtitle'] ?? '');
                    $link = trim($it['link'] ?? '');
                    if ($image !== '' || $title !== '' || $subtitle !== '') {
                        $filtered[] = compact('image','title','subtitle','link');
                    }
                }
                if (!empty($filtered)) return $filtered;
            } catch (\Throwable $e) {}
            // 默认 3 张
            return [
                [
                    'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=1600&auto=format&fit=crop',
                    'title' => '欢迎使用 AstroGuide',
                    'subtitle' => '发现和整理您喜爱的网站',
                    'link' => ''
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=1600&auto=format&fit=crop',
                    'title' => '高效分类',
                    'subtitle' => '常用工具 / 学习资源 / 娱乐休闲 / 技术文档 / 新闻资讯',
                    'link' => ''
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?q=80&w=1600&auto=format&fit=crop',
                    'title' => '一键直达',
                    'subtitle' => '整理你的常用站点，提升效率',
                    'link' => ''
                ],
            ];
        })();
        
        return view('home', compact('sites', 'categories', 'settings', 'footerLinks', 'socialLinks', 'copyright', 'carouselItems'));
    }

    /**
     * 访问网站并记录统计
     */
    public function visit(Site $site)
    {
        // 记录访问统计
        ActivityLog::create([
            'site_id' => $site->id,
            'visits' => 1,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
        
        // 更新网站访问次数
        $site->increment('visits');
        
        return redirect($site->url);
    }

    /**
     * 复制网站信息
     */
    public function copyInfo(Site $site): JsonResponse
    {
        return response()->json([
            'name' => $site->name,
            'url' => $site->url,
            'description' => $site->description
        ]);
    }
} 