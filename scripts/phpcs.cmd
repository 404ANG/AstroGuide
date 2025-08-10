@ECHO OFF
REM 使用 Docker 运行 PHPCS，避免本机安装 PHP 的需求
REM 用法：在编辑器里将 phpcs 可执行路径指向此文件

setlocal
set PROJECT_DIR=%~dp0..\

REM 确保当前目录切到项目根，挂载正确路径
pushd "%PROJECT_DIR%"

docker run --rm -v %CD%:/app -w /app php:8.2-cli php vendor/squizlabs/php_codesniffer/bin/phpcs %*

popd
endlocal 