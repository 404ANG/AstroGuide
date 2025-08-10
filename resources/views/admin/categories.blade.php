@extends('admin.layout')

@section('title', '分类管理')
@section('page-title', '分类管理')

@section('content')
<div class="space-y-6">
  <!-- 新增分类 -->
  <div class="glass-card rounded-xl p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-bold">新增分类</h3>
      <button onclick="toggleAddForm()" class="text-gray-400 hover:text-white transition-colors">
        <svg id="add-form-toggle" class="w-6 h-6 transform rotate-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>
    </div>
    
    <div id="add-form-container" class="space-y-4">
      <form action="{{ route('admin.categories.save') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      @csrf
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">分类名称 *</label>
          <input name="name" required placeholder="输入分类名称" class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">分类图标</label>
          <div class="relative">
            <input name="icon" id="icon-input" placeholder="选择图标" readonly class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors cursor-pointer" />
            <button type="button" onclick="openIconSelector()" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>
          </div>
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">主题颜色</label>
          <input name="color" placeholder="#3B82F6" class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">排序权重</label>
          <input name="sort_order" type="number" min="0" placeholder="数字越小越靠前" class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">分类简介</label>
          <textarea name="description" placeholder="分类功能描述（可选）" rows="3" class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors resize-none"></textarea>
        </div>
        
        <div class="md:col-span-3 flex items-center gap-3">
          <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-white font-medium transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            保存分类
          </button>
      </div>
    </form>
    </div>
  </div>

  <!-- 分类列表 -->
  <div class="glass-card rounded-xl p-6">
    <h3 class="text-xl font-bold mb-4">分类列表</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-gray-300 border-b border-gray-700">
            <th class="py-2 pr-3">图标</th>
            <th class="py-2 pr-3">名称</th>
            <th class="py-2 pr-3">描述</th>
            <th class="py-2 pr-3">颜色</th>
            <th class="py-2 pr-3">排序</th>
            <th class="py-2 pr-3">状态</th>
            <th class="py-2 pr-3">操作</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
          @foreach($categories as $c)
          <tr class="hover:bg-gray-800/30 transition-colors">
            <td class="py-3 pr-3">
              @if($c->icon)
                <i class="{{ $c->icon }} text-2xl" style="color: {{ $c->color }}"></i>
              @else
                <div class="w-8 h-8 bg-gray-600 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                  </svg>
                </div>
              @endif
            </td>
            <td class="py-3 pr-3">
              <span class="font-medium">{{ $c->name }}</span>
            </td>
            <td class="py-3 pr-3">
              <span class="text-gray-400 text-sm">{{ $c->description ?: '暂无描述' }}</span>
            </td>
            <td class="py-3 pr-3">
              @if($c->color)
                <div class="flex items-center gap-2">
                  <div class="w-4 h-4 rounded border border-gray-600" style="background: {{ $c->color }}"></div>
                  <span class="text-xs text-gray-400">{{ $c->color }}</span>
                </div>
              @else
                <span class="text-gray-500 text-xs">默认</span>
              @endif
            </td>
            <td class="py-3 pr-3">
              <span class="text-sm text-gray-400">{{ $c->sort_order ?? 0 }}</span>
            </td>
            <td class="py-3 pr-3">
              @if($c->is_active)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-500/20 text-green-400">
                  <span class="w-2 h-2 rounded-full bg-green-400 mr-1"></span>
                  启用
                </span>
              @else
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-500/20 text-red-400">
                  <span class="w-2 h-2 rounded-full bg-red-400 mr-1"></span>
                  禁用
                </span>
              @endif
            </td>
            <td class="py-3 pr-3">
              <div class="flex items-center gap-2">
                <button onclick="openEditModalFromButton(this)"
                        data-id="{{ $c->id }}"
                        data-name="{{ $c->name }}"
                        data-icon="{{ $c->icon }}"
                        data-color="{{ $c->color }}"
                        data-sort-order="{{ $c->sort_order ?? 0 }}"
                        data-description="{{ $c->description ?? '' }}"
                        data-active="{{ $c->is_active ? 1 : 0 }}"
                        class="px-3 py-1.5 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm transition-colors flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  编辑
                </button>
                <form action="{{ route('admin.categories.delete', $c) }}" method="POST" onsubmit="return confirm('确定删除该分类吗？')" class="inline">
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
      <div class="mt-4">{{ $categories->links() }}</div>
    </div>
  </div>
