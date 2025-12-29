<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

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
        // 若要保留你剛剛註冊的帳號，請註解掉下面這行
        DB::table('users')->truncate(); 
        try { DB::table('images')->truncate(); } catch (\Exception $e) {}

        // 2. 建立基礎資料 (作者)
        $authorId1 = DB::table('authors')->insertGetId(['name' => '岸見一郎']);
        $authorId2 = DB::table('authors')->insertGetId(['name' => 'James Clear']);
        $authorId3 = DB::table('authors')->insertGetId(['name' => 'J.K. Rowling']);
        $authorId4 = DB::table('authors')->insertGetId(['name' => 'Robert Kiyosaki']); // 富爸爸作者
        $authorId5 = DB::table('authors')->insertGetId(['name' => 'Antoine de Saint-Exupéry']); // 小王子作者
        $authorId6 = DB::table('authors')->insertGetId(['name' => 'Daniel Kahneman']); // 快思慢想作者

        // 建立類別
        $catPsychId = DB::table('book_categories')->insertGetId(['name' => 'Psychology']);
        $catBizId = DB::table('book_categories')->insertGetId(['name' => 'Business']);
        $catFicId = DB::table('book_categories')->insertGetId(['name' => 'Fiction']);

        // 3. 建立廠商
        $userId = DB::table('users')->insertGetId([
            'login_id' => 'vendor_01',
            'name' => 'Bookify 官方直營',
            'email' => 'vendor@bookify.com',
            'password' => Hash::make('123456'),
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

        // 4. 建立書籍 (共 6 本)
        $books = [
            [
                'name' => '被討厭的勇氣',
                'isbn' => '9789861371955',
                'description' => '所謂的自由，就是被別人討厭。這是一本關於阿德勒心理學的書。',
                'price' => 300,
                'author_id' => $authorId1,
                'category_id' => $catPsychId,
                'image' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&q=80&w=600'
            ],
            [
                'name' => '原子習慣',
                'isbn' => '9789861755267',
                'description' => '細微改變帶來巨大成就的實證法則。每天進步 1%，一年後你會強大 37 倍。',
                'price' => 330,
                'author_id' => $authorId2,
                'category_id' => $catBizId,
                'image' => 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?auto=format&fit=crop&q=80&w=600'
            ],
            [
                'name' => '哈利波特：神秘的魔法石',
                'isbn' => '9789573317241',
                'description' => 'J.K.羅琳的經典奇幻小說，帶你進入霍格華茲的魔法世界。',
                'price' => 450,
                'author_id' => $authorId3,
                'category_id' => $catFicId,
                'image' => 'https://images.unsplash.com/photo-1618666012174-83b441c0bc76?auto=format&fit=crop&q=80&w=600'
            ],
            [
                'name' => '富爸爸，窮爸爸',
                'isbn' => '9789861755268',
                'description' => '二十週年紀念版，教你如何建立正確的金錢觀念，實現財富自由。',
                'price' => 380,
                'author_id' => $authorId4,
                'category_id' => $catBizId,
                'image' => 'https://images.unsplash.com/photo-1554415707-6e8cfc93fe23?auto=format&fit=crop&q=80&w=600'
            ],
            [
                'name' => '小王子',
                'isbn' => '9789573317249',
                'description' => '最值得珍藏的經典譯本。真正重要的東西，用眼睛是看不見的。',
                'price' => 250,
                'author_id' => $authorId5,
                'category_id' => $catFicId,
                'image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&q=80&w=600'
            ],
            [
                'name' => '快思慢想',
                'isbn' => '9789863200543',
                'description' => '諾貝爾經濟學獎得主康納曼的傳世鉅作，探索大腦思考的運作機制。',
                'price' => 500,
                'author_id' => $authorId6,
                'category_id' => $catPsychId,
                'image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&q=80&w=600'
            ]
        ];

        foreach ($books as $book) {
            $bookId = DB::table('books')->insertGetId([
                'name' => $book['name'],
                'isbn' => $book['isbn'],
                'publish_date' => '2020-01-01', // 統一日假
                'edition' => 1,
                'publisher' => 'Bookify Press',
                'description' => $book['description'],
                'price' => $book['price'],
                'condition' => 'new',
                'stock' => 50,
                'listing' => 1,
                'author_id' => $book['author_id'],
                'category_id' => $book['category_id'],
                'business_id' => $bizId,
            ]);

            try {
                DB::table('images')->insert([
                    'book_id' => $bookId, 
                    'image_index' => 0, 
                    'image_url' => $book['image']
                ]);
            } catch (\Exception $e) {}
        }

        Schema::enableForeignKeyConstraints();
    }
}