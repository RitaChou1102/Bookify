<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCategory extends Model
{
    use HasFactory;

    /**
     * 指定資料表名稱
     */
    protected $table = 'book_categories';

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'category_id';

    /**
     * 關閉自動維護時間戳記
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * 取得該類別的所有書籍
     */
    public function books()
    {
        // Book Model 有對應 category_id
        return $this->hasMany(Book::class, 'category_id', 'category_id');
    }
}