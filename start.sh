#!/bin/bash

echo "🚀 启动AstroGuide..."

# 检查Docker是否安装
if ! command -v docker &> /dev/null; then
    echo "❌ Docker未安装，请先安装Docker"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose未安装，请先安装Docker Compose"
    exit 1
fi

# 创建必要的目录
echo "📁 创建必要目录..."
mkdir -p data
mkdir -p uploads
mkdir -p database

# 启动服务
echo "🐳 启动Docker服务..."
docker-compose up -d

# 等待服务启动
echo "⏳ 等待服务启动..."
sleep 10

# 检查服务状态
if docker-compose ps | grep -q "Up"; then
    echo "✅ 服务启动成功！"
    echo ""
    echo "🌐 访问地址："
    echo "   前台：http://localhost:8080"
    echo "   后台：http://localhost:8080/admin"
    echo ""
    echo "📝 使用说明："
    echo "   1. 首次访问后台会自动创建数据库"
    echo "   2. 可以在后台添加网站和分类"
    echo "   3. 前台支持搜索和分类筛选"
    echo ""
    echo "🛑 停止服务：docker-compose down"
else
    echo "❌ 服务启动失败，请检查日志："
    docker-compose logs
fi 