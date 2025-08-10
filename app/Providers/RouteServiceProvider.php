<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * 路由服务提供者
 * 用于配置应用程序的路由
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * 应用程序的"home"路由路径
     * 通常，用户登录后会被重定向到这里
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * 定义应用程序的路由模型绑定、模式过滤器等
     */
    public function boot(): void
    {
        // 配置速率限制
        $this->configureRateLimiting();

        // 加载路由
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * 配置应用程序的速率限制
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
} 