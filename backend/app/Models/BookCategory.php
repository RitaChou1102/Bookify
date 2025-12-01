<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCategory extends Model
{
    use HasFactory;

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'category_id';

    /**
     * 指定資料表名稱
     */
    protected $table = 'book_categories';

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
        return $this->hasMany(Book::class, 'category_id', 'category_id');
    }
}
