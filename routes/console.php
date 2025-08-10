<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\SystemSetting;
use App\Models\Site;

/**
 * 控制台路由（Artisan 命令注册）
 * 提供 settings:seed 命令，用于快速填充系统设置默认值
 */

Artisan::command('settings:seed', function () {
    $defaults = [
        'site_name' => 'AstroGuide',
        'site_description' => '发现和整理您喜爱的网站',
        'copyright_info' => 'Copyright©2025 · 404ANG',
        'footer_navigation' => '[]',
        'social_media' => '[]',
    ];

    foreach ($defaults as $key => $value) {
        SystemSetting::setValue($key, $value, in_array($key, ['footer_navigation','social_media']) ? 'json' : 'string', 'seed');
    }

    $this->info('系统设置默认值已填充完成');
})->purpose('填充系统设置默认值');

/**
 * 规范化站点 logo：将指向 gstatic 的旧地址统一替换为 google s2 接口
 */
Artisan::command('sites:normalize-logos', function () {
    $count = 0;
    Site::chunk(200, function ($sites) use (&$count) {
        foreach ($sites as $site) {
            $logo = $site->getRawOriginal('logo');
            if (!empty($logo)) {
                $host = parse_url($logo, PHP_URL_HOST) ?: '';
                if (str_contains($host, 'gstatic.com')) {
                    $domain = parse_url($site->url, PHP_URL_HOST);
                    $site->logo = "https://www.google.com/s2/favicons?domain={$domain}&sz=64";
                    $site->save();
                    $count++;
                }
            }
        }
    });
    $this->info("已规范化 {$count} 个站点的 logo 地址");
})->purpose('批量规范化站点 logo 地址，避免 404'); 