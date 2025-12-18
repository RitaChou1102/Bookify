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
        // 注意：根據 schema，users 表沒有 timestamps
        $adminUser = DB::table('users')->insertGetId([
            'login_id' => 'admin001',
            'name' => '系統管理員',
            'email' => 'admin@bookify.test',
            'password' => Hash::make('password123'),
            'phone' => '0912345678',
            'address' => '台北市信義區市府路1號',
            'role' => 'member', // 雖然是管理員，但在 users 表中仍標記為 member
        ]);

        $memberUser1 = DB::table('users')->insertGetId([
            'login_id' => 'member001',
            'name' => '張三',
            'email' => 'member001@bookify.test',
            'password' => Hash::make('password123'),
            'phone' => '0911111111',
            'address' => '台北市中正區重慶南路1段',
            'role' => 'member',
        ]);

        $memberUser2 = DB::table('users')->insertGetId([
            'login_id' => 'member002',
            'name' => '李四',
            'email' => 'member002@bookify.test',
            'password' => Hash::make('password123'),
            'phone' => '0922222222',
            'address' => '新北市板橋區文化路1段',
            'role' => 'member',
        ]);

        $businessUser1 = DB::table('users')->insertGetId([
            'login_id' => 'business001',
            'name' => '科技書店', // 商店名稱（不是個人名稱）
            'email' => 'business001@bookify.test',
            'password' => Hash::make('password123'),
            'phone' => '0933333333',
            'address' => '台北市大安區敦化南路1段',
            'role' => 'business',
        ]);

        $businessUser2 = DB::table('users')->insertGetId([
            'login_id' => 'business002',
            'name' => '文學書坊', // 商店名稱
            'email' => 'business002@bookify.test',
            'password' => Hash::make('password123'),
            'phone' => '0944444444',
            'address' => '台北市信義區信義路5段',
            'role' => 'business',
        ]);

        // 2. 建立管理員（Admins）
        // 說明：管理員是獨立的系統，有自己的登入帳號
        // 注意：根據 schema，admins 表沒有 timestamps
        $admin1 = DB::table('admins')->insertGetId([
            'login_id' => 'admin',
            'password' => Hash::make('admin123'),
            'name' => '系統管理員',
        ]);

        // 3. 建立會員（Members）
        // 說明：會員是使用者的子類型，通過 user_id 關聯
        // 注意：根據 schema，members 表沒有 timestamps
        $member1 = DB::table('members')->insertGetId([
            'user_id' => $memberUser1,
        ]);

        $member2 = DB::table('members')->insertGetId([
            'user_id' => $memberUser2,
        ]);

        // 4. 建立廠商（Businesses）
        // 說明：廠商也是使用者的子類型，通過 user_id 關聯
        // 注意：根據 schema，businesses 表沒有 timestamps
        // 注意：store_name 現在與 users.name 保持一致（商店名稱）
        $business1 = DB::table('businesses')->insertGetId([
            'user_id' => $businessUser1,
            'store_name' => '科技書店', // 店名（與 users.name 保持一致）
            'bank_account' => '1234567890123456', // 銀行帳號
            'email' => 'business1@example.com',
            'phone' => '0912345678',
        ]);

        $business2 = DB::table('businesses')->insertGetId([
            'user_id' => $businessUser2,
            'store_name' => '文學書坊', // 店名（與 users.name 保持一致）
            'bank_account' => '9876543210987654', // 銀行帳號
            'email' => 'business2@example.com',
            'phone' => '0923456789',
        ]);

        // 5. 建立作者（Authors）
        // 說明：作者是獨立的資料，不依賴其他表
        // 注意：根據 schema，authors 表沒有 timestamps
        $author1 = DB::table('authors')->insertGetId([
            'name' => '金庸',
        ]);

        $author2 = DB::table('authors')->insertGetId([
            'name' => '村上春樹',
        ]);

        $author3 = DB::table('authors')->insertGetId([
            'name' => '東野圭吾',
        ]);

        // 6. 建立書籍類別（Book Categories）
        // 說明：書籍類別是獨立的資料
        // 注意：根據 schema，book_categories 表沒有 timestamps
        $category1 = DB::table('book_categories')->insertGetId([
            'name' => '武俠小說',
        ]);

        $category2 = DB::table('book_categories')->insertGetId([
            'name' => '文學小說',
        ]);

        $category3 = DB::table('book_categories')->insertGetId([
            'name' => '推理小說',
        ]);

        // ============================================
        // 第二部分：建立依賴基礎資料的資料
        // ============================================

        // 7. 建立書籍（Books）
        // 說明：書籍依賴 authors, book_categories, businesses
        // 注意：根據 schema，books 表沒有 timestamps，condition 是 ENUM('new','used')
        $book1 = DB::table('books')->insertGetId([
            'name' => '射鵰英雄傳',
            'author_id' => $author1,
            'isbn' => '9789573277151',
            'publish_date' => '2020-01-15',
            'edition' => 1,
            'publisher' => '遠流出版',
            'description' => '金庸經典武俠小說',
            'price' => 450.00,
            'condition' => 'new', // ENUM: 'new' 或 'used'
            'category_id' => $category1,
            'business_id' => $business1,
            'stock' => 50, // 庫存
            'listing' => 1, // 上架狀態
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
            'condition' => 'new',
            'category_id' => $category2,
            'business_id' => $business1,
            'stock' => 30,
            'listing' => 1,
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
            'condition' => 'used', // 二手書
            'category_id' => $category3,
            'business_id' => $business1,
            'stock' => 20,
            'listing' => 1,
        ]);

        // 廠商2的書籍（用於測試拆單功能）
        $book4 = DB::table('books')->insertGetId([
            'name' => '解憂雜貨店',
            'author_id' => $author3,
            'isbn' => '9789573277184',
            'publish_date' => '2020-05-15',
            'edition' => 1,
            'publisher' => '皇冠文化',
            'description' => '東野圭吾溫馨療癒作品',
            'price' => 350.00,
            'condition' => 'new',
            'category_id' => $category3,
            'business_id' => $business2,
            'stock' => 40,
            'listing' => 1, // 上架
        ]);

        $book5 = DB::table('books')->insertGetId([
            'name' => '1Q84',
            'author_id' => $author2,
            'isbn' => '9789573277195',
            'publish_date' => '2019-08-20',
            'edition' => 1,
            'publisher' => '時報出版',
            'description' => '村上春樹長篇小說',
            'price' => 550.00,
            'condition' => 'new',
            'category_id' => $category2,
            'business_id' => $business2,
            'stock' => 25,
            'listing' => 1, // 上架
        ]);

        $book6 = DB::table('books')->insertGetId([
            'name' => '天龍八部',
            'author_id' => $author1,
            'isbn' => '9789573277206',
            'publish_date' => '2021-01-10',
            'edition' => 1,
            'publisher' => '遠流出版',
            'description' => '金庸武俠經典之作',
            'price' => 480.00,
            'condition' => 'new',
            'category_id' => $category1,
            'business_id' => $business2,
            'stock' => 35,
            'listing' => 1, // 上架
        ]);

        // 8. 建立圖片（Images）
        // 說明：圖片關聯到書籍，一個書籍可以有多張圖片
        // 注意：根據 schema，images 表沒有 timestamps，欄位名稱是 image_index
        DB::table('images')->insert([
            [
                'book_id' => $book1,
                'image_index' => 0, // 封面圖片
                'image_url' => 'https://example.com/images/book1_cover.jpg',
            ],
            [
                'book_id' => $book2,
                'image_index' => 0,
                'image_url' => 'https://example.com/images/book2_cover.jpg',
            ],
            [
                'book_id' => $book3,
                'image_index' => 0,
                'image_url' => 'https://example.com/images/book3_cover.jpg',
            ],
            [
                'book_id' => $book4,
                'image_index' => 0,
                'image_url' => 'https://example.com/images/book4_cover.jpg',
            ],
            [
                'book_id' => $book5,
                'image_index' => 0,
                'image_url' => 'https://example.com/images/book5_cover.jpg',
            ],
            [
                'book_id' => $book6,
                'image_index' => 0,
                'image_url' => 'https://example.com/images/book6_cover.jpg',
            ],
        ]);

        // 9. 建立優惠券（Coupons）
        // 說明：優惠券由廠商發行
        // 注意：根據 schema，coupons 表沒有 timestamps
        // discount_type: 'percent_off' 或 'fixed'
        // coupon_type: 'shipping', 'seasonal', 'special_event'
        $coupon1 = DB::table('coupons')->insertGetId([
            'name' => '新用戶專屬優惠',
            'business_id' => $business1,
            'code' => 'WELCOME2024',
            'description' => '新用戶首次購買可享九折優惠',
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(30),
            'discount_type' => 'percent_off', // 百分比折扣
            'discount_value' => 10.00, // 打九折（減10%）
            'limit_price' => 500.00, // 滿500元可使用
            'usage_limit' => 100, // 可使用100次
            'used_count' => 5, // 已使用5次
            'coupon_type' => 'special_event', // 特殊活動
            'is_deleted' => 0, // 未刪除
        ]);

        // 10. 建立購物車（Carts）
        // 說明：每個會員有一個購物車
        // 注意：根據 schema，carts 表沒有 timestamps
        // 重要：Cart 的 member_id 外鍵指向 members.member_id
        $cart1 = DB::table('carts')->insertGetId([
            'member_id' => $member1, // 指向 members.member_id
        ]);

        // 11. 建立購物車項目（Cart Items）
        // 說明：購物車中的商品項目
        // 注意：根據 schema，cart_items 表沒有 timestamps
        $cartItem1 = DB::table('cart_items')->insertGetId([
            'cart_id' => $cart1,
            'book_id' => $book1,
            'quantity' => 2,
            'price' => 450.00,
            'subtotal' => 900.00, // 2 * 450
        ]);

        $cartItem2 = DB::table('cart_items')->insertGetId([
            'cart_id' => $cart1,
            'book_id' => $book2,
            'quantity' => 1,
            'price' => 380.00,
            'subtotal' => 380.00,
        ]);

        // ============================================
        // 第三部分：建立複雜的業務資料
        // ============================================

        // 12. 建立訂單（Orders）
        // 說明：訂單是購物車結帳後的產物
        // 注意：根據 schema，orders 表沒有 timestamps
        // payment_method: 'Cash', 'Credit_card', 'Bank_transfer'
        // order_status: 'Received', 'Processing', 'Shipped', 'Completed', 'Cancelled'
        $order1 = DB::table('orders')->insertGetId([
            'member_id' => $member1,
            'total_amount' => 1280.00, // 900 + 380
            'order_time' => now()->subDays(5),
            'business_id' => $business1,
            'shipping_fee' => 60.00,
            'payment_method' => 'Credit_card', // 信用卡
            'order_status' => 'Shipped', // 已出貨
            'coupon_id' => $coupon1, // 使用了優惠券
            'cart_id' => $cart1,
        ]);

        // 13. 建立訂單明細（Order Details）
        // 說明：訂單中的每本書的詳細資訊
        // 注意：根據 schema，order_details 表只有 created_at，沒有 updated_at
        // 注意：subtotal 是虛擬欄位（VIRTUAL），會自動計算，不需要手動插入
        DB::table('order_details')->insert([
            [
                'order_id' => $order1,
                'book_id' => $book1,
                'quantity' => 2,
                'piece_price' => 450.00,
                // subtotal 是虛擬欄位，會自動計算為 quantity * piece_price
                'created_at' => now()->subDays(5),
            ],
            [
                'order_id' => $order1,
                'book_id' => $book2,
                'quantity' => 1,
                'piece_price' => 380.00,
                // subtotal 是虛擬欄位，會自動計算為 quantity * piece_price
                'created_at' => now()->subDays(5),
            ],
        ]);

        // 14. 建立評價（Reviews）
        // 說明：評價關聯到書籍和訂單
        // 注意：根據 schema，reviews 表沒有 timestamps
        // rating 是 TINYINT，comment 是可選的
        DB::table('reviews')->insert([
            [
                'book_id' => $book1,
                'order_id' => $order1,
                'rating' => 5,
                'comment' => '非常好看，值得推薦！',
                'review_time' => now()->subDays(3),
                'reply' => '感謝您的支持！',
                'reply_time' => now()->subDays(2),
            ],
        ]);

        // 15. 建立搜尋歷史（Search Histories）
        // 說明：記錄會員的搜尋行為
        // 注意：根據 schema，search_histories 表沒有 timestamps
        // 重要：SearchHistory 的 member_id 外鍵指向 users.user_id，不是 members.member_id
        DB::table('search_histories')->insert([
            [
                'member_id' => $memberUser1, // 指向 users.user_id
                'keyword' => '武俠小說',
                'search_time' => now()->subDays(7),
            ],
            [
                'member_id' => $memberUser1, // 指向 users.user_id
                'keyword' => '金庸',
                'search_time' => now()->subDays(6),
            ],
        ]);

        // 16. 建立報表（Reports）
        // 說明：管理員生成的統計報表
        // 注意：根據 schema，reports 表沒有 timestamps
        // report_type: 'sales_summary', 'inventory_status', 'user_activity', 'complaint_analysis'
        // generation_date, time_period_start, time_period_end 都是 DATETIME
        DB::table('reports')->insert([
            [
                'admin_id' => $admin1,
                'generation_date' => now()->subDays(1),
                'report_type' => 'sales_summary', // 銷售摘要
                'time_period_start' => now()->subDays(30),
                'time_period_end' => now(),
                'stats_data' => json_encode([
                    'total_orders' => 150,
                    'total_revenue' => 125000,
                    'total_books_sold' => 320,
                ]),
            ],
        ]);

        $this->command->info('資料庫種子資料建立完成！');
        $this->command->info('管理員帳號：admin / admin123');
        $this->command->info('會員帳號：member001@bookify.test / password123');
        $this->command->info('廠商帳號：business001@bookify.test / password123');
        $this->command->info('廠商2帳號：business002@bookify.test / password123');
    }
}
