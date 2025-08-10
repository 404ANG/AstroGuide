<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        Schema::create('performance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('route_name')->nullable()->index();
            $table->string('method', 10)->index();
            $table->string('url');
            $table->float('execution_time'); // 执行时间（毫秒）
            $table->bigInteger('memory_usage'); // 内存使用（字节）
            $table->integer('query_count'); // 数据库查询数量
            $table->json('additional_data')->nullable(); // 额外数据
            $table->timestamps();
            
            // 添加复合索引
            $table->index(['route_name', 'method']);
            $table->index(['created_at', 'execution_time']);
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_logs');
    }
}; 