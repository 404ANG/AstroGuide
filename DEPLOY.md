# 部署说明

## 🐳 Docker部署（推荐）

### 前置要求
- Docker 20.10+
- Docker Compose 2.0+

### 快速部署

1. **克隆项目**
```bash
git clone <repository-url>
cd nas-navigation
```

2. **启动服务**
```bash
# 使用启动脚本（推荐）
chmod +x start.sh
./start.sh

# 或手动启动
docker-compose up -d
```

3. **访问网站**
- 前台：http://localhost:8080
- 后台：http://localhost:8080/admin

### 自定义配置

1. **修改端口**
编辑 `docker-compose.yml`：
```yaml
ports:
  - "8080:80"  # 修改8080为其他端口
```

2. **更换数据库**
编辑 `docker-compose.yml` 中的环境变量：
```yaml
environment:
  - DB_CONNECTION=mysql  # 改为mysql
  - DB_HOST=mysql
  - DB_DATABASE=navigation
  - DB_USERNAME=root
  - DB_PASSWORD=password
```

## 🔧 手动部署

### 前置要求
- PHP 8.1+
- Composer 2.0+
- SQLite 3.x 或 MySQL 5.7+

### 部署步骤

1. **安装依赖**
```bash
composer install --no-dev --optimize-autoloader
```

2. **配置环境**
```bash
cp .env.example .env
php artisan key:generate
```

3. **数据库迁移**
```bash
php artisan migrate
php artisan db:seed  # 可选：填充示例数据
```

4. **设置权限**
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

5. **启动服务**
```bash
php artisan serve --host=0.0.0.0 --port=8080
```

## 📊 数据初始化

### 自动填充示例数据

```bash
# Docker环境
docker-compose exec nas-navigation php artisan db:seed

# 手动环境
php artisan db:seed
```

### 手动添加数据

1. 访问后台：http://localhost:8080/admin
2. 添加分类和网站
3. 设置网站logo和描述

## 🔍 故障排除

### 常见问题

1. **端口被占用**
```bash
# 查看端口占用
netstat -tulpn | grep 8080

# 修改端口
vim docker-compose.yml
```

2. **权限问题**
```bash
# 修复权限
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

3. **数据库连接失败**
```bash
# 检查数据库文件
ls -la database/

# 重新创建数据库
rm database/navigation.sqlite
php artisan migrate
```

4. **Docker服务启动失败**
```bash
# 查看日志
docker-compose logs

# 重新构建
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### 日志查看

```bash
# Docker日志
docker-compose logs -f

# Laravel日志
tail -f storage/logs/laravel.log
```

## 🔄 更新部署

1. **备份数据**
```bash
# 备份数据库
cp database/navigation.sqlite backup/
```

2. **更新代码**
```bash
git pull origin main
```

3. **重新部署**
```bash
# Docker环境
docker-compose down
docker-compose up -d --build

# 手动环境
composer install
php artisan migrate
```

## 📈 性能优化

1. **启用缓存**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

2. **优化数据库**
```bash
# 定期清理日志
php artisan activity:clean
```

3. **监控资源**
```bash
# 查看容器资源使用
docker stats
```

## 🔒 安全建议

1. **修改默认配置**
- 更改默认端口
- 设置强密码
- 启用HTTPS

2. **定期备份**
- 数据库备份
- 代码备份
- 配置文件备份

3. **访问控制**
- 限制后台访问IP
- 设置访问密码
- 启用日志记录 