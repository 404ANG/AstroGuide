<!DOCTYPE html>
<html lang="zh-CN" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AstroGuide 管理') - 后台管理</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- 网站图标设置 -->
    <link rel="icon" type="image/x-icon" href="{{ $settings['site_icon'] ?? '/images/AstroGuide-icon.png' }}" />
    <link rel="shortcut icon" type="image/x-icon" href="{{ $settings['site_icon'] ?? '/images/AstroGuide-icon.png' }}" />
    <link rel="apple-touch-icon" href="{{ $settings['site_icon'] ?? '/images/AstroGuide-icon.png' }}" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- 首屏主题应用：避免闪烁，根据 localStorage 或系统偏好设置主题 -->
    <script>
      (function() {
        var key = 'theme';
        var stored = localStorage.getItem(key);
        var preferDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var useDark = stored ? (stored === 'dark') : preferDark;
        if (useDark) document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
      })();
    </script>

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
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
        
        .sidebar-glass {
            background: rgba(17, 24, 39, 0.8);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* 提示信息样式优化 */
        .message-enter {
            opacity: 0;
            transform: translateY(-20px);
        }
        
        .message-enter-active {
            opacity: 1;
            transform: translateY(0);
            transition: all 0.3s ease-out;
        }
        
        .message-exit {
            opacity: 1;
            transform: translateY(0);
        }
        
        .message-exit-active {
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease-in;
        }
        
        /* 提示信息悬停效果 */
        .message-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        /* 关闭按钮悬停效果 */
        .close-btn:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
    <div class="flex h-screen">
        <!-- 侧边栏 -->
        <div class="sidebar-glass w-64 flex-shrink-0">
            <div class="p-6">
                <!-- 修复：logo和站点名称居中显示 -->
                <div class="flex flex-col items-center space-y-3">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('images/AstroGuide-icon.png') }}" alt="AstroGuide" class="w-full h-full object-cover">
                    </div>
                    <!-- 网站名称链接到首页，居中显示 -->
                    <a href="{{ route('home') }}" class="text-xl font-bold hover:text-primary-400 transition-colors cursor-pointer text-center">
                        AstroGuide
                    </a>
                </div>
            </div>
            
            <nav class="mt-8">
                <div class="px-6 space-y-2">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-primary-500 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                        </svg>
                        <span>仪表板</span>
                    </a>
                    
                    <a href="{{ route('admin.sites') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.sites*') ? 'bg-primary-500 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                        </svg>
                        <span>网站管理</span>
                    </a>
                    
                    <a href="{{ route('admin.categories') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.categories*') ? 'bg-primary-500 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>分类管理</span>
                    </a>
                    
                    <a href="{{ route('admin.activity') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.activity*') ? 'bg-primary-500 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span>活跃统计</span>
                    </a>

                    <a href="{{ url('/admin/settings') }}"
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->is('admin/settings') ? 'bg-primary-500 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4m0-10V4m0 16v-2m-6-6H4m16 0h-2M7.05 7.05L5.64 5.64m12.72 12.72l-1.41-1.41M7.05 16.95l-1.41 1.41m12.72-12.72l-1.41 1.41"/>
                        </svg>
                        <span>系统设置</span>
                    </a>

                    <a href="{{ route('admin.import.bookmarks') }}"
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.import.bookmarks') ? 'bg-primary-500 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 0V4m0 4h4m-4 0H8m-6 8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v8z"/>
                        </svg>
                        <span>导入书签</span>
                    </a>

                    <a href="{{ route('admin.performance') }}"
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.performance*') ? 'bg-primary-500 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>性能监控</span>
                    </a>
                </div>
            </nav>
        </div>
        
        <!-- 主内容区域 -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- 顶部栏 -->
            <header class="glass-effect border-b border-gray-700">
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <h1 class="text-2xl font-bold">@yield('page-title', '仪表板')</h1>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- 主题切换 -->
                        <button id="theme-toggle" class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                        </button>
                        
                        <!-- 用户头像和登出 -->
                        <div class="flex items-center space-x-4">
                            <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white font-bold">A</span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-sm font-medium text-white truncate">{{ session('admin_username', '管理员') }}</span>
                                <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-gray-400 hover:text-red-400 transition-colors hover:underline">
                                        退出登录
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- 内容区域 -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div id="success-message" class="message-container mb-4 p-4 bg-green-500 text-white rounded-lg flex items-center justify-between transition-all duration-300 shadow-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        {{ session('success') }}
                        </div>
                        <button onclick="closeMessage('success-message')" class="close-btn ml-4 text-white hover:text-green-200 transition-all duration-200 p-1 rounded-full hover:bg-green-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div id="error-message" class="message-container mb-4 p-4 bg-red-500 text-white rounded-lg flex items-center justify-between transition-all duration-300 shadow-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        {{ session('error') }}
                        </div>
                        <button onclick="closeMessage('error-message')" class="close-btn ml-4 text-white hover:text-red-200 transition-all duration-200 p-1 rounded-full hover:bg-red-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div id="warning-message" class="message-container mb-4 p-4 bg-yellow-500 text-white rounded-lg flex items-center justify-between transition-all duration-300 shadow-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            {{ session('warning') }}
                        </div>
                        <button onclick="closeMessage('warning-message')" class="close-btn ml-4 text-white hover:text-yellow-200 transition-all duration-200 p-1 rounded-full hover:bg-yellow-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif
                
                @if(session('info'))
                    <div id="info-message" class="message-container mb-4 p-4 bg-blue-500 text-white rounded-lg flex items-center justify-between transition-all duration-300 shadow-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ session('info') }}
                        </div>
                        <button onclick="closeMessage('info-message')" class="close-btn ml-4 text-white hover:text-blue-200 transition-all duration-200 p-1 rounded-full hover:bg-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <script>
        // 主题切换功能（持久化到 localStorage）
        (function(){
          const btn = document.getElementById('theme-toggle');
          if (!btn) return;
          btn.addEventListener('click', function() {
              const root = document.documentElement;
              const nextIsDark = !root.classList.contains('dark');
              root.classList.toggle('dark', nextIsDark);
              localStorage.setItem('theme', nextIsDark ? 'dark' : 'light');
          });
        })();

        // 提示信息自动消失功能
        document.addEventListener('DOMContentLoaded', function() {
            // 自动隐藏成功提示（5秒后）
            const successMessage = document.getElementById('success-message');
            if (successMessage) {
                setTimeout(function() {
                    fadeOutMessage(successMessage);
                }, 5000);
            }

            // 自动隐藏错误提示（8秒后）
            const errorMessage = document.getElementById('error-message');
            if (errorMessage) {
                setTimeout(function() {
                    fadeOutMessage(errorMessage);
                }, 8000);
            }

            // 自动隐藏警告提示（6秒后）
            const warningMessage = document.getElementById('warning-message');
            if (warningMessage) {
                setTimeout(function() {
                    fadeOutMessage(warningMessage);
                }, 6000);
            }

            // 自动隐藏信息提示（7秒后）
            const infoMessage = document.getElementById('info-message');
            if (infoMessage) {
                setTimeout(function() {
                    fadeOutMessage(infoMessage);
                }, 7000);
            }
        });

        // 关闭提示信息
        function closeMessage(messageId) {
            const message = document.getElementById(messageId);
            if (message) {
                fadeOutMessage(message);
            }
        }

        // 淡出效果
        function fadeOutMessage(element) {
            element.style.opacity = '0';
            element.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                element.style.display = 'none';
            }, 300);
        }
    </script>
    
    @stack('scripts')
</body>
</html> 