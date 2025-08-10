@extends('admin.layout')

@section('title', '系统设置')
@section('page-title', '系统设置')

@section('content')
<div class="space-y-6">
  <!-- 网站基本信息设置 -->
  <div class="glass-card rounded-xl p-6">
    <h3 class="text-xl font-bold mb-4">网站基本信息</h3>
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-300 mb-1">网站名称</label>
          <input name="site_name" value="{{ $settings['site_name'] ?? 'AstroGuide' }}" placeholder="AstroGuide" 
                 class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-1">网站描述</label>
          <input name="site_description" value="{{ $settings['site_description'] ?? '发现和整理您喜爱的网站' }}" 
                 placeholder="发现和整理您喜爱的网站" 
                 class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-300 mb-1">网站图标</label>
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded overflow-hidden flex-shrink-0">
              @if(!empty($settings['site_icon']))
                <img src="{{ $settings['site_icon'] }}" alt="网站图标" 
                     class="w-full h-full object-cover" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                <div class="w-full h-full bg-gray-700 flex items-center justify-center text-gray-400 text-xs font-bold" style="display: none;">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                  </svg>
                </div>
              @else
                <div class="w-full h-full bg-gray-700 flex items-center justify-center text-gray-400 text-xs font-bold">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                  </svg>
                </div>
              @endif
            </div>
            <input type="file" name="site_icon" accept=".ico,.png,.jpg,.jpeg" 
                   class="flex-1 px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white file:mr-4 file:py-1 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-primary-500 file:text-white hover:file:bg-primary-600" />
          </div>
          <p class="text-xs text-gray-400 mt-1">支持 .ico, .png, .jpg, .jpeg 格式，最大 2MB</p>
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-1">管理员头像</label>
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0">
              @if(!empty($settings['admin_avatar']))
                <img src="{{ $settings['admin_avatar'] }}" alt="管理员头像" 
                     class="w-full h-full object-cover" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                <div class="w-full h-full bg-gray-700 flex items-center justify-center text-gray-400 text-xs font-bold" style="display: none;">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                  </svg>
                </div>
              @else
                <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center flex-shrink-0">
                  <span class="text-white font-bold text-sm">A</span>
                </div>
              @endif
            </div>
            <input type="file" name="admin_avatar" accept=".png,.jpg,.jpeg" 
                   class="flex-1 px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white file:mr-4 file:py-1 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-primary-500 file:text-white hover:file:bg-primary-600" />
          </div>
          <p class="text-xs text-gray-400 mt-1">支持 .png, .jpg, .jpeg 格式，最大 2MB</p>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-300 mb-1">ICP备案号</label>
          <input name="icp_number" value="{{ $settings['icp_number'] ?? '' }}" placeholder="例如：桂ICP备20001761号-1" 
                 class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-1">备案跳转链接</label>
          <input name="icp_url" value="{{ $settings['icp_url'] ?? 'https://beian.miit.gov.cn/' }}" placeholder="https://beian.miit.gov.cn/" 
                 class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
        </div>
      </div>
      <div>
        <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded text-white transition-colors">
          保存网站信息
        </button>
      </div>
    </form>
  </div>

  <!-- 轮播图与主/副标题+链接 -->
  <div class="glass-card rounded-xl p-6">
    <h3 class="text-xl font-bold mb-4">轮播图与标题配置</h3>
    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4" id="carousel-form">
      @csrf
      @php
        $carouselItems = (function(){ try { return json_decode($settings['carousel_items'] ?? '[]', true) ?: []; } catch (\Throwable $e) { return []; } })();
      @endphp
      <div id="carousel-list" class="space-y-3">
        @forelse($carouselItems as $i => $item)
          <div class="grid grid-cols-1 md:grid-cols-5 gap-3 p-3 rounded border border-gray-700 bg-gray-800/30">
            <input name="carousel_items[{{ $i }}][image]" value="{{ $item['image'] ?? '' }}" placeholder="图片地址 https://..." class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white md:col-span-2" />
            <input name="carousel_items[{{ $i }}][title]" value="{{ $item['title'] ?? '' }}" placeholder="主标题" class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
            <input name="carousel_items[{{ $i }}][subtitle]" value="{{ $item['subtitle'] ?? '' }}" placeholder="副标题" class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
            <div class="flex items-center gap-2">
              <input name="carousel_items[{{ $i }}][link]" value="{{ $item['link'] ?? '' }}" placeholder="可选：点击链接 https://..." class="flex-1 px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
              <button type="button" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white" onclick="removeCarouselItem(this)">删除</button>
            </div>
          </div>
        @empty
          <div class="text-sm text-gray-400">暂无轮播项，点击下方“新增一项”添加。</div>
        @endforelse
      </div>
      <div class="flex items-center gap-3">
        <button type="button" onclick="addCarouselItem()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded text-white">新增一项</button>
        <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded text-white">保存轮播配置</button>
      </div>
    </form>
  </div>

  <!-- 页脚导航设置（支持增删，保留旧配置兼容） -->
  <div class="glass-card rounded-xl p-6">
    <h3 class="text-xl font-bold mb-4">页脚导航设置</h3>
    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
      @csrf
      @php
        $footerLinks = (function(){ try { return json_decode($settings['footer_links'] ?? '[]', true) ?: []; } catch (\Throwable $e) { return []; } })();
      @endphp
      <div id="footer-links-list" class="space-y-3">
        @forelse($footerLinks as $i => $row)
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3 border border-gray-700 rounded-lg p-4">
            <input name="footer_links[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}" placeholder="名称"
                   class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
            <input name="footer_links[{{ $i }}][url]" value="{{ $row['url'] ?? '' }}" placeholder="链接 https://..."
                   class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
            <div class="flex items-center gap-2">
              <span class="text-xs text-gray-500">自定义链接</span>
              <button type="button" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white ml-auto" onclick="removeFooterLink(this)">删除</button>
            </div>
          </div>
        @empty
          <div class="text-sm text-gray-400">暂无链接，点击下方“新增一项”添加。</div>
        @endforelse
      </div>
      <div class="flex gap-3">
        <button type="button" onclick="addFooterLink()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded text-white">新增一项</button>
        <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded text-white">保存页脚导航</button>
      </div>

      <hr class="my-4 border-gray-700" />
      <p class="text-xs text-gray-500">兼容旧配置：仍可填写固定 4 项（可选）。</p>
      @php
        $footer = (function(){ try { return json_decode($settings['footer_navigation'] ?? '[]', true) ?: []; } catch (\Throwable $e) { return []; } })();
        $footerKeys = [ 'personal_page' => '个人页','website_navigation' => '网址导航','blog' => '博客','about_us' => '关于我们'];
      @endphp
      @foreach($footerKeys as $key => $label)
        @php
          $isDeleted = !isset($footer[$key]) || empty($footer[$key]['name']) || empty($footer[$key]['url']);
          $displayStyle = $isDeleted ? 'style="display: none;"' : '';
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 border border-gray-800 rounded-lg p-4" {!! $displayStyle !!}>
          <input name="footer_navigation[{{ $key }}][name]" value="{{ $footer[$key]['name'] ?? $label }}" placeholder="{{ $label }}"
                 class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
          <input name="footer_navigation[{{ $key }}][url]" value="{{ $footer[$key]['url'] ?? '#' }}" placeholder="#"
                 class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
          <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500">固定模块（兼容）</span>
            @if($isDeleted)
              <button type="button" class="px-3 py-2 rounded bg-green-600 hover:bg-green-700 text-white ml-auto" onclick="restoreFooterNavigationItem('{{ $key }}', this.closest('.grid'))">恢复</button>
              <input type="hidden" name="footer_navigation[{{ $key }}][deleted]" value="1" />
            @else
              <button type="button" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white ml-auto" onclick="removeFooterNavigationItem('{{ $key }}')">删除</button>
            @endif
          </div>
        </div>
      @endforeach
    </form>
  </div>

  <!-- 社交媒体设置（支持增删，保留固定平台兼容） -->
  <div class="glass-card rounded-xl p-6">
    <h3 class="text-xl font-bold mb-4">社交媒体设置</h3>
    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
      @csrf
      @php
        $socialLinks = (function(){ try { return json_decode($settings['social_links'] ?? '[]', true) ?: []; } catch (\Throwable $e) { return []; } })();
      @endphp
      <div id="social-links-list" class="space-y-3">
        @forelse($socialLinks as $i => $row)
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3 border border-gray-700 rounded-lg p-4">
            <input name="social_links[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}" placeholder="名称"
                   class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
            <input name="social_links[{{ $i }}][url]" value="{{ $row['url'] ?? '' }}" placeholder="链接 https://..."
                   class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
            <div class="flex items-center gap-2">
              <span class="text-xs text-gray-500">自定义链接</span>
              <button type="button" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white ml-auto" onclick="removeSocialLink(this)">删除</button>
            </div>
          </div>
        @empty
          <div class="text-sm text-gray-400">暂无链接，点击下方"新增一项"添加。</div>
        @endforelse
      </div>
      <div class="flex gap-3">
        <button type="button" onclick="addSocialLink()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded text-white">新增一项</button>
        <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded text-white">保存社交媒体</button>
      </div>

      <hr class="my-4 border-gray-700" />
      <p class="text-xs text-gray-500">兼容旧配置：仍可填写固定 5 项（可选）。</p>
      @php
        $social = (function(){ try { return json_decode($settings['social_media'] ?? '[]', true) ?: []; } catch (\Throwable $e) { return []; } })();
        $socialKeys = [
          'github' => 'GitHub',
          'bilibili' => 'Bilibili',
          'zhihu' => '知乎',
          'wechat' => '微信',
          'email' => '邮箱',
        ];
      @endphp
      @foreach($socialKeys as $key => $label)
        @php
          $isDeleted = !isset($social[$key]) || empty($social[$key]['name']) || empty($social[$key]['url']);
          $displayStyle = $isDeleted ? 'style="display: none;"' : '';
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 border border-gray-700 rounded-lg p-4" {!! $displayStyle !!}>
          <input name="social_media[{{ $key }}][name]" value="{{ $social[$key]['name'] ?? $label }}" placeholder="{{ $label }}"
                 class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
          <input name="social_media[{{ $key }}][url]" value="{{ $social[$key]['url'] ?? '#' }}" placeholder="# 或 mailto:contact@example.com"
                 class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
          <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500">固定平台（不可自定义图标 / 不支持微信二维码）</span>
            @if($isDeleted)
              <button type="button" class="px-3 py-2 rounded bg-green-600 hover:bg-green-700 text-white ml-auto" onclick="restoreSocialMediaItem('{{ $key }}', this.closest('.grid'))">恢复</button>
              <input type="hidden" name="social_media[{{ $key }}][deleted]" value="1" />
            @else
              <button type="button" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white ml-auto" onclick="removeSocialMediaItem('{{ $key }}')">删除</button>
            @endif
          </div>
        </div>
      @endforeach
    </form>
  </div>

  <!-- 修改密码（原样保留） -->
  <div class="glass-card rounded-xl p-6">
    <h3 class="text-xl font-bold mb-4">修改密码</h3>
    <form action="#" method="POST" class="space-y-4">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-300 mb-1">当前密码</label>
          <input type="password" name="current_password" 
                 class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-1">新密码</label>
          <input type="password" name="new_password" 
                 class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
        </div>
      </div>
      <div>
        <label class="block text-sm text-gray-300 mb-1">确认新密码</label>
        <input type="password" name="new_password_confirmation" 
               class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
      </div>
      <div>
        <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded text-white transition-colors">
          更新密码
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
// 动态新增一行轮播项
function addCarouselItem() {
  const list = document.getElementById('carousel-list')
  const index = list.querySelectorAll('.grid').length
  const wrapper = document.createElement('div')
  wrapper.className = 'grid grid-cols-1 md:grid-cols-5 gap-3 p-3 rounded border border-gray-700 bg-gray-800/30'
  wrapper.innerHTML = `
    <input name="carousel_items[${index}][image]" placeholder="图片地址 https://..." class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white md:col-span-2" />
    <input name="carousel_items[${index}][title]" placeholder="主标题" class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
    <input name="carousel_items[${index}][subtitle]" placeholder="副标题" class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
    <div class="flex items-center gap-2">
      <input name="carousel_items[${index}][link]" placeholder="可选：点击链接 https://..." class="flex-1 px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
      <button type="button" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white" onclick="removeCarouselItem(this)">删除</button>
    </div>
  `
  list.appendChild(wrapper)
}