</div>

<!-- 编辑分类弹窗 -->
<div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-gray-900 rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold">编辑分类</h3>
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
            <label class="text-sm font-medium text-gray-300">分类名称 *</label>
            <input name="name" id="edit-name" required class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
          </div>
          
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-300">分类图标</label>
            <div class="relative">
              <input name="icon" id="edit-icon" placeholder="选择图标" readonly class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors cursor-pointer" />
              <button type="button" onclick="openIconSelectorForEdit()" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>
            </div>
          </div>
          
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-300">主题颜色</label>
            <input name="color" id="edit-color" placeholder="#3B82F6" class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
          </div>
          
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-300">排序权重</label>
            <input name="sort_order" id="edit-sort-order" type="number" min="0" class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
          </div>
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">分类简介</label>
          <textarea name="description" id="edit-description" rows="3" class="w-full px-3 py-2 rounded bg-gray-800/60 border border-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors resize-none"></textarea>
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-300">状态</label>
          <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="is_active" value="1" id="edit-active-true" class="w-4 h-4 text-primary-600 bg-gray-800 border-gray-700 focus:ring-primary-500" />
              <span class="text-sm text-gray-300">启用</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="is_active" value="0" id="edit-active-false" class="w-4 h-4 text-primary-600 bg-gray-800 border-gray-700 focus:ring-primary-500" />
              <span class="text-sm text-gray-300">禁用</span>
            </label>
          </div>
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

<!-- 图标选择器模态框 -->
<div id="icon-selector-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-gray-900 rounded-xl p-6 w-full max-w-4xl max-h-[80vh] overflow-hidden">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold">选择图标</h3>
        <button onclick="closeIconSelector()" class="text-gray-400 hover:text-white transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      
      <!-- 搜索框 -->
      <div class="mb-4">
        <input type="text" id="icon-search" placeholder="搜索图标..." class="w-full px-3 py-2 rounded bg-gray-800 border border-gray-700 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" />
      </div>
      
      <!-- 图标分类标签 -->
      <div class="mb-4 flex flex-wrap gap-2">
        <button onclick="filterIcons('all')" class="icon-category px-3 py-1 rounded bg-primary-500 text-white text-sm transition-colors">全部</button>
        <button onclick="filterIcons('common')" class="icon-category px-3 py-1 rounded bg-gray-700 text-gray-300 text-sm hover:bg-gray-600 transition-colors">常用</button>
        <button onclick="filterIcons('tech')" class="icon-category px-3 py-1 rounded bg-gray-700 text-gray-300 text-sm hover:bg-gray-600 transition-colors">科技</button>
        <button onclick="filterIcons('business')" class="icon-category px-3 py-1 rounded bg-gray-700 text-gray-300 text-sm hover:bg-gray-600 transition-colors">商务</button>
        <button onclick="filterIcons('social')" class="icon-category px-3 py-1 rounded bg-gray-700 text-gray-300 text-sm hover:bg-gray-600 transition-colors">社交</button>
        <button onclick="filterIcons('media')" class="icon-category px-3 py-1 rounded bg-gray-700 text-gray-300 text-sm hover:bg-gray-600 transition-colors">媒体</button>
        <button onclick="filterIcons('design')" class="icon-category px-3 py-1 rounded bg-gray-700 text-gray-300 text-sm hover:bg-gray-600 transition-colors">设计</button>
      </div>
      
      <!-- 图标网格 -->
      <div id="icon-grid" class="grid grid-cols-8 gap-3 overflow-y-auto max-h-96">
        <!-- 图标将通过JavaScript动态加载 -->
      </div>
    </div>
  </div>
</div>

@endsection 

