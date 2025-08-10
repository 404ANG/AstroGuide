<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * 认证控制器
 * 处理管理员登录、登出等认证相关功能
 */
class AuthController extends Controller
{
    /**
     * 显示登录页面
     */
    public function showLoginForm(): View
    {
        return view('admin.auth.login');
    }
    
    /**
     * 处理登录请求
     */
    public function login(Request $request): RedirectResponse
    {
        // 验证输入
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        
        // 简单的硬编码验证（实际项目中应该使用数据库和加密密码）
        $adminUsername = config('app.admin_username', 'admin');
        $adminPassword = config('app.admin_password', 'admin123');
        
        if ($request->username === $adminUsername && $request->password === $adminPassword) {
            // 登录成功，设置session
            session(['admin_logged_in' => true]);
            session(['admin_username' => $request->username]);
            
            // 重定向到后台首页
            return redirect()->route('admin.dashboard')->with('success', '登录成功！');
        }
        
        // 登录失败
        return back()->withErrors([
            'username' => '用户名或密码错误',
        ])->withInput($request->only('username'));
    }
    
    /**
     * 处理登出请求
     */
    public function logout(): RedirectResponse
    {
        // 清除session
        session()->forget(['admin_logged_in', 'admin_username']);
        
        // 重定向到登录页面
        return redirect()->route('admin.login')->with('success', '已成功登出！');
    }
} 