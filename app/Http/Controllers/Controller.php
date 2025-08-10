<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * 基础控制器类
 * 所有控制器都应该继承这个类
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
} 