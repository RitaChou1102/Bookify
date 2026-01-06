<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $primaryKey = 'book_id';
    protected $table = 'books';

    // ✅ 關閉時間戳記（books 表沒有 created_at/updated_at）
    public $timestamps = false;

    protected $fillable = [
        'name',
        'author_id',
        'isbn',
        'publish_date',
        'edition',
        'publisher',
        'description',
        'price',
        'condition',
        'category_id',
        'business_id',
        'stock',
        'listing',
        'user_id',
        // 'image_url', // 注意：如果你的 books 表沒有這個欄位，這裡不要加，圖片是存在 images 表
    ];

    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
            'price' => 'decimal:2',
            'listing' => 'boolean',
        ];
    }

    // 關聯：賣家 (User) - 建議補上這個，方便以後查詢 "這本書是誰賣的"
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id', 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(BookCategory::class, 'category_id', 'category_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'book_id', 'book_id');
    }

    /**
     * 取得書籍的封面圖片 (修復首頁 500 錯誤的關鍵)
     */
    public function coverImage()
    {
        return $this->hasOne(Image::class, 'book_id', 'book_id')
                    // 直接取第一張圖片作為封面
                    ->orderBy('image_index'); 
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'book_id', 'book_id');
    }

    // ✅ 訂單明細
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'book_id', 'book_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'book_id', 'book_id');
    }
}