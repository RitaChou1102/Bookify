<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // 繼承這個類別
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 如果後台也要用 Token 驗證 API

class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // 指定主鍵名稱
    protected $primaryKey = 'admin_id';

    // 不使用 timestamps（根據 schema）
    public $timestamps = false;

    // 允許批量賦值的欄位
    protected $fillable = [
        'login_id',
        'name',
        'password',
    ];

    // 隱藏敏感資訊
    protected $hidden = [
        'password',
        'remember_token',
    ];
}