// 删除当前轮播项行（仅前端移除，提交后即为删除）
function removeCarouselItem(btn) {
  const row = btn.closest('.grid')
  if (row) row.remove()
}

// 页脚链接增删
function addFooterLink() {
  const list = document.getElementById('footer-links-list')
  const index = list.querySelectorAll('.grid').length
  const row = document.createElement('div')
  row.className = 'grid grid-cols-1 md:grid-cols-3 gap-3 border border-gray-700 rounded-lg p-4'
  row.innerHTML = `
    <input name="footer_links[${index}][name]" placeholder="名称" class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
    <input name="footer_links[${index}][url]" placeholder="链接 https://..." class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
    <div class="flex items-center gap-2"> 
      <span class="text-xs text-gray-500">自定义链接</span>
      <button type="button" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white ml-auto" onclick="removeFooterLink(this)">删除</button>
    </div>
  `
  list.appendChild(row)
}

// 删除页脚链接行
function removeFooterLink(btn) {
  const row = btn.closest('.grid')
  if (row) row.remove()
}

// 社交媒体链接增删
function addSocialLink() {
  const list = document.getElementById('social-links-list')
  const index = list.querySelectorAll('.grid').length
  const row = document.createElement('div')
  row.className = 'grid grid-cols-1 md:grid-cols-3 gap-3 border border-gray-700 rounded-lg p-4'
  row.innerHTML = `
    <input name="social_links[${index}][name]" placeholder="名称" class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
    <input name="social_links[${index}][url]" placeholder="链接 https://..." class="px-3 py-2 rounded bg-gray-800/60 border border-gray-700 text-white" />
    <div class="flex items-center gap-2"> 
      <span class="text-xs text-gray-500">自定义链接</span>
      <button type="button" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white ml-auto" onclick="removeSocialLink(this)">删除</button>
    </div>
  `
  list.appendChild(row)
}

