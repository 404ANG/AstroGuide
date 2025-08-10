<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['site_name'] ?? 'AstroGuide' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- 网站图标设置 -->
    <link rel="icon" type="image/x-icon" href="{{ $settings['site_icon'] ?? '/images/AstroGuide-icon.png' }}" />
    <link rel="shortcut icon" type="image/x-icon" href="{{ $settings['site_icon'] ?? '/images/AstroGuide-icon.png' }}" />
    <link rel="apple-touch-icon" href="{{ $settings['site_icon'] ?? '/images/AstroGuide-icon.png' }}" />
    
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
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
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
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        /* 取消某些卡片的悬浮效果 */
        .no-hover:hover { transform: none; box-shadow: none; }

        /* 信息卡片悬浮时的交互（浮动+放大轻微） */
        .site-float:hover { 
            transform: translateY(-4px) scale(1.02); 
            box-shadow: 0 12px 28px rgba(0,0,0,.25);
            border-color: rgba(255, 255, 255, 0.3);
        }

        /* 分类卡片特殊样式 */
        .category-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .category-card:hover::before {
            left: 100%;
        }

        .category-card:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.25);
        }

        /* 网站卡片增强样式 */
        .site-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.06) 0%, rgba(255, 255, 255, 0.02) 100%);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .site-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .site-card:hover::after {
            opacity: 1;
        }

        /* 分组标题增强样式 */
        .section-title {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .section-title:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .section-title::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            border-radius: inherit;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .section-title:hover::before {
            opacity: 1;
        }

        /* 加载动画 */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* 脉冲动画 */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* 通知系统样式 */
        #notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 99999;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #notification > div {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        #notification.show {
            transform: translateX(0) !important;
            opacity: 1 !important;
        }

        #notification.hide {
            transform: translateX(100%) !important;
            opacity: 0 !important;
        }

        /* 确保通知系统在顶部栏之上 */
        .header-glass {
            z-index: 50;
        }

        #notification {
            z-index: 99999 !important;
        }
        
        /* 联想下拉 */
        .suggest-box { position:absolute; top:100%; left:0; right:0; background:rgba(17,24,39,.95); border:1px solid rgba(255,255,255,.1); border-radius:.5rem; margin-top:.25rem; z-index:50; max-height:240px; overflow:auto; }
        .suggest-item { padding:.5rem .75rem; cursor:pointer; }
        .suggest-item:hover { background:rgba(255,255,255,.06); }
        /*状态栏模糊度 */
        .header-glass {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(35px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.25);
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .scroll-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .scroll-to-top.show {
            opacity: 1;
            visibility: visible;
        }
        
        /* 轮播高度：随窗口自适应，最小 320px，理想 52vh，最大 680px */
        .hero-height { height: clamp(320px, 52vh, 680px); }
        
        /* 自定义网格列：固定卡片宽度 290px */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 270px));
            gap: 1rem;
            justify-content: center; /* 居中对齐 */
            max-width: 100%;
            margin: 0 auto;
            padding: 0 1rem; /* 添加基础左右边距 */
        }
        
        /* 响应式边距优化 */
        @media (min-width: 640px) {
            .card-grid {
                padding: 0 1.5rem;
            }
        }
        
        @media (min-width: 1024px) {
            .card-grid {
                padding: 0 2rem;
            }
        }
        /* 隐藏滚动条（全局） */
        *::-webkit-scrollbar { width: 0; height: 0; }
        * { scrollbar-width: none; }

        /* 分类条容器的横向滚动隐藏但可滚 */
        .anchor-link::-webkit-scrollbar { display: none; }
        
        /* 平滑滚动效果 */
        html {
            scroll-behavior: smooth;
        }
        
        /* 锚点链接的平滑过渡效果 */
        .anchor-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            transform: translateZ(0); /* 启用硬件加速 */
        }
        
        .anchor-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .anchor-link:hover::before {
            left: 100%;
        }
        
        .anchor-link:hover {
            transform: translateY(-2px) translateZ(0);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .anchor-link:hover span:first-child {
            transform: scale(1.1);
        }
        
        /* 锚点链接激活状态 */
        .anchor-link.active {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }
        
        /* 分组标题样式优化 */
        .grouped-sections section {
            position: relative;
        }
        
        .grouped-sections section:not(:first-child)::before {
            content: '';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        }
        
        /* 分组标题悬浮效果 */
        .grouped-sections h2 {
            transition: all 0.3s ease;
        }
        
        .grouped-sections h2:hover {
            transform: scale(1.05);
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        /* Alpine.js x-cloak 指令样式 - 防止页面刷新时元素闪烁 */
        [x-cloak] {
            display: none !important;
        }
        
        /* 分类锚点条响应式边距优化 */
        @media (max-width: 640px) {
            .glass-card {
                margin-left: 1rem;
                margin-right: 1rem;
            }
        }
        
        /* 确保分类卡片在小屏幕上有合适的边距 */
        @media (max-width: 768px) {
            .category-card {
                margin: 0.25rem;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen text-white">
    <!-- 顶部栏 -->
    <header class="header-glass sticky top-0 z-50">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 relative">
                <div class="flex items-center space-x-4">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('images/AstroGuide-icon.png') }}" alt="AstroGuide" class="w-full h-full object-cover">
                    </div>
                    <!-- 修复：添加链接到首页 -->
                    <a href="{{ route('home') }}" class="text-xl font-bold hover:text-primary-400 transition-colors cursor-pointer">
                        {{ $settings['site_name'] ?? 'AstroGuide' }}
                    </a>
                </div>
                
                <div class="flex items-center space-x-3">
                    <!-- 搜索框（顶部栏正中） -->
                    <div class="absolute left-1/2 -translate-x-1/2">
                        <div class="relative">
                          <input type="text" 
                                 id="search" 
                                 placeholder="搜索网站..." 
                                 class="w-[520px] max-w-[70vw] px-5 py-2.5 bg-white/10 border border-white/20 rounded-full text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
                          <svg class="absolute right-4 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                          </svg>
                        </div>
                    </div>
                    
                    <!-- 主题切换 -->
                    <button id="theme-toggle" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                    
                    <!-- 用户头像 + 下拉菜单 -->
                    <div x-data="{open:false}" class="relative flex-shrink-0">
                      <button @click="open=!open" class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center focus:outline-none hover:bg-primary-600 transition-colors">
                        <span class="text-white font-bold">U</span>
                      </button>
                      <div x-show="open" 
                           x-cloak
                           x-transition:enter="transition ease-out duration-200"
                           x-transition:enter-start="opacity-0 scale-95"
                           x-transition:enter-end="opacity-100 scale-100"
                           x-transition:leave="transition ease-in duration-150"
                           x-transition:leave-start="opacity-100 scale-100"
                           x-transition:leave-end="opacity-0 scale-95"
                           @click.outside="open=false" 
                           @keydown.escape.window="open=false"
                           class="absolute right-0 mt-2 w-44 rounded-lg glass-effect p-2 shadow-xl border border-white/10">
                        <a href="{{ route('admin.login') }}" class="block px-3 py-2 rounded hover:bg-white/10 transition-colors text-sm">
                          <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826-3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            后台管理
                          </div>
                        </a>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- 主要内容 -->
    <main class="w-full px-0 lg:px-0 py-0">
        <!-- 大图轮播模块（替换原欢迎模块） -->
        <div x-data="carousel()" class="relative w-full hero-height overflow-hidden -mt-16">
          <!-- 图片列表 -->
          <template x-for="(item, idx) in items" :key="idx">
            <div x-show="current === idx" x-transition.opacity
                 class="absolute inset-0">
              <a :href="item.link || '#'" target="_blank" class="block absolute inset-0">
                <img :src="item.image" alt="banner" class="w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black/40"></div>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
                  <h2 class="text-3xl md:text-4xl font-bold mb-3" x-text="item.title"></h2>
                  <p class="text-base md:text-lg text-white/80" x-text="item.subtitle"></p>
                </div>
              </a>
            </div>
          </template>
          <!-- 指示器 -->
          <div class="absolute bottom-4 left-0 right-0 flex items-center justify-center gap-2">
            <template x-for="(item, idx) in items" :key="'dot-'+idx">
              <button @click="go(idx)" :class="current===idx?'bg-white':'bg-white/50'" class="w-2.5 h-2.5 rounded-full"></button>
            </template>
          </div>
          <!-- 左右切换 -->
          <button @click="prev" class="absolute left-3 top-1/2 -translate-y-1/2 p-2 rounded-full bg-black/40 hover:bg-black/60">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>
          </button>
          <button @click="next" class="absolute right-3 top-1/2 -translate-y-1/2 p-2 rounded-full bg-black/40 hover:bg-black/60">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
        
        <!-- 分类锚点条（点击滚动到分组） -->
        <div class="glass-card no-hover rounded-lg w-full max-w-[2433px] min-h-[60px] mx-auto mt-[20px] mb-[25px] p-2 flex items-center justify-center">
          <div class="flex items-center justify-center flex-wrap gap-2 sm:gap-3 overflow-x-auto max-w-full px-4 sm:px-6 lg:px-8">
            @foreach($categories as $category)
              <a href="#cat-{{ $category->id }}" class="category-card inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 ease-out anchor-link whitespace-nowrap">
                <span class="inline-block w-3 h-3 rounded-full transition-transform duration-300" style="background: {{ $category->color ?? '#9ca3af' }}"></span>
                <span class="font-medium">{{ $category->name }}</span>
                <span class="text-xs opacity-60 ml-1">({{ $category->sites->count() }})</span>
              </a>
            @endforeach
          </div>
        </div>
        
        <!-- 网站分组：按分类渲染，每组一个标题锚点 -->
        <div class="space-y-16 px-4 sm:px-6 lg:px-8" id="grouped-sections">
          @foreach($categories as $category)
            @if($category->sites->count() > 0)
            <section id="cat-{{ $category->id }}" class="scroll-mt-24 {{ $loop->first ? 'pt-8' : '' }}">
              <!-- 分组标题区域，增加上下间距 -->
              <div class="flex items-center gap-2 mb-8 justify-center pt-4 pb-2">
                <div class="section-title">
                  <span class="inline-block w-3 h-3 rounded-full" style="background: {{ $category->color ?? '#9ca3af' }}"></span>
                  <h2 class="text-xl font-bold text-white/90">{{ $category->name }}</h2>
                  <span class="text-sm text-white/60 bg-white/10 px-2 py-1 rounded-full">{{ $category->sites->count() }} 个网站</span>
                </div>
              </div>
              <div class="card-grid px-4 sm:px-6 lg:px-8">
                @foreach($category->sites as $site)
                  <a href="{{ route('visit', $site) }}" target="_blank" class="group block relative rounded-2xl site-card site-float px-4 py-3 h-[80px]" data-category="{{ $site->category_id }}" data-raw-url="{{ $site->url }}">
                    <button class="copy-btn absolute bottom-2 right-2 z-10 p-1.5 rounded-full bg-white/15 hover:bg-white/25 opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110" data-site-id="{{ $site->id }}" title="复制信息">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                    <div class="flex items-center gap-3 h-full">
                      @if(!empty($site->logo))
                        <div class="relative">
                          <img src="{{ $site->logo }}" alt="{{ $site->name }}" class="w-[52px] h-[52px] rounded-xl object-cover shadow-lg" onerror="this.replaceWith((function(){var s=document.createElement('div');s.innerHTML='\x3Csvg viewBox=\"0 0 1024 1024\" xmlns=\"http://www.w3.org/2000/svg\" class=\"w-[52px] h-[52px] rounded-xl\"\x3E\x3Cpath d=\"M725.333333 512H1024v455.082667a56.917333 56.917333 0 0 1-56.917333 56.917333H725.333333V512z\" fill=\"#8a8a8a\"\x3E\x3C/path\x3E\x3Cpath d=\"M640 1024H56.917333A56.917333 56.917333 0 0 1 0 967.082667V512h640v512zM1024 430.506667H0V56.917333C0 25.429333 25.429333 0 56.917333 0h910.165334c31.488 0 56.917333 25.429333 56.917333 56.917333v373.589334z\" fill=\"#8a8a8a\"\x3E\x3C/path\x3E\x3C/svg\x3E';return s.firstChild;})());">
                          <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-transparent to-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                      @else
                        <div class="relative">
                          <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" class="w-[52px] h-[52px] rounded-xl">
                            <path d="M725.333333 512H1024v455.082667a56.917333 56.917333 0 0 1-56.917333 56.917333H725.333333V512z" fill="#8a8a8a"></path>
                            <path d="M640 1024H56.917333A56.917333 56.917333 0 0 1 0 967.082667V512h640v512zM1024 430.506667H0V56.917333C0 25.429333 25.429333 0 56.917333 0h910.165334c31.488 0 56.917333 25.429333 56.917333 56.917333v373.589334z" fill="#8a8a8a"></path>
                          </svg>
                          <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-transparent to-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                      @endif
                      <div class="min-w-0 self-center flex-1">
                        <h3 class="text-sm font-semibold truncate group-hover:text-primary-300 transition-colors duration-200">{{ $site->name }}</h3>
                        <p class="mt-1 text-xs text-white/70 line-clamp-1 group-hover:text-white/90 transition-colors duration-200">{{ $site->description }}</p>
                      </div>
                    </div>
                  </a>
                @endforeach
              </div>
            </section>
            @endif
          @endforeach
        </div>

        <!-- 原有的"全部"平铺网格保留（用于搜索/筛选显示） -->
        <div class="card-grid hidden px-4 sm:px-6 lg:px-8" id="sites-grid">
          @foreach($sites as $site)
            <a href="{{ route('visit', $site) }}" target="_blank" class="group block relative rounded-2xl site-card site-float px-4 py-3 h-[80px]" data-category="{{ $site->category_id }}" data-raw-url="{{ $site->url }}">
              <button class="copy-btn absolute bottom-2 right-2 z-10 p-1.5 rounded-full bg-white/15 hover:bg-white/25 opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110" data-site-id="{{ $site->id }}" title="复制信息">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
              </button>
              <div class="flex items-center gap-3 h-full">
                @if(!empty($site->logo))
                  <div class="relative">
                    <img src="{{ $site->logo }}" alt="{{ $site->name }}" class="w-[52px] h-[52px] rounded-xl object-cover shadow-lg" onerror="this.replaceWith((function(){var s=document.createElement('div');s.innerHTML='\x3Csvg viewBox=\"0 0 1024 1024\" xmlns=\"http://www.w3.org/2000/svg\" class=\"w-[52px] h-[52px] rounded-xl\"\x3E\x3Cpath d=\"M725.333333 512H1024v455.082667a56.917333 56.917333 0 0 1-56.917333 56.917333H725.333333V512z\" fill=\"#8a8a8a\"\x3E\x3C/path\x3E\x3Cpath d=\"M640 1024H56.917333A56.917333 56.917333 0 0 1 0 967.082667V512h640v512zM1024 430.506667H0V56.917333C0 25.429333 25.429333 0 56.917333 0h910.165334c31.488 0 56.917333 25.429333 56.917333 56.917333v373.589334z\" fill=\"#8a8a8a\"\x3E\x3C/path\x3E\x3C/svg\x3E';return s.firstChild;})());">
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-transparent to-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                  </div>
                @else
                  <div class="relative">
                    <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" class="w-[52px] h-[52px] rounded-xl">
                      <path d="M725.333333 512H1024v455.082667a56.917333 56.917333 0 0 1-56.917333 56.917333H725.333333V512z" fill="#8a8a8a"></path>
                      <path d="M640 1024H56.917333A56.917333 56.917333 0 0 1 0 967.082667V512h640v512zM1024 430.506667H0V56.917333C0 25.429333 25.429333 0 56.917333 0h910.165334c31.488 0 56.917333 25.429333 56.917333 56.917333v373.589334z" fill="#8a8a8a"></path>
                    </svg>
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-transparent to-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                  </div>
                @endif
                <div class="min-w-0 self-center flex-1">
                  <h3 class="text-sm font-semibold truncate group-hover:text-primary-300 transition-colors duration-200">{{ $site->name }}</h3>
                  <p class="mt-1 text-xs text-white/70 line-clamp-1 group-hover:text-white/90 transition-colors duration-200">{{ $site->description }}</p>
                </div>
              </div>
            </a>
          @endforeach
        </div>
        
        <!-- 无结果提示 -->
        <div id="no-results" class="hidden text-center py-12 px-4 sm:px-6 lg:px-8">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <p class="text-xl text-gray-400">未找到相关网站</p>
        </div>
    </main>
    
    <!-- 返回顶部按钮 -->
    <button id="scroll-to-top" class="scroll-to-top p-3 bg-primary-500 hover:bg-primary-600 rounded-full text-white shadow-lg transition-all">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>
    
    <script>
        // 页面加载动画
        document.addEventListener('DOMContentLoaded', function() {
            // 为所有section添加淡入动画
            const sections = document.querySelectorAll('section[id^="cat-"]');
            sections.forEach((section, index) => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    section.style.transition = 'all 0.6s ease-out';
                    section.style.opacity = '1';
                    section.style.transform = 'translateY(0)';
                }, index * 100); // 错开动画时间
            });

            // 为分类卡片添加动画
            const categoryCards = document.querySelectorAll('.category-card');
            categoryCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.4s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'scale(1)';
                }, 200 + index * 50);
            });

            // 注释掉欢迎通知，避免页面刷新时弹出
            // setTimeout(() => {
            //     showNotification('欢迎使用 AstroGuide！', 'info');
            // }, 1000);
        });

        // 搜索功能
        document.getElementById('search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.site-card');
            let hasResults = false;
            
            cards.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                    hasResults = true;
                } else {
                    card.style.display = 'none';
                }
            });
            
            document.getElementById('no-results').classList.toggle('hidden', hasResults);
        });
        
        // 分类筛选
        document.querySelectorAll('.category-filter').forEach(button => {
            button.addEventListener('click', function() {
                const category = this.dataset.category;
                const cards = document.querySelectorAll('.site-card');
                
                // 更新按钮状态
                document.querySelectorAll('.category-filter').forEach(btn => {
                    btn.classList.remove('bg-primary-500', 'text-white');
                    btn.classList.add('bg-white/5');
                });
                if (category) {
                    this.classList.remove('bg-white/5');
                    this.classList.add('bg-primary-500', 'text-white');
                }
                // 没有“全部”按钮，不做 else 高亮处理
                
                // 筛选卡片
                let hasResults = false;
                cards.forEach(card => {
                    if (!category || card.dataset.category === category) {
                        card.style.display = 'block';
                        hasResults = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                document.getElementById('no-results').classList.toggle('hidden', hasResults);
            });
        });
        
        // 复制功能（优化版：直接从DOM获取数据，无需HTTP请求）
        document.querySelectorAll('.copy-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // 直接从当前卡片DOM中获取数据，无需HTTP请求
                const card = this.closest('.site-card');
                const name = card.querySelector('h3').textContent.trim();
                const description = card.querySelector('p').textContent.trim();
                
                                 // 获取网站真实链接（从 data-raw-url 属性），若不存在则退回 href
                 const raw = card.getAttribute('data-raw-url');
                 let url = (raw && raw.trim()) ? raw.trim() : '';
                 if (!url) {
                   const linkElement = card.closest('a');
                   url = linkElement ? linkElement.href : window.location.origin;
                 }
                 
                 // 检查简介信息，如果没有则添加默认文本
                let finalDescription = description;
                if (!finalDescription || finalDescription.trim() === '') {
                    finalDescription = '这个人很懒，没有填写网站简介信息';
                }
                
                const text = `网站名称：${name}\n网站链接：${url}\n网站简介：${finalDescription}`;
                
                // 使用现代剪贴板API，性能更好
                if (navigator.clipboard?.writeText) {
                    navigator.clipboard.writeText(text).then(() => {
                        // 复制成功，显示成功图标
                        this.showSuccessIcon();
                    }).catch(() => {
                        // 如果现代API失败，使用兜底方案
                        this.fallbackCopy(text);
                    });
                } else {
                    // 兜底：创建临时文本域复制
                    this.fallbackCopy(text);
                }
            });
        });
        
        // 显示成功图标的函数
        Element.prototype.showSuccessIcon = function() {
            const original = this.innerHTML;
            this.innerHTML = '<svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            showNotification('网站信息已复制到剪贴板', 'success');
            setTimeout(() => { 
                this.innerHTML = original; 
            }, 1000);
        };
        
        // 兜底复制函数
        Element.prototype.fallbackCopy = function(text) {
            try {
                const ta = document.createElement('textarea');
                ta.value = text; 
                ta.style.position = 'absolute';
                ta.style.left = '-9999px';
                document.body.appendChild(ta); 
                ta.select();
                document.execCommand('copy'); 
                document.body.removeChild(ta);
                
                // 显示成功图标
                this.showSuccessIcon();
            } catch (err) {
                console.error('复制失败', err);
                // 显示错误提示
                this.innerHTML = '<svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                showNotification('复制失败，请手动复制', 'error');
                setTimeout(() => { 
                    this.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>';
                }, 1000);
            }
        };
        
        // 返回顶部功能
        const scrollToTopBtn = document.getElementById('scroll-to-top');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('show');
            } else {
                scrollToTopBtn.classList.remove('show');
            }
        });
        
        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // 主题切换（持久化到 localStorage）
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
    </script>

    <script>
      // 轮播逻辑（Alpine）
      function carousel() {
        return {
          current: 0,
          items: @json($carouselItems ?? []),
          timer: null,
          init() {
            if (!Array.isArray(this.items) || this.items.length === 0) {
              this.items = []
            }
            this.play()
          },
          play() {
            this.stop()
            if (this.items.length > 1) {
              this.timer = setInterval(() => { this.next() }, 5000)
            }
          },
          stop() { if (this.timer) { clearInterval(this.timer); this.timer = null } },
          go(i) { this.current = i; this.play() },
          prev() { this.current = (this.current - 1 + this.items.length) % this.items.length },
          next() { this.current = (this.current + 1) % this.items.length },
        }
      }
    </script>

    <script>
      // 搜索：模糊 + 联想；输入时隐藏分组，仅展示平铺网格
      const searchInput = document.getElementById('search');
      const grouped = document.getElementById('grouped-sections');
      const flatGrid = document.getElementById('sites-grid');

      // 动态联想容器
      const suggest = document.createElement('div');
      suggest.className = 'suggest-box hidden';
      searchInput.parentElement.appendChild(suggest);

      function setViewMode(showFlat) {
        flatGrid.classList.toggle('hidden', !showFlat);
        grouped.classList.toggle('hidden', showFlat);
      }

      // 初始显示分组
      setViewMode(false);

      function buildSuggestions(matches) {
        suggest.innerHTML = '';
        if (!matches.length) { suggest.classList.add('hidden'); return; }
        matches.slice(0, 8).forEach(m => {
          const item = document.createElement('div');
          item.className = 'suggest-item';
          item.textContent = `${m.name}`;
          item.addEventListener('click', () => {
            searchInput.value = m.name;
            applyFilter();
            suggest.classList.add('hidden');
          });
          suggest.appendChild(item);
        });
        suggest.classList.remove('hidden');
      }

      function collectSiteData() {
        const data = [];
        document.querySelectorAll('#sites-grid .site-card, #grouped-sections .site-card').forEach(card => {
          const name = card.querySelector('h3')?.textContent?.trim() || '';
          const desc = card.querySelector('p')?.textContent?.trim() || '';
          data.push({ el: card, name, desc, cat: card.dataset.category });
        });
        return data;
      }

      const allSitesCache = collectSiteData();

      function applyFilter() {
        const term = (searchInput.value || '').toLowerCase().trim();
        // 空字符串：显示分组
        if (!term) { setViewMode(false); return; }
        // 有搜索词：显示平铺
        setViewMode(true);

        let hasResults = false;
        document.querySelectorAll('#sites-grid .site-card').forEach(c => c.style.display = 'none');

        allSitesCache.forEach(item => {
          const matched = item.name.toLowerCase().includes(term) || item.desc.toLowerCase().includes(term);
          const cloneInFlat = document.querySelector(`#sites-grid .site-card[data-category='${item.cat}'] h3`) ? null : null; // 保持占位逻辑简单
          if (matched) {
            // 在平铺网格中显示对应的卡片（复用 DOM：先尝试在平铺网格中找到同引用，否则跳过，由于我们同时生成了两份DOM，平铺内已有对应项）
            // 简化：直接在平铺网格中查找所有卡片并匹配标题
            document.querySelectorAll('#sites-grid .site-card').forEach(card => {
              const t = card.querySelector('h3')?.textContent?.toLowerCase() || '';
              const d = card.querySelector('p')?.textContent?.toLowerCase() || '';
              if (t === item.name.toLowerCase() || d === item.desc.toLowerCase()) {
                card.style.display = 'block';
                hasResults = true;
              }
            });
          }
        });
        document.getElementById('no-results')?.classList.toggle('hidden', hasResults);
      }

      // 输入监听：模糊 + 联想
      searchInput.addEventListener('input', () => {
        const term = (searchInput.value || '').toLowerCase().trim();
        if (!term) { suggest.classList.add('hidden'); setViewMode(false); return; }
        const matches = allSitesCache.filter(i => i.name.toLowerCase().includes(term) || i.desc.toLowerCase().includes(term));
        buildSuggestions(matches);
        applyFilter();
      });

      // 失焦隐藏联想
      searchInput.addEventListener('blur', () => setTimeout(()=>suggest.classList.add('hidden'), 150));

      // 锚点链接平滑跳转
      document.querySelectorAll('.anchor-link').forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          const targetId = this.getAttribute('href');
          const targetElement = document.querySelector(targetId);
          
          if (targetElement) {
            // 计算目标位置，考虑顶部导航栏的高度
            const headerHeight = document.querySelector('header').offsetHeight;
            const targetPosition = targetElement.offsetTop - headerHeight - 20; // 额外偏移20px
            
            // 平滑滚动到目标位置
            window.scrollTo({
              top: targetPosition,
              behavior: 'smooth'
            });
            
            // 添加点击反馈效果
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
              this.style.transform = '';
            }, 150);
          }
        });
      });
      
      // 滚动监听，高亮当前激活的锚点链接
      let scrollTimeout;
      window.addEventListener('scroll', () => {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
          const sections = document.querySelectorAll('section[id^="cat-"]');
          const anchorLinks = document.querySelectorAll('.anchor-link');
          
          // 移除所有激活状态
          anchorLinks.forEach(link => link.classList.remove('active'));
          
          // 找到当前可见的section
          let currentSection = null;
          const headerHeight = document.querySelector('header').offsetHeight;
          const scrollTop = window.pageYOffset + headerHeight + 100; // 偏移100px
          
          sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionBottom = sectionTop + section.offsetHeight;
            
            if (scrollTop >= sectionTop && scrollTop < sectionBottom) {
              currentSection = section;
            }
          });
          
          // 高亮对应的锚点链接
          if (currentSection) {
            const targetId = '#' + currentSection.id;
            const activeLink = document.querySelector(`.anchor-link[href="${targetId}"]`);
            if (activeLink) {
              activeLink.classList.add('active');
            }
          }
        }, 100); // 防抖处理
      });

      // 分类筛选按钮：点击时也切换到平铺视图
      document.querySelectorAll('.category-filter').forEach(button => {
        button.addEventListener('click', function() {
          setViewMode(true);
          const category = this.dataset.category;
          document.querySelectorAll('#sites-grid .site-card').forEach(card => {
            card.style.display = (card.dataset.category === category) ? 'block' : 'none';
          });
        });
      });
    </script>
    
    <!-- 页脚模块 -->
    <footer class="bg-white/5 backdrop-blur-sm border-t border-white/10 mt-20" style="height:75px; min-height:75px;">
      <div class="max-w-6xl mx-auto px-6 h-full">
        <div class="h-full grid grid-cols-1 md:grid-cols-3 items-center gap-1">
          <!-- 导航链接（动态列表，兼容旧配置） -->
          @if(!empty($footerLinks))
          <div class="order-2 md:order-1 flex items-center justify-center md:justify-start gap-3 text-xs md:text-sm">
            @php $links = ($footerLinks ?? []); @endphp
            @foreach($links as $i => $nav)
              <a href="{{ $nav['url'] ?? '#' }}" class="text-gray-300 hover:text-white transition-colors">{{ $nav['name'] ?? '' }}</a>
              @if($i < count($links ?? []) - 1)
                <span class="text-gray-500">|</span>
              @endif
            @endforeach
          </div>
          @else
          <div class="order-2 md:order-1"></div>
          @endif
          
          <!-- 社交平台（动态列表，兼容旧配置） -->
          @if(!empty($socialLinks))
          <div class="order-1 md:order-2 flex items-center justify-center gap-2">
            @php $list = ($socialLinks ?? []); @endphp
            @foreach($list as $s)
              <a href="{{ $s['url'] ?? '#' }}" class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-gray-800/60 hover:bg-gray-700/80 flex items-center justify-center transition-colors group" title="{{ $s['name'] ?? '' }}">
                <span class="w-3.5 h-3.5 md:w-4 md:h-4 text-gray-400 group-hover:text-white flex items-center justify-center font-semibold text-[10px] md:text-xs">
                  {{ strtoupper(substr($s['name'] ?? 'S', 0, 1)) }}
                </span>
              </a>
            @endforeach
          </div>
          @else
          <div class="order-1 md:order-2"></div>
          @endif
          
          <!-- 版权信息 + 备案号 -->
          <div class="order-3 md:order-3 text-center md:text-right text-gray-400 text-[11px] md:text-xs">
            @if(str_contains($copyright ?? 'Copyright©2025 · 404ANG', '404ANG'))
              {!! str_replace('404ANG', '<a href="https://github.com/404ANG" target="_blank" rel="noopener" class="hover:text-white transition-colors">404ANG</a>', $copyright ?? 'Copyright©2025 · 404ANG') !!}
            @else
              {!! $copyright ?? 'Copyright©2025 · <a href="https://github.com/404ANG" target="_blank" rel="noopener" class="hover:text-white transition-colors">404ANG</a>' !!}
            @endif
            @if(!empty($settings['icp_number']))
              <span class="mx-2">·</span>
              <a href="{{ $settings['icp_url'] ?? 'https://beian.miit.gov.cn/' }}" target="_blank" rel="noopener" class="hover:text-white no-underline">
                {{ $settings['icp_number'] }}
              </a>
            @endif
          </div>
        </div>
      </div>
    </footer>

    <!-- 通知系统 -->
    <div id="notification" class="fixed top-4 right-4 z-[99999] transform translate-x-full transition-all duration-300 ease-in-out">
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-lg px-4 py-3 shadow-xl">
            <div class="flex items-center gap-3">
                <div id="notification-icon" class="w-5 h-5 flex-shrink-0"></div>
                <span id="notification-text" class="text-white text-sm font-medium"></span>
            </div>
        </div>
    </div>

    <script>
        // 通知系统
        function showNotification(message, type = 'info') {
            const notification = document.getElementById('notification');
            const icon = document.getElementById('notification-icon');
            const text = document.getElementById('notification-text');
            
            if (!notification || !icon || !text) {
                console.error('通知系统元素未找到');
                return;
            }
            
            // 设置图标
            const icons = {
                success: '<svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
                error: '<svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
                info: '<svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            };
            
            icon.innerHTML = icons[type] || icons.info;
            text.textContent = message;
            
            // 移除之前的类
            notification.classList.remove('show', 'hide');
            
            // 强制重绘
            notification.offsetHeight;
            
            // 显示通知
            notification.classList.add('show');
            
            // 3秒后自动隐藏
            setTimeout(() => {
                notification.classList.remove('show');
                notification.classList.add('hide');
            }, 3000);
        }

        // 复制成功时显示通知
        Element.prototype.showSuccessIcon = function() {
            const original = this.innerHTML;
            this.innerHTML = '<svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            showNotification('网站信息已复制到剪贴板', 'success');
            setTimeout(() => { 
                this.innerHTML = original; 
            }, 1000);
        };
    </script>
</body>
</html> 