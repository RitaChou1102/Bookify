<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model
{
    use HasFactory;

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'blacklist_id';

    /**
     * 指定資料表名稱
     */
    protected $table = 'blacklists';

    /**
     * 不使用 timestamps（因為我們手動定義了 created_at）
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'blocked_userid',
        'reason',
        'created_at',
        'banned_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * 取得被封鎖的使用者
     */
    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'blocked_userid', 'user_id');
    }

    /**
     * 取得執行封鎖的管理員
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'banned_by', 'admin_id');
    }
}

