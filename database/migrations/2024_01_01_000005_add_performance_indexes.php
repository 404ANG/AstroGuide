<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 添加性能优化索引
     */
    public function up(): void
    {
        // 为活动日志表添加索引
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('created_at'); // 日期查询索引
            $table->index(['created_at', 'visits']); // 复合索引
        });

        // 为网站表添加索引
        Schema::table('sites', function (Blueprint $table) {
            $table->index('visits'); // 访问量排序索引
            $table->index('created_at'); // 创建时间索引
            $table->index('category_id'); // 分类关联索引
        });

        // 为分类表添加索引
        Schema::table('categories', function (Blueprint $table) {
            $table->index('sort_order'); // 排序索引
            $table->index('is_active'); // 状态索引
        });
    }

    /**
     * 回滚索引
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['created_at', 'visits']);
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropIndex(['visits']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['category_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
            $table->dropIndex(['is_active']);
        });
    }
}; 