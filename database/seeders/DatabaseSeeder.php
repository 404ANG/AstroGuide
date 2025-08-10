<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Site;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 创建分类
        $categories = [
            ['name' => '常用工具', 'color' => '#3B82F6', 'sort_order' => 1],
            ['name' => '学习资源', 'color' => '#10B981', 'sort_order' => 2],
            ['name' => '娱乐休闲', 'color' => '#F59E0B', 'sort_order' => 3],
            ['name' => '技术文档', 'color' => '#8B5CF6', 'sort_order' => 4],
            ['name' => '新闻资讯', 'color' => '#EF4444', 'sort_order' => 5],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // 创建示例网站
        $sites = [
            [
                'name' => 'GitHub',
                'url' => 'https://github.com',
                'description' => '全球最大的代码托管平台',
                'category_id' => 1,
                'sort_order' => 1
            ],
            [
                'name' => 'Stack Overflow',
                'url' => 'https://stackoverflow.com',
                'description' => '程序员问答社区',
                'category_id' => 1,
                'sort_order' => 2
            ],
            [
                'name' => 'MDN Web Docs',
                'url' => 'https://developer.mozilla.org',
                'description' => 'Web开发技术文档',
                'category_id' => 4,
                'sort_order' => 1
            ],
            [
                'name' => 'W3Schools',
                'url' => 'https://www.w3schools.com',
                'description' => '在线Web技术学习平台',
                'category_id' => 2,
                'sort_order' => 1
            ],
            [
                'name' => 'YouTube',
                'url' => 'https://www.youtube.com',
                'description' => '全球最大的视频分享平台',
                'category_id' => 3,
                'sort_order' => 1
            ],
            [
                'name' => '知乎',
                'url' => 'https://www.zhihu.com',
                'description' => '中文问答社区',
                'category_id' => 5,
                'sort_order' => 1
            ],
            [
                'name' => 'B站',
                'url' => 'https://www.bilibili.com',
                'description' => '中国领先的视频网站',
                'category_id' => 3,
                'sort_order' => 2
            ],
            [
                'name' => '掘金',
                'url' => 'https://juejin.cn',
                'description' => '开发者技术社区',
                'category_id' => 4,
                'sort_order' => 2
            ]
        ];

        foreach ($sites as $siteData) {
            Site::create($siteData);
        }
    }
} 