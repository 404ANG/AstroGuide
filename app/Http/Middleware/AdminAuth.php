<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 管理员认证中间件
 * 用于保护后台管理路由，检查用户是否已登录
 */
class AdminAuth
{
    /**
     * 处理传入的请求
     * 
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 检查session中是否有管理员登录状态
        if (!session()->has('admin_logged_in')) {
            // 如果未登录，重定向到登录页面
            return redirect()->route('admin.login');
        }
        
        // 如果已登录，继续处理请求
        return $next($request);
    }
} 