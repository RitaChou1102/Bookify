<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'book_id';

    /**
     * 指定資料表名稱
     */
    protected $table = 'books';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',           // 書名
        'author_id',      // 作者 ID
        'isbn',           // ISBN 碼
        'publish_date',   // 出版日期
        'edition',        // 版本
        'publisher',      // 出版商
        'description',    // 描述
        'price',          // 價格
        'condition',      // 書況 (new/used)
        'category_id',    // 分類 ID
        'business_id',    // 商家 ID
        'stock',          // 庫存
        'listing',        // 是否上架
    ];

    /**
     * 不使用 timestamps（根據 schema）
     */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
            'price' => 'decimal:2',
            'listing' => 'boolean',
        ];
    }

    /**
     * 取得書籍的作者
     */
    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id', 'author_id');
    }

    /**
     * 取得書籍的類別
     */
    public function category()
    {
        return $this->belongsTo(BookCategory::class, 'category_id', 'category_id');
    }

    /**
     * 取得書籍的廠商
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    /**
     * 取得書籍的所有圖片
     */
    public function images()
    {
        return $this->hasMany(Image::class, 'book_id', 'book_id')->orderBy('image_index');
    }

    /**
     * 取得書籍的封面圖片（image_index=0 的圖片）
     */
    public function coverImage()
    {
        return $this->hasOne(Image::class, 'book_id', 'book_id')->where('image_index', 0);
    }

    /**
     * 取得書籍的所有評價
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'book_id', 'book_id');
    }

    /**
     * 取得書籍的訂單明細
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'book_id', 'book_id');
    }

    /**
     * 取得書籍的購物車項目
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'book_id', 'book_id');
    }
}
