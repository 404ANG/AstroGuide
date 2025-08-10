@echo off
echo ========================================
echo    AstroGuide - 安装检查工具
echo ========================================
echo.

echo 🔍 检查PHP安装...
php --version >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ PHP已安装
    php --version
) else (
    echo ❌ PHP未安装
    echo.
    echo 📥 请下载并安装XAMPP：
    echo https://www.apachefriends.org/download.html
    echo.
    echo 📋 安装步骤：
    echo 1. 下载XAMPP
    echo 2. 安装到C:\xampp
    echo 3. 启动Apache服务
    echo 4. 添加C:\xampp\php到环境变量Path
    echo.
)

echo.
echo 🔍 检查Composer安装...
composer --version >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Composer已安装
    composer --version
) else (
    echo ❌ Composer未安装
    echo.
    echo 📥 请下载并安装Composer：
    echo https://getcomposer.org/download/
    echo.
    echo 📋 安装步骤：
    echo 1. 下载Composer-Setup.exe
    echo 2. 运行安装程序
    echo 3. 选择PHP路径：C:\xampp\php\php.exe
    echo 4. 完成安装
    echo.
)

echo.
echo 🔍 检查项目文件...
if exist composer.json (
    echo ✅ composer.json存在
) else (
    echo ❌ composer.json不存在
)

if exist artisan (
    echo ✅ artisan文件存在
) else (
    echo ❌ artisan文件不存在
)

echo.
echo 🔍 检查依赖...
if exist vendor (
    echo ✅ vendor目录存在（依赖已安装）
) else (
    echo ❌ vendor目录不存在（需要安装依赖）
    echo.
    echo 💡 运行以下命令安装依赖：
    echo composer install
)

echo.
echo ========================================
echo 📋 安装状态总结
echo ========================================

php --version >nul 2>&1
set php_installed=%errorlevel%

composer --version >nul 2>&1
set composer_installed=%errorlevel%

if exist vendor (
    set deps_installed=0
) else (
    set deps_installed=1
)

if %php_installed% equ 0 (
    if %composer_installed% equ 0 (
        if %deps_installed% equ 0 (
            echo ✅ 所有组件已安装，可以启动项目！
            echo.
            echo 🚀 运行以下命令启动项目：
            echo php artisan serve --host=0.0.0.0 --port=8080
            echo.
            echo 📱 然后访问：http://localhost:8080
        ) else (
            echo ⚠️  PHP和Composer已安装，但依赖未安装
            echo 💡 运行：composer install
        )
    ) else (
        echo ⚠️  PHP已安装，但Composer未安装
        echo 💡 请安装Composer
    )
) else (
    echo ❌ PHP未安装，请先安装XAMPP
)

echo.
pause 