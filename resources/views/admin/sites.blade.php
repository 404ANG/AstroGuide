@extends('admin.layout')

@section('title', '网站管理')
@section('page-title', '网站管理')

@section('content')
<div class="space-y-6">
  <!-- 新增网站 -->
  <div class="glass-card rounded-xl p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-bold">新增网站</h3>
      <button onclick="toggleAddForm()" class="text-gray-400 hover:text-white transition-colors">
        <svg id="add-form-toggle" class="w-6 h-6 transform rotate-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>
    </div>
    
    <div id="add-form-container" class="space-y-4">
      <form action="{{ route('admin.sites.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      @csrf
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">网站名称 *</label>
          <input name="name" id="site-name" required placeholder="输入网站名称" class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">网站链接 *</label>
          <input name="url" id="site-url" type="url" required placeholder="https://example.com" class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">Logo地址</label>
          <div class="flex items-center gap-3">
            <div id="site-logo-preview" class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0 bg-gray-700 flex items-center justify-center">
              <span class="text-gray-400 text-xs font-bold">?</span>
            </div>
            <input name="logo" id="site-logo" placeholder="Logo图片链接（可选）" class="flex-1 px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
          </div>
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">分类选择 *</label>
          <input type="hidden" name="category_id" id="create-category-id" required />
          <div class="flex flex-wrap gap-2 p-3 bg-gray-800/30 rounded border border-gray-700 min-h-[44px]">
        @foreach($categories as $c)
              <button type="button" class="px-3 py-1.5 rounded-full border border-gray-700 hover:bg-white/10 text-sm category-chip transition-colors" data-id="{{ $c->id }}">
            <span class="inline-block w-2.5 h-2.5 rounded-full mr-2" style="background: {{ $c->color ?? '#9ca3af' }}"></span>{{ $c->name }}
          </button>
        @endforeach
      </div>
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">排序权重</label>
          <input name="sort_order" type="number" min="0" placeholder="数字越小越靠前" class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">网站简介</label>
          <textarea name="description" id="site-description" placeholder="网站功能描述（可选）" rows="3" class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors resize-none"></textarea>
        </div>
        
        <div class="md:col-span-3 flex items-center gap-3">
          <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-white font-medium transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            保存网站
          </button>
          <button type="button" id="auto-fetch-btn" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded-lg text-white transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            自动获取信息
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- 网站列表 -->
  <div class="glass-card rounded-xl p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-bold">网站列表</h3>
      <div class="flex items-center gap-2">
        <button id="bulk-fetch-btn" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded text-white text-sm flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          批量自动获取信息
        </button>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-gray-300 border-b border-gray-700">
            <th class="py-2 pr-3">名称</th>
            <th class="py-2 pr-3">链接</th>
            <th class="py-2 pr-3">分类</th>
            <th class="py-2 pr-3">简介</th>
            <th class="py-2 pr-3">排序</th>
            <th class="py-2 pr-3">访问</th>
            <th class="py-2 pr-3">操作</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
          @foreach($sites as $s)
          <tr class="hover:bg-gray-800/30 transition-colors">
            <td class="py-3 pr-3 flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0">
                @if($s->logo)
                  <img src="{{ $s->logo }}" class="w-full h-full object-cover" alt="logo" 
                       onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                  <div class="w-full h-full bg-gray-700 flex items-center justify-center text-gray-400 text-xs font-bold" style="display: none;">
                    {{ strtoupper(substr($s->name, 0, 1)) }}
                  </div>
                @else
                  <div class="w-full h-full bg-gray-700 flex items-center justify-center text-gray-400 text-xs font-bold">
                    {{ strtoupper(substr($s->name, 0, 1)) }}
                  </div>
                @endif
              </div>
              <span class="font-medium">{{ $s->name }}</span>
            </td>
            <td class="py-3 pr-3 text-primary-400 truncate max-w-[240px]">
              <a href="{{ $s->url }}" target="_blank" class="hover:text-primary-300 transition-colors">{{ $s->url }}</a>
            </td>
            <td class="py-3 pr-3">
              @if($s->category)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs" style="background: {{ $s->category->color ?? '#9ca3af' }}20; color: {{ $s->category->color ?? '#9ca3af' }}">
                  <span class="w-2 h-2 rounded-full mr-1" style="background: {{ $s->category->color ?? '#9ca3af' }}"></span>
                  {{ $s->category->name }}
                </span>
              @else
                <span class="text-gray-500 text-xs">未分类</span>
              @endif
            </td>
            <td class="py-3 pr-3 text-xs text-gray-400 max-w-[320px]"><span class="line-clamp-2">{{ $s->description ?: '—' }}</span></td>
            <td class="py-3 pr-3">{{ $s->sort_order ?? 0 }}</td>
            <td class="py-3 pr-3">
              <span class="text-sm text-gray-400">{{ $s->visits ?? 0 }} 次</span>
            </td>
            <td class="py-3 pr-3">
              <div class="flex items-center gap-2">
                <button onclick="openEditModalFromButton(this)" 
                        data-id="{{ $s->id }}" 
                        data-name="{{ $s->name }}" 
                        data-url="{{ $s->url }}" 
                        data-logo="{{ $s->getRawOriginal('logo') }}" 
                        data-category-id="{{ $s->category_id ?? '' }}" 
                        data-sort-order="{{ $s->sort_order ?? 0 }}" 
                        data-description="{{ $s->description ?? '' }}"
                        class="px-3 py-1.5 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm transition-colors flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  编辑
                </button>
                <form action="{{ route('admin.sites.delete', $s) }}" method="POST" onsubmit="return confirm('确定删除该网站吗？')" class="inline">
                  @csrf
                  @method('DELETE')
                  <button class="px-3 py-1.5 rounded bg-red-600 hover:bg-red-700 text-white text-sm transition-colors flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    删除
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="mt-4">{{ $sites->links() }}</div>
    </div>
  </div>
