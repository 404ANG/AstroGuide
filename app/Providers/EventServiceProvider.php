<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

/**
 * 事件服务提供者
 * 用于注册应用程序的事件和监听器
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * 应用程序的事件监听器映射
     * 用于将事件与监听器关联
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * 注册任何事件
     */
    public function boot(): void
    {
        // 在这里注册事件监听器
    }

    /**
     * 确定事件和监听器是否应该自动发现
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
} 