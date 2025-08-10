<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'visits',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'visits' => 'integer'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
} 