</div>

<!-- 编辑网站弹窗 -->
<div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-gray-900 rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold">编辑网站</h3>
        <button onclick="closeEditModal()" class="text-gray-400 hover:text-white transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      
      <form id="edit-form" action="" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-300">网站名称 *</label>
            <input name="name" id="edit-name" required class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
          </div>
          
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-300">网站链接 *</label>
            <input name="url" id="edit-url" type="url" required class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
          </div>
          
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-300">Logo地址</label>
            <div class="flex items-center gap-3">
              <div id="edit-logo-preview" class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0 bg-gray-700 flex items-center justify-center">
                <span class="text-gray-400 text-xs font-bold">?</span>
              </div>
              <input name="logo" id="edit-logo" class="flex-1 px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" 
                     placeholder="Logo图片链接（可选）" />
            </div>
          </div>
          
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-300">排序权重</label>
            <input name="sort_order" id="edit-sort-order" type="number" min="0" class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
          </div>
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">分类选择 *</label>
          <input type="hidden" name="category_id" id="edit-category-id" required />
          <div class="flex flex-wrap gap-2 p-3 bg-gray-800/30 rounded border border-gray-700 min-h-[44px]">
            @foreach($categories as $c)
              <button type="button" class="px-3 py-1.5 rounded-full border border-gray-700 hover:bg-white/10 text-sm edit-category-chip transition-colors" data-id="{{ $c->id }}">
                <span class="inline-block w-2.5 h-2.5 rounded-full mr-2" style="background: {{ $c->color ?? '#9ca3af' }}"></span>{{ $c->name }}
              </button>
            @endforeach
          </div>
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">网站简介</label>
          <textarea name="description" id="edit-description" rows="3" class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors resize-none"></textarea>
        </div>
        
        <div class="flex items-center gap-3 pt-4">
          <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-white font-medium transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            保存修改
          </button>
          <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded-lg text-white transition-colors">
            取消
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
      <script>
// 分类选择器逻辑
        (function(){
          const chips = document.querySelectorAll('.category-chip');
          const hidden = document.getElementById('create-category-id');
          chips.forEach(chip => {
            chip.addEventListener('click', function(){
              chips.forEach(c=>c.classList.remove('bg-primary-500','text-white'));
              this.classList.add('bg-primary-500','text-white');
              hidden.value = this.dataset.id;
            });
          });
          
          // 初始化图标预览功能
          updateLogoPreview('site-logo', 'site-logo-preview');
          updateLogoPreview('edit-logo', 'edit-logo-preview');
        })();

// 编辑弹窗分类选择器
function initEditCategoryChips() {
  const chips = document.querySelectorAll('.edit-category-chip');
  const hidden = document.getElementById('edit-category-id');
  chips.forEach(chip => {
    chip.addEventListener('click', function(){
      chips.forEach(c=>c.classList.remove('bg-primary-500','text-white'));
      this.classList.add('bg-primary-500','text-white');
      hidden.value = this.dataset.id;
    });
  });
}

// 图标预览功能
function updateLogoPreview(inputId, previewId) {
  const input = document.getElementById(inputId);
  const preview = document.getElementById(previewId);
  
  if (!input || !preview) return;
  
  input.addEventListener('input', function() {
    const logoUrl = this.value.trim();
    if (logoUrl) {
      // 创建新的图片元素来测试URL
      const img = new Image();
      img.onload = function() {
        preview.innerHTML = `<img src="${logoUrl}" class="w-full h-full object-cover" alt="logo" />`;
      };
      img.onerror = function() {
        preview.innerHTML = '<span class="text-gray-400 text-xs font-bold">?</span>';
      };
      img.src = logoUrl;
    } else {
      preview.innerHTML = '<span class="text-gray-400 text-xs font-bold">?</span>';
    }
  });
}

// 新增表单展开/收起
function toggleAddForm() {
  const container = document.getElementById('add-form-container');
  const toggle = document.getElementById('add-form-toggle');
  
  if (container.classList.contains('hidden')) {
    container.classList.remove('hidden');
    toggle.classList.remove('rotate-180');
  } else {
    container.classList.add('hidden');
    toggle.classList.add('rotate-180');
  }
}

