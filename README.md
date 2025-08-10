# AstroGuide 导航网站

一个专为家庭NAS设计的现代化导航网站，具有美观的毛玻璃效果、完整的后台管理功能和活跃天数统计。

## ✨ 功能特性

### 🎨 前台功能
- **毛玻璃效果**：现代化的卡片设计和头部栏
- **卡片式布局**：响应式网格布局，支持多种屏幕尺寸
- **搜索功能**：实时搜索网站名称和描述
- **分类筛选**：按分类快速筛选网站
- **一键直达**：点击卡片直接访问网站
- **复制功能**：一键复制网站信息（名称、链接、简介）
- **主题切换**：支持深色/浅色主题切换
- **返回顶部**：右下角悬浮返回顶部按钮
- **全宽布局**：充分利用屏幕空间

### 🔧 后台管理
- **深色主题**：默认深色模式，护眼舒适
- **仪表板**：数据统计和可视化图表
- **网站管理**：增删改查网站信息，支持自动获取网站信息
- **分类管理**：网站分类管理
- **书签导入**：支持HTML书签文件导入，自动获取网站信息
- **活跃天数统计**：类似GitHub贡献图的访问统计
- **毛玻璃效果**：现代化的UI设计

### 📊 活跃天数功能
- **年度视图**：显示过去一年的访问活跃度
- **颜色分级**：不同颜色代表不同访问量级别
- **月度统计**：按月统计访问数据
- **交互式图表**：鼠标悬停显示详细信息

## 🚀 快速部署

### 使用Docker一键部署

1. **克隆项目**
```bash
git clone https://github.com/404ANG/AstroGuide.git
cd nas-navigation
```

2. **启动服务**
```bash
docker-compose up -d
```

3. **访问网站**
- 前台：http://localhost:8080
- 后台：http://localhost:8080/admin

### 手动部署

1. **安装依赖**
```bash
composer install
```

2. **配置环境**
```bash
cp .env.example .env
php artisan key:generate
```

3. **数据库迁移**
```bash
php artisan migrate
```

4. **启动服务**
```bash
php artisan serve
```

## 📁 项目结构

```
nas-navigation/
├── app/
│   ├── Http/Controllers/
│   │   ├── AdminController.php    # 后台管理控制器
│   │   └── HomeController.php     # 前台控制器
│   └── Models/
│       ├── Site.php              # 网站模型
│       ├── Category.php          # 分类模型
│       └── ActivityLog.php       # 活动日志模型
├── resources/views/
│   ├── admin/                    # 后台管理视图
│   │   ├── layout.blade.php      # 后台布局
│   │   ├── dashboard.blade.php   # 仪表板
│   │   └── activity.blade.php    # 活跃统计
│   └── home.blade.php            # 前台首页
├── database/migrations/          # 数据库迁移
├── routes/web.php               # 路由定义
├── docker-compose.yml           # Docker配置
└── Dockerfile                   # Docker镜像
```

## 🎯 技术栈

- **后端**：Laravel 10.x
- **前端**：Blade模板 + Tailwind CSS + Alpine.js
- **数据库**：SQLite（默认）/ MySQL / PostgreSQL
- **缓存**：Laravel Cache
- **部署**：Docker + Apache

## 🤖 智能信息获取功能

### ✨ 自动获取网站信息
系统集成了智能网站信息抓取服务，能够自动获取网站的以下信息：

- **网站标题**：从HTML的`<title>`标签或`<h1>`标签获取
- **网站描述**：从meta description、Open Graph或Twitter Card获取
- **网站图标**：自动获取favicon.ico或自定义图标链接

### 🔄 应用场景

1. **新增网站时**：
   - 输入网站URL后，点击"自动获取信息"按钮
   - 系统自动填充网站名称、描述和图标
   - 如果某些字段已有内容，不会覆盖用户输入

2. **批量导入书签时**：
   - 上传HTML书签文件后，系统自动处理每个链接
   - 为每个网站自动获取标题、描述和图标
   - 如果自动获取失败，使用Google Favicon服务作为备选

### 🛡️ 安全特性

- **超时控制**：HTTP请求设置10秒超时，避免长时间等待
- **错误处理**：完善的异常处理，不会因单个网站失败影响整体导入
- **日志记录**：记录获取失败的原因，便于问题排查
- **用户代理**：使用标准浏览器User-Agent，提高兼容性

### 📱 使用方法

#### 手动新增网站
1. 进入后台管理 → 网站管理
2. 填写网站URL（必须包含http://或https://）
3. 点击"自动获取信息"按钮
4. 系统自动填充相关信息
5. 选择分类并保存

#### 批量导入书签
1. 进入后台管理 → 导入书签
2. 上传浏览器导出的HTML书签文件
3. 选择导入选项（推荐全部勾选）
4. 点击"开始导入"
5. 系统自动处理并获取所有网站信息
- **前端**：Tailwind CSS + Alpine.js
- **数据库**：SQLite（默认）/ MySQL
- **部署**：Docker + Docker Compose
- **服务器**：Apache

## 🔧 配置说明

### 环境变量

```env
APP_NAME=AstroGuide
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8080

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/navigation.sqlite
```

### 自定义配置

1. **修改端口**：编辑 `docker-compose.yml` 中的端口映射
2. **更换数据库**：修改 `DB_CONNECTION` 为 `mysql`
3. **自定义主题**：修改 `resources/views/` 中的CSS变量

## 📈 数据统计

系统会自动记录以下数据：
- 网站访问次数
- 每日访问统计
- 用户活跃度
- 热门网站排行

## 🤝 贡献指南

1. Fork 项目
2. 创建功能分支
3. 提交更改
4. 推送到分支
5. 创建 Pull Request

## 📄 许可证

MIT License

## 🆘 支持

如有问题，请提交 Issue 或联系开发者。 
