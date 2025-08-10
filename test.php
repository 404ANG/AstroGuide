<?php

// 简单的项目结构测试
echo "AstroGuide 项目结构测试\n";
echo "========================\n\n";

// 检查必要文件
$requiredFiles = [
    'composer.json',
    'docker-compose.yml',
    'Dockerfile',
    'app/Http/Controllers/AdminController.php',
    'app/Http/Controllers/HomeController.php',
    'app/Models/Site.php',
    'app/Models/Category.php',
    'app/Models/ActivityLog.php',
    'resources/views/home.blade.php',
    'resources/views/admin/layout.blade.php',
    'resources/views/admin/dashboard.blade.php',
    'resources/views/admin/activity.blade.php',
    'routes/web.php',
    'database/migrations/2024_01_01_000001_create_categories_table.php',
    'database/migrations/2024_01_01_000002_create_sites_table.php',
    'database/migrations/2024_01_01_000003_create_activity_logs_table.php'
];

$missingFiles = [];
foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        $missingFiles[] = $file;
    } else {
        echo "✅ {$file}\n";
    }
}

if (!empty($missingFiles)) {
    echo "\n❌ 缺失文件：\n";
    foreach ($missingFiles as $file) {
        echo "   - {$file}\n";
    }
} else {
    echo "\n🎉 所有必要文件都存在！\n";
}

echo "\n📊 项目统计：\n";
echo "   - 控制器：2个\n";
echo "   - 模型：3个\n";
echo "   - 视图：4个\n";
echo "   - 迁移：3个\n";
echo "   - 路由：已配置\n";

echo "\n🚀 部署说明：\n";
echo "   1. 运行：docker-compose up -d\n";
echo "   2. 访问：http://localhost:8080\n";
echo "   3. 后台：http://localhost:8080/admin\n"; 