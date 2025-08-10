@extends('admin.layout')

@section('title','导入书签')
@section('page-title','导入浏览器收藏夹')

@section('content')
<div class="space-y-6">
  <div class="glass-card rounded-xl p-6">
    <h3 class="text-xl font-bold mb-4">上传浏览器导出的书签文件（HTML）</h3>
    <form action="{{ route('admin.import.bookmarks.process') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-300 mb-1">书签文件（.html/.htm/.txt）</label>
          <input type="file" name="file" accept=".html,.htm,.txt" required class="w-full file:mr-3 file:px-3 file:py-2 file:rounded file:border-0 file:bg-primary-500 file:text-white file:hover:bg-primary-600 bg-gray-800/60 border border-gray-700 rounded px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-1">默认分类（当未勾选“根据文件夹创建分类”或未识别到分组时）</label>
          <select name="category_id" class="w-full bg-gray-800/60 border border-gray-700 rounded px-3 py-2">
            <option value="">自动选择（使用第一个分类）</option>
            @foreach($categories as $c)
              <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="map_folders" value="1" class="rounded bg-gray-800 border-gray-600"> 
          <span class="text-sm text-gray-300">根据文件夹创建/映射分类</span>
        </label>
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="smart_dedupe" value="1" checked class="rounded bg-gray-800 border-gray-600"> 
          <span class="text-sm text-gray-300">智能去重（忽略协议/WWW/末尾斜杠）</span>
        </label>
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="fetch_icon" value="1" checked class="rounded bg-gray-800 border-gray-600"> 
          <span class="text-sm text-gray-300">自动抓取站点图标</span>
        </label>
      </div>

      <!-- 自动获取信息说明 -->
      <div class="bg-blue-900/20 border border-blue-700/30 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <h4 class="text-blue-300 font-medium mb-1">✨ 智能信息获取</h4>
            <p class="text-sm text-blue-200/80">
              系统会在导入过程中自动获取每个网站的标题、描述和图标信息，让您的书签更加完整和美观。
              如果自动获取失败，系统会使用Google Favicon服务作为备选方案。
            </p>
          </div>
        </div>
      </div>

      <div>
        <button class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded text-white">开始导入</button>
      </div>
    </form>
    <p class="text-xs text-gray-400 mt-3">说明：请在浏览器书签管理中选择“导出书签”为HTML文件后上传。系统会解析其中的链接（A标签）并导入。</p>
  </div>
</div>
@endsection 