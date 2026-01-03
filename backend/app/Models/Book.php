<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $primaryKey = 'book_id';
    protected $table = 'books';

    // ✅ [修正1] 開啟時間戳記，這樣首頁才能用「最新上架」排序
    public $timestamps = true;

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
        'user_id', // ✅ [修正2] 加入 user_id，允許寫入賣家 ID
        'created_at', // 保險起見也可以加上去
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
            'price' => 'decimal:2',
            'listing' => 'boolean',
        ];
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
        // 這裡如果你確定資料表有 image_index 就保留，沒有的話建議拿掉 orderBy
        return $this->hasMany(Image::class, 'book_id', 'book_id');
    }

    /**
     * 取得書籍的封面圖片
     */
    public function coverImage()
    {
        // ✅ [修正3] 配合 Controller 的寫法，改成找 'is_cover' 為 true 的圖片
        // 原本的 image_index 可能不存在於資料庫，導致 500 錯誤
        return $this->hasOne(Image::class, 'book_id', 'book_id')->where('is_cover', true);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'book_id', 'book_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'book_id', 'book_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'book_id', 'book_id');
    }
}