<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * 說明：這個 Seeder 會按照正確的順序建立測試資料
     * 
     * 設計原理：
     * 1. 必須按照資料表之間的依賴關係來建立資料
     * 2. 先建立沒有外鍵依賴的基礎資料（users, authors, book_categories）
     * 3. 再建立有外鍵依賴的資料（members, businesses, books 等）
     * 4. 最後建立複雜的關聯資料（orders, reviews 等）
     */
    public function run(): void
    {
        // dd('測試中文');
        DB::statement("SET NAMES 'utf8mb4'");
        // ============================================
        // 第一部分：建立基礎資料（沒有外鍵依賴）
        // ============================================

        // 1. 建立使用者（Users）
        // 說明：Users 表是所有角色的基礎，會員、廠商都從這裡開始
        $adminUser = DB::table('users')->insertGetId([
            'login_id' => 'admin001',
            'name' => '系統管理員',
            'email' => 'admin@bookify.test',
            'password' => Hash::make('password123'),
            'phone' => '0912345678',
            'address' => '台北市信義區市府路1號',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $memberUser1 = DB::table('users')->insertGetId([
            'login_id' => 'member001',
            'name' => '張三',
            'email' => 'member001@bookify.test',
            'password' => Hash::make('password123'),
            'phone' => '0911111111',
            'address' => '台北市中正區重慶南路1段',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $memberUser2 = DB::table('users')->insertGetId([
            'login_id' => 'member002',
            'name' => '李四',
            'email' => 'member002@bookify.test',
            'password' => Hash::make('password123'),
            'phone' => '0922222222',
            'address' => '新北市板橋區文化路1段',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $businessUser1 = DB::table('users')->insertGetId([
            'login_id' => 'business001',
            'name' => '王老闆',
            'email' => 'business001@bookify.test',
            'password' => Hash::make('password123'),
            'phone' => '0933333333',
            'address' => '台北市大安區敦化南路1段',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. 建立管理員（Admins）
        // 說明：管理員是獨立的系統，有自己的登入帳號
        $admin1 = DB::table('admins')->insertGetId([
            'login_id' => 'admin',
            'password' => Hash::make('admin123'),
            'name' => '系統管理員',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. 建立會員（Members）
        // 說明：會員是使用者的子類型，通過 user_id 關聯
        $member1 = DB::table('members')->insertGetId([
            'user_id' => $memberUser1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $member2 = DB::table('members')->insertGetId([
            'user_id' => $memberUser2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. 建立廠商（Businesses）
        // 說明：廠商也是使用者的子類型，通過 user_id 關聯
        $business1 = DB::table('businesses')->insertGetId([
            'user_id' => $businessUser1,
            'bank_account' => '1234567890123456', // 銀行帳號
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. 建立作者（Authors）
        // 說明：作者是獨立的資料，不依賴其他表
        $author1 = DB::table('authors')->insertGetId([
            'name' => '金庸',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $author2 = DB::table('authors')->insertGetId([
            'name' => '村上春樹',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $author3 = DB::table('authors')->insertGetId([
            'name' => '東野圭吾',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. 建立書籍類別（Book Categories）
        // 說明：書籍類別是獨立的資料
        $category1 = DB::table('book_categories')->insertGetId([
            'name' => '武俠小說',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $category2 = DB::table('book_categories')->insertGetId([
            'name' => '文學小說',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $category3 = DB::table('book_categories')->insertGetId([
            'name' => '推理小說',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ============================================
        // 第二部分：建立依賴基礎資料的資料
        // ============================================

        // 7. 建立書籍（Books）
        // 說明：書籍依賴 authors, book_categories, businesses
        $book1 = DB::table('books')->insertGetId([
            'name' => '射鵰英雄傳',
            'author_id' => $author1,
            'isbn' => '9789573277151',
            'publish_date' => '2020-01-15',
            'edition' => 1,
            'publisher' => '遠流出版',
            'description' => '金庸經典武俠小說',
            'price' => 450.00,
            'condition' => '全新',
            'category_id' => $category1,
            'business_id' => $business1,
            'listing' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $book2 = DB::table('books')->insertGetId([
            'name' => '挪威的森林',
            'author_id' => $author2,
            'isbn' => '9789573277162',
            'publish_date' => '2019-06-20',
            'edition' => 2,
            'publisher' => '時報出版',
            'description' => '村上春樹經典作品',
            'price' => 380.00,
            'condition' => '全新',
            'category_id' => $category2,
            'business_id' => $business1,
            'listing' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $book3 = DB::table('books')->insertGetId([
            'name' => '白夜行',
            'author_id' => $author3,
            'isbn' => '9789573277173',
            'publish_date' => '2021-03-10',
            'edition' => 1,
            'publisher' => '獨步文化',
            'description' => '東野圭吾推理小說代表作',
            'price' => 420.00,
            'condition' => '九成新',
            'category_id' => $category3,
            'business_id' => $business1,
            'listing' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 8. 建立圖片（Images）
        // 說明：圖片關聯到書籍，一個書籍可以有多張圖片
        DB::table('images')->insert([
            [
                'book_id' => $book1,
                'index' => 0, // 封面圖片
                'image_url' => 'https://example.com/images/book1_cover.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'book_id' => $book2,
                'index' => 0,
                'image_url' => 'https://example.com/images/book2_cover.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'book_id' => $book3,
                'index' => 0,
                'image_url' => 'https://example.com/images/book3_cover.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 9. 建立優惠券（Coupons）
        // 說明：優惠券由廠商發行
        $coupon1 = DB::table('coupons')->insertGetId([
            'name' => '新用戶專屬優惠',
            'business_id' => $business1,
            'code' => 'WELCOME2024',
            'description' => '新用戶首次購買可享九折優惠',
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(30),
            'discount_type' => 0, // 0=百分比折扣
            'discount_value' => 10.00, // 打九折（減10%）
            'limit_price' => 500.00, // 滿500元可使用
            'usage_limit' => 100, // 可使用100次
            'used_count' => 5, // 已使用5次
            'coupon_type' => 0, // 0=運費優惠
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 10. 建立購物車（Carts）
        // 說明：每個會員有一個購物車
        $cart1 = DB::table('carts')->insertGetId([
            'member_id' => $member1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 11. 建立購物車項目（Cart Items）
        // 說明：購物車中的商品項目
        $cartItem1 = DB::table('cart_items')->insertGetId([
            'cart_id' => $cart1,
            'book_id' => $book1,
            'quantity' => 2,
            'price' => 450.00,
            'subtotal' => 900.00, // 2 * 450
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cartItem2 = DB::table('cart_items')->insertGetId([
            'cart_id' => $cart1,
            'book_id' => $book2,
            'quantity' => 1,
            'price' => 380.00,
            'subtotal' => 380.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ============================================
        // 第三部分：建立複雜的業務資料
        // ============================================

        // 12. 建立訂單（Orders）
        // 說明：訂單是購物車結帳後的產物
        $order1 = DB::table('orders')->insertGetId([
            'member_id' => $member1,
            'total_amount' => 1280.00, // 900 + 380
            'order_time' => now()->subDays(5),
            'business_id' => $business1,
            'shipping_fee' => 60.00,
            'payment_method' => 1, // 1=信用卡
            'order_status' => 2, // 2=已出貨
            'coupon_id' => $coupon1, // 使用了優惠券
            'cart_id' => $cart1,
            'created_at' => now()->subDays(5),
            'updated_at' => now(),
        ]);

        // 13. 建立訂單明細（Order Details）
        // 說明：訂單中的每本書的詳細資訊
        DB::table('order_details')->insert([
            [
                'order_id' => $order1,
                'book_id' => $book1,
                'quantity' => 2,
                'piece_price' => 450.00,
                'subtotal' => 900.00,
                'created_at' => now()->subDays(5),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order1,
                'book_id' => $book2,
                'quantity' => 1,
                'piece_price' => 380.00,
                'subtotal' => 380.00,
                'created_at' => now()->subDays(5),
                'updated_at' => now(),
            ],
        ]);

        // 14. 建立評價（Reviews）
        // 說明：評價關聯到書籍和訂單
        DB::table('reviews')->insert([
            [
                'book_id' => $book1,
                'order_id' => $order1,
                'rating' => 5,
                'comment' => '非常好看，值得推薦！',
                'review_time' => now()->subDays(3),
                'reply' => '感謝您的支持！',
                'reply_time' => now()->subDays(2),
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(2),
            ],
        ]);

        // 15. 建立搜尋歷史（Search Histories）
        // 說明：記錄會員的搜尋行為
        DB::table('search_histories')->insert([
            [
                'member_id' => $member1,
                'keyword' => '武俠小說',
                'search_time' => now()->subDays(7),
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],
            [
                'member_id' => $member1,
                'keyword' => '金庸',
                'search_time' => now()->subDays(6),
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(6),
            ],
        ]);

        // 16. 建立報表（Reports）
        // 說明：管理員生成的統計報表
        DB::table('reports')->insert([
            [
                'admin_id' => $admin1,
                'generation_date' => now()->subDays(1),
                'report_type' => '銷售報表',
                'time_period_start' => now()->subDays(30)->format('Y-m-d'),
                'time_period_end' => now()->format('Y-m-d'),
                'stats_data' => json_encode([
                    'total_orders' => 150,
                    'total_revenue' => 125000,
                    'total_books_sold' => 320,
                ]),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ]);

        $this->command->info('資料庫種子資料建立完成！');
        $this->command->info('管理員帳號：admin / admin123');
        $this->command->info('會員帳號：member001@bookify.test / password123');
        $this->command->info('廠商帳號：business001@bookify.test / password123');
    }
}
