<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * 认证服务提供者
 * 用于配置应用程序的认证和授权策略
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * 应用程序的策略映射
     * 用于将模型与策略类关联
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 在这里定义模型与策略的映射关系
        // 例如: 'App\Models\User' => 'App\Policies\UserPolicy',
    ];

    /**
     * 注册任何认证/授权服务
     */
    public function boot(): void
    {
        // 注册策略
        $this->registerPolicies();
    }
} 