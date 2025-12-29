<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    // 1. 指定主鍵名稱 
    protected $primaryKey = 'user_id';

    // 2. 指定主鍵是否自動遞增
    public $incrementing = true;

    // 3. 主鍵類型
    protected $keyType = 'int';

    protected $fillable = [
        'login_id',
        'name',
        'email',
        'password', 
        'role',         
        'phone',
        'address',
    ];


    protected $hidden = [
        'password', 
        'remember_token',
    ];

    /**
     * 不使用 timestamps（根據 schema）
     */
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime', 
            'password' => 'hashed',      // Laravel 會自動幫這個欄位進行 Hash 加密
        ];
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    // 取得使用者的會員資料（如果有的話）
    public function member()
    {
        return $this->hasOne(\App\Models\Member::class, 'user_id', 'user_id');
    }

    // 取得使用者的廠商資料（如果有的話）
    public function business()
    {
        return $this->hasOne(\App\Models\Business::class, 'user_id', 'user_id');
    }

    // 取得使用者的購物車（注意：根據 schema，Cart 的 member_id 外鍵指向 users 表）
    public function cart()
    {
        return $this->hasOne(\App\Models\Cart::class, 'member_id', 'user_id');
    }

    // 取得使用者的搜尋歷史（注意：根據 schema，SearchHistory 的 member_id 外鍵指向 users 表）
    public function searchHistories()
    {
        return $this->hasMany(\App\Models\SearchHistory::class, 'member_id', 'user_id');
    }

    // 取得該使用者的黑名單紀錄
    public function blacklist()
    {
        // 一個使用者在 blacklist 表中應該只有一筆有效的封鎖紀錄
        return $this->hasOne(Blacklist::class, 'blocked_userid', 'user_id');
    }

    // 快速判斷使用者是否被封鎖
    public function isBanned(): bool
    {
        return $this->blacklist()->exists();
    }
}