// 删除社交媒体链接行
function removeSocialLink(btn) {
  const row = btn.closest('.grid')
  if (row) row.remove()
}

// 删除固定页脚导航项
function removeFooterNavigationItem(key) {
  if (confirm('确定要删除这个固定导航项吗？删除后可以通过"新增一项"重新添加。')) {
    const row = event.target.closest('.grid')
    if (row) {
      // 清空输入框内容
      const inputs = row.querySelectorAll('input')
      inputs.forEach(input => {
        input.value = ''
      })
      // 隐藏行（不删除，保持表单结构）
      row.style.display = 'none'
      // 添加隐藏字段标记已删除
      const hiddenInput = document.createElement('input')
      hiddenInput.type = 'hidden'
      hiddenInput.name = `footer_navigation[${key}][deleted]`
      hiddenInput.value = '1'
      row.appendChild(hiddenInput)
      
      // 将删除按钮改为恢复按钮
      const deleteBtn = row.querySelector('button')
      if (deleteBtn) {
        deleteBtn.textContent = '恢复'
        deleteBtn.className = 'px-3 py-2 rounded bg-green-600 hover:bg-green-700 text-white ml-auto'
        deleteBtn.onclick = () => restoreFooterNavigationItem(key, row)
      }
    }
  }
}

// 恢复固定页脚导航项
function restoreFooterNavigationItem(key, row) {
  if (confirm('确定要恢复这个固定导航项吗？')) {
    // 显示行
    row.style.display = 'grid'
    // 移除删除标记
    const hiddenInput = row.querySelector(`input[name="footer_navigation[${key}][deleted]"]`)
    if (hiddenInput) {
      hiddenInput.remove()
    }
    // 将恢复按钮改回删除按钮
    const restoreBtn = row.querySelector('button')
    if (restoreBtn) {
      restoreBtn.textContent = '删除'
      restoreBtn.className = 'px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white ml-auto'
      restoreBtn.onclick = () => removeFooterNavigationItem(key)
    }
    // 清空输入框内容，让用户重新填写
    const inputs = row.querySelectorAll('input:not([type="hidden"])')
    inputs.forEach(input => {
      input.value = ''
    })
  }
}

