<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * 指定主鍵名稱（因為我們使用 user_id 而非 id）
     */
    protected $primaryKey = 'user_id';

    /**
     * 指定主鍵是否自動遞增
     */
    public $incrementing = true;

    /**
     * 主鍵類型
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'login_id',
        'name',
        'email',
        'password',
        'phone',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * 取得使用者的會員資料（如果有的話）
     */
    public function member()
    {
        return $this->hasOne(\App\Models\Member::class, 'user_id', 'user_id');
    }

    /**
     * 取得使用者的廠商資料（如果有的話）
     */
    public function business()
    {
        return $this->hasOne(\App\Models\Business::class, 'user_id', 'user_id');
    }
}
