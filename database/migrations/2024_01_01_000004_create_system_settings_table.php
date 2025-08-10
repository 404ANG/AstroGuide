<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added missing import for DB facade

return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('设置键名');
            $table->text('value')->nullable()->comment('设置值');
            $table->string('type')->default('string')->comment('值类型：string, image, json');
            $table->string('description')->nullable()->comment('设置描述');
            $table->timestamps();
        });

        // 插入默认设置
        $this->seedDefaultSettings();
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }

    /**
     * 插入默认设置
     */
    private function seedDefaultSettings(): void
    {
        $defaults = [
            [
                'key' => 'site_name',
                'value' => 'AstroGuide',
                'type' => 'string',
                'description' => '网站名称'
            ],
            [
                'key' => 'site_description',
                'value' => '发现和整理您喜爱的网站',
                'type' => 'string',
                'description' => '网站描述'
            ],
            [
                'key' => 'site_icon',
                'value' => '/images/AstroGuide-icon.png',
                'type' => 'image',
                'description' => '网站图标'
            ],
            [
                'key' => 'admin_avatar',
                'value' => '/images/AstroGuide-icon.png',
                'type' => 'image',
                'description' => '管理员头像'
            ],
            [
                'key' => 'footer_navigation',
                'value' => '[]',
                'type' => 'json',
                'description' => '页脚导航链接（初始为空，需手动配置）'
            ],
            [
                'key' => 'social_media',
                'value' => '[]',
                'type' => 'json',
                'description' => '社交媒体链接（初始为空，需手动配置）'
            ],
            [
                'key' => 'copyright_info',
                'value' => 'Copyright©2025 · 404ANG',
                'type' => 'string',
                'description' => '版权信息'
            ]
        ];

        foreach ($defaults as $setting) {
            DB::table('system_settings')->insert($setting);
        }
    }
}; 