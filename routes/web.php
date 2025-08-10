<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

// 前台路由
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/visit/{site}', [HomeController::class, 'visit'])->name('visit');
Route::get('/copy-info/{site}', [HomeController::class, 'copyInfo'])->name('copy-info');

// 认证路由（不需要中间件保护）
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// 后台路由（需要认证中间件保护）
Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/sites', [AdminController::class, 'sites'])->name('sites');
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::get('/activity', [AdminController::class, 'activity'])->name('activity');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

    // 导入浏览器收藏夹
    Route::get('/import-bookmarks', [AdminController::class, 'importBookmarksForm'])->name('import.bookmarks');
    Route::post('/import-bookmarks', [AdminController::class, 'importBookmarksProcess'])->name('import.bookmarks.process');
    
    // 网站管理
    Route::post('/sites', [AdminController::class, 'storeSite'])->name('sites.store');
    Route::put('/sites/{site}', [AdminController::class, 'updateSite'])->name('sites.update');
    Route::delete('/sites/{site}', [AdminController::class, 'deleteSite'])->name('sites.delete');
    // 批量自动获取网站信息
    Route::post('/sites/bulk-fetch', [AdminController::class, 'bulkFetchSiteInfo'])->name('sites.bulk-fetch');

    // 分类管理（保留原简单POST保存/删除示例，新增 is_active 支持）
    Route::post('/categories', function(\Illuminate\Http\Request $request) {
        if ($id = $request->input('delete_id')) { 
            \App\Models\Category::find($id)?->delete(); 
            return back()->with('success','删除成功'); 
        }
        if ($id = $request->input('id')) { 
            \App\Models\Category::find($id)?->update($request->only(['name','icon','color','sort_order','description','is_active'])); 
            return back()->with('success','更新成功'); 
        }
        \App\Models\Category::create($request->only(['name','icon','color','sort_order','description','is_active']));
        return back()->with('success','保存成功');
    })->name('categories.save');

    // 分类管理：RESTful 更新与删除（供弹窗编辑/删除使用）
    Route::put('/categories/{category}', function(\Illuminate\Http\Request $request, \App\Models\Category $category) {
        $category->update($request->only(['name','icon','color','sort_order','description','is_active']));
        return back()->with('success','更新成功');
    })->name('categories.update');

    Route::delete('/categories/{category}', function(\App\Models\Category $category) {
        $category->delete();
        return back()->with('success','删除成功');
    })->name('categories.delete');

    // 性能监控
    Route::get('/performance', [AdminController::class, 'performance'])->name('performance');
    Route::post('/performance/clear-cache', [AdminController::class, 'clearCache'])->name('performance.clear-cache');
    Route::post('/performance/warmup-cache', [AdminController::class, 'warmupCache'])->name('performance.warmup-cache');
    Route::post('/performance/optimize', [AdminController::class, 'optimizePerformance'])->name('performance.optimize');
    // 实时系统状态（JSON）
    Route::get('/performance/system-stats', [AdminController::class, 'systemStats'])->name('performance.system-stats');
    // 性能历史数据（JSON）
    Route::get('/performance/history', [AdminController::class, 'performanceHistory'])->name('performance.history');
    // 动态刷新数据（JSON）
    Route::get('/performance/cache-status', [AdminController::class, 'cacheStatus'])->name('performance.cache-status');
    Route::get('/performance/metrics', [AdminController::class, 'performanceMetrics'])->name('performance.metrics');
    Route::get('/performance/optimization-tips', [AdminController::class, 'optimizationTips'])->name('performance.optimization-tips');
    // 慢查询分析（JSON）
    Route::get('/performance/slow-queries', [AdminController::class, 'slowQueries'])->name('performance.slow-queries');
    Route::get('/performance/database-stats', [AdminController::class, 'databaseStats'])->name('performance.database-stats');
    // 数据库优化操作
    Route::post('/performance/optimize-indexes', [AdminController::class, 'optimizeIndexes'])->name('performance.optimize-indexes');
}); 