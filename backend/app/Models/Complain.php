<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complain extends Model
{
    use HasFactory;

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'complaint_id';

    /**
     * 指定資料表名稱（根據 schema，表名是 complaints）
     */
    protected $table = 'complaints';

    /**
     * 不使用 timestamps（根據 schema）
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'content',
        'complaint_time',
        'complaint_status',
        'result',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'complaint_time' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 取得投訴的訂單
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * 投訴狀態常數
     */
    const STATUS_PENDING = 'pending';      // 待處理
    const STATUS_IN_PROGRESS = 'in_progress';  // 處理中
    const STATUS_RESOLVED = 'resolved';     // 已解決
}