@push('scripts')
<!-- 引入阿里图标库 -->
<link rel="stylesheet" href="//at.alicdn.com/t/c/font_4444444_abcdefg.css">
<script src="//at.alicdn.com/t/c/font_4444444_abcdefg.js"></script>

<script>
// 图标数据 - 使用真实的阿里图标库图标名称
const iconData = {
  common: [
    'icon-home', 'icon-user', 'icon-setting', 'icon-search', 'icon-heart', 'icon-star', 'icon-bookmark', 'icon-share',
    'icon-download', 'icon-upload', 'icon-edit', 'icon-delete', 'icon-add', 'icon-close', 'icon-menu', 'icon-arrow-right',
    'icon-folder', 'icon-file', 'icon-image', 'icon-video', 'icon-music', 'icon-document', 'icon-calendar', 'icon-clock'
  ],
  tech: [
    'icon-computer', 'icon-mobile', 'icon-tablet', 'icon-wifi', 'icon-bluetooth', 'icon-cloud', 'icon-database', 'icon-code',
    'icon-bug', 'icon-rocket', 'icon-chip', 'icon-circuit', 'icon-robot', 'icon-ai', 'icon-vr', 'icon-3d',
    'icon-server', 'icon-network', 'icon-security', 'icon-firewall', 'icon-api', 'icon-sdk', 'icon-framework', 'icon-library'
  ],
  business: [
    'icon-briefcase', 'icon-chart', 'icon-graph', 'icon-money', 'icon-credit-card', 'icon-bank', 'icon-invoice', 'icon-contract',
    'icon-meeting', 'icon-presentation', 'icon-target', 'icon-goal', 'icon-success', 'icon-growth', 'icon-profit', 'icon-investment',
    'icon-analysis', 'icon-report', 'icon-dashboard', 'icon-metrics', 'icon-kpi', 'icon-roi', 'icon-budget', 'icon-expense'
  ],
  social: [
    'icon-chat', 'icon-message', 'icon-phone', 'icon-email', 'icon-camera', 'icon-video', 'icon-music', 'icon-game',
    'icon-party', 'icon-gift', 'icon-cake', 'icon-balloon', 'icon-fireworks', 'icon-trophy', 'icon-medal', 'icon-flag',
    'icon-group', 'icon-team', 'icon-community', 'icon-friends', 'icon-family', 'icon-love', 'icon-smile', 'icon-laugh'
  ],
  media: [
    'icon-play', 'icon-pause', 'icon-stop', 'icon-forward', 'icon-backward', 'icon-volume', 'icon-mute', 'icon-fullscreen',
    'icon-subtitle', 'icon-quality', 'icon-hd', 'icon-4k', 'icon-live', 'icon-record', 'icon-broadcast', 'icon-stream',
    'icon-camera', 'icon-microphone', 'icon-headphones', 'icon-speaker', 'icon-tv', 'icon-radio', 'icon-podcast', 'icon-video-call'
  ],
  design: [
    'icon-palette', 'icon-brush', 'icon-pencil', 'icon-pen', 'icon-marker', 'icon-crayon', 'icon-paint', 'icon-canvas',
    'icon-layers', 'icon-shapes', 'icon-circle', 'icon-square', 'icon-triangle', 'icon-hexagon', 'icon-star', 'icon-heart',
    'icon-gradient', 'icon-pattern', 'icon-texture', 'icon-material', 'icon-3d-model', 'icon-vector', 'icon-raster', 'icon-icon'
  ]
};

let currentTargetInput = null;
let currentCategory = 'all';

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
function openEditModal(id, name, icon, color, sortOrder, description, isActive) {
  const modal = document.getElementById('edit-modal');
  const form = document.getElementById('edit-form');
  
  // 设置表单数据
  form.action = `/admin/categories/${id}`;
  document.getElementById('edit-name').value = name;
  document.getElementById('edit-icon').value = icon;
  document.getElementById('edit-color').value = color;
  document.getElementById('edit-sort-order').value = sortOrder;
  document.getElementById('edit-description').value = description;
  
  // 设置状态单选按钮
  if (isActive) {
    document.getElementById('edit-active-true').checked = true;
  } else {
    document.getElementById('edit-active-false').checked = true;
  }
  
  // 显示弹窗
  modal.classList.remove('hidden');
}

