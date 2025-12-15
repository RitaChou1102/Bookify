<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'member_id';

    /**
     * 指定資料表名稱
     */
    protected $table = 'members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * 取得會員的使用者資料
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * 取得會員的購物車
     */
    public function getCartAttribute()
    {
        return $this->user ? $this->user->cart : null;
    }

    /**
     * 取得會員的訂單
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'member_id', 'member_id');
    }

    /**
     * 取得會員的搜尋歷史
     */
    public function getSearchHistoriesAttribute()
    {
        return $this->user ? $this->user->searchHistories : collect();
    }
}

