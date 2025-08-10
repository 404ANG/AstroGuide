<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 系统设置模型
 * 用于存储网站配置信息，如网站名称、图标、页脚内容等
 */
class SystemSetting extends Model
{
    protected $fillable = [
        'key',           // 设置键名
        'value',         // 设置值
        'type',          // 值类型：string, image, json
        'description'    // 设置描述
    ];

    /**
     * 获取设置值
     * @param string $key 设置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * 设置值
     * @param string $key 设置键名
     * @param mixed $value 设置值
     * @param string $type 值类型
     * @param string $description 描述
     * @return bool
     */
    public static function setValue($key, $value, $type = 'string', $description = '')
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description
            ]
        );
    }

    /**
     * 获取所有设置
     * @return array
     */
    public static function getAllSettings()
    {
        return static::pluck('value', 'key')->toArray();
    }
} 