/**
 * 从按钮 data-* 读取数据并打开编辑弹窗
 */
function openEditModalFromButton(button) {
  const id = button.dataset.id;
  const name = button.dataset.name || '';
  const icon = button.dataset.icon || '';
  const color = button.dataset.color || '';
  const sortOrder = Number(button.dataset.sortOrder || 0);
  const description = button.dataset.description || '';
  const isActive = button.dataset.active === '1';
  openEditModal(id, name, icon, color, sortOrder, description, isActive);
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

// 图标选择器相关函数
function openIconSelector() {
  currentTargetInput = document.getElementById('icon-input');
  document.getElementById('icon-selector-modal').classList.remove('hidden');
  loadIcons();
}

function openIconSelectorForEdit() {
  currentTargetInput = document.getElementById('edit-icon');
  document.getElementById('icon-selector-modal').classList.remove('hidden');
  loadIcons();
}

function closeIconSelector() {
  document.getElementById('icon-selector-modal').classList.add('hidden');
  currentTargetInput = null;
}

function loadIcons() {
  const iconGrid = document.getElementById('icon-grid');
  iconGrid.innerHTML = '';
  
  let iconsToShow = [];
  if (currentCategory === 'all') {
    Object.values(iconData).forEach(category => {
      iconsToShow = iconsToShow.concat(category);
    });
  } else {
    iconsToShow = iconData[currentCategory] || [];
  }
  
  iconsToShow.forEach(iconClass => {
    const iconDiv = document.createElement('div');
    iconDiv.className = 'w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center cursor-pointer hover:bg-gray-700 transition-colors border border-gray-700 hover:border-gray-500';
    iconDiv.innerHTML = `<i class="${iconClass} text-xl text-gray-300"></i>`;
    iconDiv.title = iconClass;
    iconDiv.onclick = () => selectIcon(iconClass);
    iconGrid.appendChild(iconDiv);
  });
}

function selectIcon(iconClass) {
  if (currentTargetInput) {
    currentTargetInput.value = iconClass;
  }
  closeIconSelector();
}

function filterIcons(category) {
  currentCategory = category;
  
  // 更新按钮状态
  document.querySelectorAll('.icon-category').forEach(btn => {
    btn.className = 'icon-category px-3 py-1 rounded bg-gray-700 text-gray-300 text-sm hover:bg-gray-600 transition-colors';
  });
  event.target.className = 'icon-category px-3 py-1 rounded bg-primary-500 text-white text-sm transition-colors';
  
  loadIcons();
}

// 搜索图标
document.getElementById('icon-search').addEventListener('input', function(e) {
  const searchTerm = e.target.value.toLowerCase();
  const iconGrid = document.getElementById('icon-grid');
  iconGrid.innerHTML = '';
  
  let allIcons = [];
  Object.values(iconData).forEach(category => {
    allIcons = allIcons.concat(category);
  });
  
  const filteredIcons = allIcons.filter(icon => 
    icon.toLowerCase().includes(searchTerm)
  );
  
  filteredIcons.forEach(iconClass => {
    const iconDiv = document.createElement('div');
    iconDiv.className = 'w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center cursor-pointer hover:bg-gray-700 transition-colors border border-gray-700 hover:border-gray-500';
    iconDiv.innerHTML = `<i class="${iconClass} text-xl text-gray-300"></i>`;
    iconDiv.title = iconClass;
    iconDiv.onclick = () => selectIcon(iconClass);
    iconGrid.appendChild(iconDiv);
  });
});

// 点击模态框外部关闭
document.getElementById('icon-selector-modal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeIconSelector();
  }
});

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
  // 为现有的图标输入框添加点击事件
  const iconInputs = document.querySelectorAll('input[name="icon"]');
  iconInputs.forEach(input => {
    input.addEventListener('click', function() {
      if (this.id === 'icon-input') {
        openIconSelector();
      } else {
        openIconSelectorForEdit();
      }
    });
  });
});
</script>
@endpush 