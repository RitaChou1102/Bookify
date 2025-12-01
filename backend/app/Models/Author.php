<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'author_id';

    /**
     * 指定資料表名稱
     */
    protected $table = 'authors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * 取得作者的所有書籍
     */
    public function books()
    {
        return $this->hasMany(Book::class, 'author_id', 'author_id');
    }
}
