<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'description',
        'logo',
        'category_id',
        'sort_order',
        'visits',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'visits' => 'integer'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function getLogoAttribute($value)
    {
        // 如果已有 logo，但指向旧的 gstatic faviconV2 接口，统一回退为 google s2 接口
        if (!empty($value)) {
            $logoHost = parse_url($value, PHP_URL_HOST) ?: '';
            if (str_contains($logoHost, 'gstatic.com')) {
                $domain = parse_url($this->url, PHP_URL_HOST);
                return "https://www.google.com/s2/favicons?domain={$domain}&sz=64";
            }
            return $value;
        }
        
        // 自动获取网站 favicon（google s2 接口，更稳定，失败也会返回占位图标）
        $domain = parse_url($this->url, PHP_URL_HOST);
        return "https://www.google.com/s2/favicons?domain={$domain}&sz=64";
    }
} 