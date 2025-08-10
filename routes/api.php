<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| 这里定义了应用程序的API路由
| 这些路由通常是无状态的，用于API接口
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 网站信息获取API
Route::post('/website-info', function (Request $request) {
    $request->validate(['url' => 'required|url']);
    
    $websiteInfo = app(\App\Services\WebsiteInfoService::class)->getWebsiteInfo($request->url);
    
    return response()->json($websiteInfo);
}); 