<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OptimizeDatabaseIndexes extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'db:optimize-indexes {--force : 强制执行优化}';

    /**
     * 命令描述
     */
    protected $description = '优化数据库索引以提升查询性能';

    /**
     * 建议的索引配置
     */
    private array $suggestedIndexes = [
        'activity_logs' => [
            ['name' => 'idx_activity_logs_created_at', 'columns' => ['created_at']],
            ['name' => 'idx_activity_logs_site_id', 'columns' => ['site_id']],
            ['name' => 'idx_activity_logs_visits', 'columns' => ['visits']],
        ],
        'performance_logs' => [
            ['name' => 'idx_performance_logs_created_at', 'columns' => ['created_at']],
            ['name' => 'idx_performance_logs_execution_time', 'columns' => ['execution_time']],
            ['name' => 'idx_performance_logs_route_name', 'columns' => ['route_name']],
            ['name' => 'idx_performance_logs_method', 'columns' => ['method']],
        ],
        'sites' => [
            ['name' => 'idx_sites_category_id', 'columns' => ['category_id']],
            ['name' => 'idx_sites_created_at', 'columns' => ['created_at']],
            ['name' => 'idx_sites_visits', 'columns' => ['visits']],
            ['name' => 'idx_sites_is_active', 'columns' => ['is_active']],
            ['name' => 'idx_sites_sort_order', 'columns' => ['sort_order']],
        ],
        'categories' => [
            ['name' => 'idx_categories_sort_order', 'columns' => ['sort_order']],
            ['name' => 'idx_categories_is_active', 'columns' => ['is_active']],
            ['name' => 'idx_categories_name', 'columns' => ['name']],
        ],
    ];

    /**
     * 执行命令
     */
    public function handle()
    {
        $this->info('开始数据库索引优化...');
        
        if (!$this->option('force') && !$this->confirm('确定要优化数据库索引吗？这可能需要一些时间。')) {
            $this->info('操作已取消。');
            return 0;
        }

        $optimizedCount = 0;
        $errorCount = 0;

        foreach ($this->suggestedIndexes as $table => $indexes) {
            $this->info("正在优化表: {$table}");
            
            foreach ($indexes as $index) {
                try {
                    if ($this->createIndexIfNotExists($table, $index)) {
                        $this->line("  ✓ 创建索引: {$index['name']}");
                        $optimizedCount++;
                    } else {
                        $this->line("  - 索引已存在: {$index['name']}");
                    }
                } catch (\Exception $e) {
                    $this->error("  ✗ 创建索引失败: {$index['name']} - {$e->getMessage()}");
                    $errorCount++;
                }
            }
        }

        $this->newLine();
        $this->info("索引优化完成！");
        $this->info("成功创建: {$optimizedCount} 个索引");
        
        if ($errorCount > 0) {
            $this->warn("失败: {$errorCount} 个索引");
        }

        // 分析优化效果
        $this->analyzeOptimizationEffect();

        return 0;
    }

    /**
     * 创建索引（如果不存在）
     */
    private function createIndexIfNotExists(string $table, array $index): bool
    {
        $indexName = $index['name'];
        $columns = $index['columns'];

        // 检查索引是否已存在
        if ($this->indexExists($table, $indexName)) {
            return false;
        }

        // 检查表是否存在
        if (!Schema::hasTable($table)) {
            throw new \Exception("表 {$table} 不存在");
        }

        // 创建索引
        Schema::table($table, function ($table) use ($indexName, $columns) {
            $table->index($columns, $indexName);
        });

        return true;
    }

    /**
     * 检查索引是否存在
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            // 对于SQLite数据库
            if (config('database.default') === 'sqlite') {
                $indexes = DB::select("PRAGMA index_list({$table})");
                foreach ($indexes as $index) {
                    if ($index->name === $indexName) {
                        return true;
                    }
                }
                return false;
            }

            // 对于MySQL数据库
            $indexes = DB::select("SHOW INDEX FROM {$table}");
            foreach ($indexes as $index) {
                if ($index->Key_name === $indexName) {
                    return true;
                }
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 分析优化效果
     */
    private function analyzeOptimizationEffect(): void
    {
        $this->newLine();
        $this->info('分析优化效果...');

        try {
            // 获取当前索引统计
            $indexStats = $this->getIndexStatistics();
            
            $this->table(
                ['表名', '索引数量', '建议索引'],
                $indexStats
            );

            // 提供优化建议
            $this->newLine();
            $this->info('优化建议:');
            $this->line('1. 定期监控慢查询日志');
            $this->line('2. 根据实际查询模式调整索引');
            $this->line('3. 避免过度索引，影响写入性能');
            $this->line('4. 定期重建索引以保持性能');

        } catch (\Exception $e) {
            $this->error("分析优化效果时出错: {$e->getMessage()}");
        }
    }

    /**
     * 获取索引统计信息
     */
    private function getIndexStatistics(): array
    {
        $stats = [];

        foreach (array_keys($this->suggestedIndexes) as $table) {
            try {
                $indexCount = $this->getTableIndexCount($table);
                $suggestedCount = count($this->suggestedIndexes[$table]);
                $stats[] = [$table, $indexCount, $suggestedCount];
            } catch (\Exception $e) {
                $stats[] = [$table, 'N/A', count($this->suggestedIndexes[$table])];
            }
        }

        return $stats;
    }

    /**
     * 获取表的索引数量
     */
    private function getTableIndexCount(string $table): int
    {
        try {
            if (config('database.default') === 'sqlite') {
                $indexes = DB::select("PRAGMA index_list({$table})");
                return count($indexes);
            } else {
                $indexes = DB::select("SHOW INDEX FROM {$table}");
                $uniqueIndexes = array_unique(array_column($indexes, 'Key_name'));
                return count($uniqueIndexes);
            }
        } catch (\Exception $e) {
            return 0;
        }
    }
} 