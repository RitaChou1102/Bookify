<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ExtendedSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::beginTransaction();
        try {
            // Create extra categories
            $catExtras = DB::table('book_categories')->where('name', 'Extras')->value('category_id');
            if (!$catExtras) {
                $catExtras = DB::table('book_categories')->insertGetId(['name' => 'Extras']);
            }

            // Create extra authors
            $authorA = DB::table('authors')->where('name', 'Extra Author A')->value('author_id');
            if (!$authorA) {
                $authorA = DB::table('authors')->insertGetId(['name' => 'Extra Author A']);
            }
            $authorB = DB::table('authors')->where('name', 'Extra Author B')->value('author_id');
            if (!$authorB) {
                $authorB = DB::table('authors')->insertGetId(['name' => 'Extra Author B']);
            }

            // Create two business users and businesses
            $userBiz1 = DB::table('users')->where('login_id', 'seed_business_01')->value('user_id');
            if (!$userBiz1) {
                $userBiz1 = DB::table('users')->insertGetId([
                    'login_id' => 'seed_business_01',
                    'name' => 'Seed 商家 1',
                    'email' => 'seedbiz1@bookify.test',
                    'password' => Hash::make('password'),
                    'phone' => '0955000001',
                    'address' => '台北市 Seed 路 1 號',
                    'role' => 'business',
                ]);
            }

            $biz1 = DB::table('businesses')->insertGetId([
                'user_id' => $userBiz1,
                'store_name' => 'Seed 商店 1',
                'bank_account' => '1111222233334444',
                'email' => 'seedbiz1@bookify.test',
                'phone' => '0955000001',
            ]);

            $userBiz2 = DB::table('users')->where('login_id', 'seed_business_02')->value('user_id');
            if (!$userBiz2) {
                $userBiz2 = DB::table('users')->insertGetId([
                    'login_id' => 'seed_business_02',
                    'name' => 'Seed 商家 2',
                    'email' => 'seedbiz2@bookify.test',
                    'password' => Hash::make('password'),
                    'phone' => '0955000002',
                    'address' => '新北市 Seed 路 2 號',
                    'role' => 'business',
                ]);
            }

            $biz2 = DB::table('businesses')->insertGetId([
                'user_id' => $userBiz2,
                'store_name' => 'Seed 商店 2',
                'bank_account' => '4444333322221111',
                'email' => 'seedbiz2@bookify.test',
                'phone' => '0955000002',
            ]);

            // Create books for both businesses
            $book1 = DB::table('books')->insertGetId([
                'name' => 'Seed Book A',
                'author_id' => $authorA,
                'isbn' => 'SEED0000001',
                'publish_date' => Carbon::now()->subYears(1)->toDateString(),
                'edition' => 1,
                'publisher' => 'Seed Press',
                'description' => '測試用書籍 A',
                'price' => 199.00,
                'condition' => 'new',
                'category_id' => $catExtras,
                'business_id' => $biz1,
                'stock' => 10,
                'listing' => 1,
            ]);

            $book2 = DB::table('books')->insertGetId([
                'name' => 'Seed Book B',
                'author_id' => $authorB,
                'isbn' => 'SEED0000002',
                'publish_date' => Carbon::now()->subMonths(6)->toDateString(),
                'edition' => 1,
                'publisher' => 'Seed Press',
                'description' => '測試用書籍 B',
                'price' => 299.00,
                'condition' => 'new',
                'category_id' => $catExtras,
                'business_id' => $biz1,
                'stock' => 5,
                'listing' => 1,
            ]);

            $book3 = DB::table('books')->insertGetId([
                'name' => 'Seed Book C',
                'author_id' => $authorA,
                'isbn' => 'SEED0000003',
                'publish_date' => Carbon::now()->subMonths(2)->toDateString(),
                'edition' => 1,
                'publisher' => 'Seed Press',
                'description' => '測試用書籍 C',
                'price' => 249.00,
                'condition' => 'new',
                'category_id' => $catExtras,
                'business_id' => $biz2,
                'stock' => 8,
                'listing' => 1,
            ]);

            $book4 = DB::table('books')->insertGetId([
                'name' => 'Seed Book D',
                'author_id' => $authorB,
                'isbn' => 'SEED0000004',
                'publish_date' => Carbon::now()->subMonths(3)->toDateString(),
                'edition' => 1,
                'publisher' => 'Seed Press',
                'description' => '測試用書籍 D',
                'price' => 159.00,
                'condition' => 'new',
                'category_id' => $catExtras,
                'business_id' => $biz2,
                'stock' => 12,
                'listing' => 1,
            ]);

            // Create images for these books
            DB::table('images')->insert([
                ['book_id' => $book1, 'image_index' => 0, 'image_url' => 'https://example.com/seed/book1.jpg'],
                ['book_id' => $book2, 'image_index' => 0, 'image_url' => 'https://example.com/seed/book2.jpg'],
                ['book_id' => $book3, 'image_index' => 0, 'image_url' => 'https://example.com/seed/book3.jpg'],
                ['book_id' => $book4, 'image_index' => 0, 'image_url' => 'https://example.com/seed/book4.jpg'],
            ]);

            // Create coupons for businesses
            $coupon1 = DB::table('coupons')->insertGetId([
                'name' => 'Seed10%',
                'business_id' => $biz1,
                'code' => 'SEED10',
                'description' => 'Seed 測試 10% 折扣',
                'start_date' => Carbon::now()->subDays(1),
                'end_date' => Carbon::now()->addMonths(6),
                'discount_type' => 'percent_off',
                'discount_value' => 10,
                'limit_price' => 0,
                'usage_limit' => 100,
                'used_count' => 0,
                'is_deleted' => 0,
            ]);

            $coupon2 = DB::table('coupons')->insertGetId([
                'name' => 'Seed$20',
                'business_id' => $biz2,
                'code' => 'SEED20',
                'description' => 'Seed 測試 20 元折抵',
                'start_date' => Carbon::now()->subDays(1),
                'end_date' => Carbon::now()->addMonths(6),
                'discount_type' => 'fixed',
                'discount_value' => 20,
                'limit_price' => 100,
                'usage_limit' => 100,
                'used_count' => 0,
                'is_deleted' => 0,
            ]);

            // Create two member users
            $userMem1 = DB::table('users')->where('login_id', 'seed_member_01')->value('user_id');
            if (!$userMem1) {
                $userMem1 = DB::table('users')->insertGetId([
                    'login_id' => 'seed_member_01',
                    'name' => 'Seed 會員 1',
                    'email' => 'seedmember1@bookify.test',
                    'password' => Hash::make('password'),
                    'phone' => '0966000001',
                    'address' => '台北市 會員 路 1 號',
                    'role' => 'member',
                ]);
            }

            $userMem2 = DB::table('users')->where('login_id', 'seed_member_02')->value('user_id');
            if (!$userMem2) {
                $userMem2 = DB::table('users')->insertGetId([
                    'login_id' => 'seed_member_02',
                    'name' => 'Seed 會員 2',
                    'email' => 'seedmember2@bookify.test',
                    'password' => Hash::make('password'),
                    'phone' => '0966000002',
                    'address' => '新北市 會員 路 2 號',
                    'role' => 'member',
                ]);
            }

            $member1 = DB::table('members')->insertGetId(['user_id' => $userMem1]);
            $member2 = DB::table('members')->insertGetId(['user_id' => $userMem2]);

            // Create carts and cart items
            $cart1 = DB::table('carts')->insertGetId(['member_id' => $member1]);
            $cart2 = DB::table('carts')->insertGetId(['member_id' => $member2]);

            DB::table('cart_items')->insert([
                ['cart_id' => $cart1, 'book_id' => $book1, 'quantity' => 1, 'price' => 199.00, 'subtotal' => 199.00],
                ['cart_id' => $cart1, 'book_id' => $book2, 'quantity' => 2, 'price' => 299.00, 'subtotal' => 598.00],
                ['cart_id' => $cart2, 'book_id' => $book3, 'quantity' => 1, 'price' => 249.00, 'subtotal' => 249.00],
            ]);

            // Create orders from carts
            $order1 = DB::table('orders')->insertGetId([
                'member_id' => $member1,
                'total_amount' => 797.00,
                'order_time' => Carbon::now()->subDays(2),
                'business_id' => $biz1,
                'shipping_fee' => 50.00,
                'payment_method' => 'Credit_card',
                'order_status' => 'Processing',
                'coupon_id' => $coupon1,
                'cart_id' => $cart1,
            ]);

            DB::table('order_details')->insert([
                ['order_id' => $order1, 'book_id' => $book1, 'quantity' => 1, 'piece_price' => 199.00, 'created_at' => Carbon::now()->subDays(2)],
                ['order_id' => $order1, 'book_id' => $book2, 'quantity' => 2, 'piece_price' => 299.00, 'created_at' => Carbon::now()->subDays(2)],
            ]);

            $order2 = DB::table('orders')->insertGetId([
                'member_id' => $member2,
                'total_amount' => 299.00,
                'order_time' => Carbon::now()->subDays(1),
                'business_id' => $biz2,
                'shipping_fee' => 40.00,
                'payment_method' => 'Cash',
                'order_status' => 'Shipped',
                'coupon_id' => $coupon2,
                'cart_id' => $cart2,
            ]);

            DB::table('order_details')->insert([
                ['order_id' => $order2, 'book_id' => $book3, 'quantity' => 1, 'piece_price' => 249.00, 'created_at' => Carbon::now()->subDays(1)],
            ]);

            DB::commit();
            $this->command->info('ExtendedSeeder completed.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('ExtendedSeeder failed: ' . $e->getMessage());
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