// 打开编辑弹窗
function openEditModal(id, name, url, logo, categoryId, sortOrder, description) {
  const modal = document.getElementById('edit-modal');
  const form = document.getElementById('edit-form');
  
  // 设置表单数据
  form.action = `/admin/sites/${id}`;
  document.getElementById('edit-name').value = name;
  document.getElementById('edit-url').value = url;
  document.getElementById('edit-logo').value = logo;
  document.getElementById('edit-sort-order').value = sortOrder;
  document.getElementById('edit-description').value = description;
  document.getElementById('edit-category-id').value = categoryId || '';
  
  // 重置分类选择器状态
  document.querySelectorAll('.edit-category-chip').forEach(chip => {
    chip.classList.remove('bg-primary-500', 'text-white');
    if (chip.dataset.id == categoryId) {
      chip.classList.add('bg-primary-500', 'text-white');
    }
  });
  
  // 更新图标预览
  const logoPreview = document.getElementById('edit-logo-preview');
  if (logo && logo.trim()) {
    const img = new Image();
    img.onload = function() {
      logoPreview.innerHTML = `<img src="${logo}" class="w-full h-full object-cover" alt="logo" />`;
    };
    img.onerror = function() {
      logoPreview.innerHTML = '<span class="text-gray-400 text-xs font-bold">?</span>';
    };
    img.src = logo;
  } else {
    logoPreview.innerHTML = '<span class="text-gray-400 text-xs font-bold">?</span>';
  }
  
  // 显示弹窗
  modal.classList.remove('hidden');
  initEditCategoryChips();
}

/**
 * 从按钮 data-* 读取数据并打开编辑弹窗
 * 说明：避免字符串拼接传参导致的转义问题
 */
function openEditModalFromButton(button) {
  const id = button.dataset.id;
  const name = button.dataset.name || '';
  const url = button.dataset.url || '';
  const logo = button.dataset.logo || '';
  const categoryId = button.dataset.categoryId || '';
  const sortOrder = Number(button.dataset.sortOrder || 0);
  const description = button.dataset.description || '';
  openEditModal(id, name, url, logo, categoryId, sortOrder, description);
}

// 关闭编辑弹窗
function closeEditModal() {
  document.getElementById('edit-modal').classList.add('hidden');
}

// 点击弹窗外部关闭
document.getElementById('edit-modal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeEditModal();
  }
});

// 自动获取网站信息
    (function(){
      const autoFetchBtn = document.getElementById('auto-fetch-btn');
      const urlInput = document.getElementById('site-url');
      const nameInput = document.getElementById('site-name');
      const logoInput = document.getElementById('site-logo');
      const descriptionInput = document.getElementById('site-description');
      
      autoFetchBtn.addEventListener('click', async function() {
        const url = urlInput.value.trim();
        if (!url) {
          alert('请先输入网站链接');
          return;
        }
        
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
          alert('请输入完整的网站链接，包含 http:// 或 https://');
          return;
        }
        
        // 显示加载状态
        autoFetchBtn.disabled = true;
        autoFetchBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> 获取中...';
        
        try {
          const response = await fetch('/api/website-info', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ url: url })
          });
          
          if (response.ok) {
            const data = await response.json();
            
            if (data.success) {
              // 自动填充信息
              if (data.title && !nameInput.value) {
                nameInput.value = data.title;
              }
              if (data.description && !descriptionInput.value) {
                descriptionInput.value = data.description;
              }
              if (data.icon && !logoInput.value) {
                logoInput.value = data.icon;
              }
              
              alert('网站信息获取成功！');
            } else {
              alert('无法获取网站信息，请手动填写');
            }
          } else {
            alert('获取网站信息失败，请检查网络连接');
          }
        } catch (error) {
          console.error('获取网站信息错误:', error);
          alert('获取网站信息时发生错误');
        } finally {
          // 恢复按钮状态
          autoFetchBtn.disabled = false;
      autoFetchBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> 自动获取信息';
        }
      });
    })();

// 批量自动获取网站信息
                      (function(){
  const btn = document.getElementById('bulk-fetch-btn')
  if (!btn) return
  btn.addEventListener('click', async () => {
    if (!confirm('将为缺少“简介/图标”的网站自动抓取信息，是否继续？')) return
    btn.disabled = true
    const prev = btn.innerHTML
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> 执行中...'
    try {
      const res = await fetch('{{ route('admin.sites.bulk-fetch') }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        },
        body: new URLSearchParams({ limit: '100', only_empty: '1' })
      })
      if (res.ok) {
        const data = await res.json()
        alert(`完成！共处理 ${data.total} 条，更新 ${data.updated} 条，失败 ${data.failed} 条。`)
        location.reload()
      } else {
        alert('请求失败，请稍后重试')
      }
    } catch (e) {
      alert('执行出现异常，请稍后重试')
    } finally {
      btn.disabled = false
      btn.innerHTML = prev
    }
  })
})()
                    </script>
@endpush 