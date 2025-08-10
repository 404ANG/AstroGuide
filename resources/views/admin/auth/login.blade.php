<!DOCTYPE html>
<html lang="zh-CN" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - AstroGuide</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- 网站图标设置 -->
    <link rel="icon" type="image/x-icon" href="{{ $settings['site_icon'] ?? '/images/AstroGuide-icon.png' }}" />
    <link rel="shortcut icon" type="image/x-icon" href="{{ $settings['site_icon'] ?? '/images/AstroGuide-icon.png' }}" />
    <link rel="apple-touch-icon" href="{{ $settings['site_icon'] ?? '/images/AstroGuide-icon.png' }}" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen text-white flex items-center justify-center">
    <div class="w-full max-w-md px-6">
        <!-- 登录卡片 -->
        <div class="glass-card rounded-2xl p-8 shadow-2xl">
            <!-- 标题区域 -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-primary-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="text-white text-2xl font-bold">N</span>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">AstroGuide</h1>
                <p class="text-gray-400">管理员登录</p>
            </div>
            
            <!-- 登录表单 -->
            <form method="POST" action="{{ route('admin.login') }}" class="space-y-6">
                @csrf
                
                <!-- 用户名输入 -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-300 mb-2">
                        用户名
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="{{ old('username') }}"
                        class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                        placeholder="请输入用户名"
                        required
                        autofocus
                    >
                    @error('username')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- 密码输入 -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                        密码
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                        placeholder="请输入密码"
                        required
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- 登录按钮 -->
                <button 
                    type="submit" 
                    class="w-full bg-primary-500 hover:bg-primary-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-gray-900"
                >
                    登录
                </button>
            </form>
            
            <!-- 返回首页链接 -->
            <div class="text-center mt-6">
                <a href="{{ route('home') }}" class="text-gray-400 hover:text-white text-sm transition-colors">
                    ← 返回首页
                </a>
            </div>
            
            <!-- 默认登录信息提示 -->
            <div class="mt-6 p-4 bg-blue-500/20 border border-blue-500/30 rounded-lg">
                <p class="text-sm text-blue-300">
                    <strong>默认登录信息：</strong><br>
                    用户名：admin<br>
                    密码：admin123
                </p>
            </div>
        </div>
        
        <!-- 页脚信息 -->
        <div class="text-center mt-8">
            <p class="text-gray-500 text-sm">
                © 2025 AstroGuide. 保留所有权利。
            </p>
        </div>
    </div>
    
    <!-- 成功消息提示 -->
    @if(session('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif
</body>
</html> 