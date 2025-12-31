<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'detail_id';

    /**
     * 指定資料表名稱
     */
    protected $table = 'order_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'book_id',
        'quantity',
        'piece_price',
        // 注意：subtotal 是虛擬欄位，不需要在 fillable 中
    ];

    /**
     * 只使用 created_at，不使用 updated_at（根據 schema）
     */
    public $timestamps = true;

    /**
     * 指定時間戳記欄位
     */
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'piece_price' => 'decimal:2',
            'subtotal' => 'decimal:2', // 虛擬欄位也需要 cast
            'created_at' => 'datetime',
        ];
    }

    /**
     * 取得訂單明細的小計（如果虛擬欄位無法正常運作，使用此方法）
     * 
     * @return float
     */
    public function getSubtotalAttribute($value)
    {
        // 如果資料庫有計算好的 subtotal（虛擬欄位），直接返回
        if ($value !== null) {
            return (float) $value;
        }
        
        // 否則手動計算
        return (float) ($this->quantity * $this->piece_price);
    }

    /**
     * 取得訂單明細所屬的訂單
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    /**
     * 取得訂單明細的書籍
     */
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'book_id');
    }
}

