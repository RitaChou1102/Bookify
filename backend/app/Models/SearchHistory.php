<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    use HasFactory;

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'history_id';

    /**
     * 指定資料表名稱
     */
    protected $table = 'search_histories';

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
        'member_id',
        'keyword',
        'search_time',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'search_time' => 'datetime',
        ];
    }

    /**
     * 取得搜尋歷史的使用者（注意：根據 schema，member_id 外鍵指向 users 表）
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'member_id', 'user_id');
    }
}