// 删除固定社交媒体项
function removeSocialMediaItem(key) {
  if (confirm('确定要删除这个社交媒体项吗？删除后可以通过"新增一项"重新添加。')) {
    const row = event.target.closest('.grid')
    if (row) {
      // 清空输入框内容
      const inputs = row.querySelectorAll('input')
      inputs.forEach(input => {
        input.value = ''
      })
      // 隐藏行（不删除，保持表单结构）
      row.style.display = 'none'
      // 添加隐藏字段标记已删除
      const hiddenInput = document.createElement('input')
      hiddenInput.type = 'hidden'
      hiddenInput.name = `social_media[${key}][deleted]`
      hiddenInput.value = '1'
      row.appendChild(hiddenInput)
      
      // 将删除按钮改为恢复按钮
      const deleteBtn = row.querySelector('button')
      if (deleteBtn) {
        deleteBtn.textContent = '恢复'
        deleteBtn.className = 'px-3 py-2 rounded bg-green-600 hover:bg-green-700 text-white ml-auto'
        deleteBtn.onclick = () => restoreSocialMediaItem(key, row)
      }
    }
  }
}

// 恢复固定社交媒体项
function restoreSocialMediaItem(key, row) {
  if (confirm('确定要恢复这个社交媒体项吗？')) {
    // 显示行
    row.style.display = 'grid'
    // 移除删除标记
    const hiddenInput = row.querySelector(`input[name="social_media[${key}][deleted]"]`)
    if (hiddenInput) {
      hiddenInput.remove()
    }
    // 将恢复按钮改回删除按钮
    const restoreBtn = row.querySelector('button')
    if (restoreBtn) {
      restoreBtn.textContent = '删除'
      restoreBtn.className = 'px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white ml-auto'
      restoreBtn.onclick = () => removeSocialMediaItem(key)
    }
    // 清空输入框内容，让用户重新填写
    const inputs = row.querySelectorAll('input:not([type="hidden"])')
    inputs.forEach(input => {
      input.value = ''
    })
  }
}
</script>
@endpush
@endsection 