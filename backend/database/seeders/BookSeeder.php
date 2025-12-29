<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookSeeder extends Seeder
{
    public function run()
    {
        // 暫時關閉外鍵檢查
        Schema::disableForeignKeyConstraints();

        // 1. 清空舊資料
        DB::table('books')->truncate();
        DB::table('authors')->truncate();
        DB::table('book_categories')->truncate();
        DB::table('businesses')->truncate();
        DB::table('users')->truncate(); // 記得也要清空使用者，不然 login_id 重複會報錯
        try { DB::table('images')->truncate(); } catch (\Exception $e) {}

        // 2. 建立基礎資料
        $authorId1 = DB::table('authors')->insertGetId(['name' => '岸見一郎']);
        $authorId2 = DB::table('authors')->insertGetId(['name' => 'James Clear']);

        $catPsychId = DB::table('book_categories')->insertGetId(['name' => 'Psychology']);
        $catBizId = DB::table('book_categories')->insertGetId(['name' => 'Business']);

        // 3. 建立廠商 (需先建 User，再建 Business)
        $userId = DB::table('users')->insertGetId([
            'login_id' => 'vendor_01',
            'name' => 'Bookify 官方直營',
            'email' => 'vendor@bookify.com',
            'password' => '$2y$12$K.Jw/..', // 隨便塞個 hash 或是用 bcrypt('123456')
            'role' => 'business',
            'phone' => '0912345678',
            'address' => '台北市信義區'
        ]);

        $bizId = DB::table('businesses')->insertGetId([
            'user_id' => $userId,
            'store_name' => 'Bookify 官方直營',
            'bank_account' => '822-000012345678',
            'email' => 'service@bookify.com',
            'phone' => '02-87654321'
        ]);

        // 4. 建立書籍
        $bookId1 = DB::table('books')->insertGetId([
            'name' => '被討厭的勇氣',
            'isbn' => '9789861371955',
            'publish_date' => '2014-11-01',
            'edition' => 1,
            'publisher' => '究竟出版社',
            'description' => '所謂的自由，就是被別人討厭。',
            'price' => 300,
            'condition' => 'new',
            'stock' => 20,
            'listing' => 1,
            'author_id' => $authorId1,
            'category_id' => $catPsychId,
            'business_id' => $bizId,
        ]);

        $bookId2 = DB::table('books')->insertGetId([
            'name' => '原子習慣',
            'isbn' => '9789861755267',
            'publish_date' => '2019-06-01',
            'edition' => 1,
            'publisher' => '方智出版社',
            'description' => '細微改變帶來巨大成就的實證法則。',
            'price' => 330,
            'condition' => 'new',
            'stock' => 15,
            'listing' => 1,
            'author_id' => $authorId2,
            'category_id' => $catBizId,
            'business_id' => $bizId,
        ]);

        // 5. 建立圖片
        try {
            DB::table('images')->insert([
                ['book_id' => $bookId1, 'url' => 'https://im1.book.com.tw/image/getImage?i=https://www.books.com.tw/img/001/065/31/0010653153.jpg&v=544f07e5&w=348&h=348'],
                ['book_id' => $bookId2, 'url' => 'https://im1.book.com.tw/image/getImage?i=https://www.books.com.tw/img/001/082/25/0010822522.jpg&v=5ce27b0c&w=348&h=348']
            ]);
        } catch (\Exception $e) {}

        Schema::enableForeignKeyConstraints();
    }
}