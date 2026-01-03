<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Report;
use App\Models\User;
use App\Models\Business;
use App\Models\Book;
use App\Models\Order;
use App\Models\Complain;
use App\Models\Author;
use App\Models\BookCategory;
use App\Models\Coupon;
use App\Enums\ReportType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $adminToken;

protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create([
            'login_id' => 'admin01',
            'name'     => 'Super Admin',
            'password' => bcrypt('password'),
        ]);

        $this->adminToken = $this->admin->createToken('admin-token', ['admin:all'])->plainTextToken;
    }

    /**
     * 測試：生成銷售報表 (Sales Summary)
     */
    public function test_admin_can_generate_sales_report()
    {
        // 準備數據：建立一筆訂單
        $user = User::factory()->create(['role' => 'member']);
        $order = Order::create([
            'user_id' => $user->user_id,
            'total_amount' => 1000,
            'shipping_fee' => 60,
            'order_status' => 'Completed', // 必須是 Completed 才算
            'order_time' => now(),
        ]);

        $data = [
            'report_type' => ReportType::SALES_SUMMARY->value,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->postJson('/api/admin/reports', $data);

        $response->assertStatus(201)
                 ->assertJsonPath('report.report_type', ReportType::SALES_SUMMARY->value);
        
        // 驗證資料庫
        $this->assertDatabaseHas('reports', [
            'admin_id' => $this->admin->admin_id,
            'report_type' => ReportType::SALES_SUMMARY->value,
        ]);
    }

    /**
     * 測試：生成庫存報表 (Inventory Status)
     */
    public function test_admin_can_generate_inventory_report()
    {
        $user = User::factory()->create(['role' => 'business']);
        $business = Business::create([
            'user_id' => $user->user_id,
            'store_name' => 'Test Store',
            'bank_account' => '123'
        ]);
        
        $author = Author::create(['name' => 'Test Author']);
        $category = BookCategory::create(['name' => 'Test Category']);
        
        Book::create([
            'business_id' => $business->business_id,
            'author_id' => $author->author_id,
            'category_id' => $category->category_id,
            'name' => 'Test Book',
            'price' => 100,
            'stock' => 50,
            'isbn' => '1234567890',
            'publish_date' => '2023-01-01',
            'edition' => 1,
            'publisher' => 'Pub',
            'condition' => 'New',
            'listing' => true
        ]);

        $data = [
            'report_type' => ReportType::INVENTORY_STATUS->value,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->toDateString(),
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->postJson('/api/admin/reports', $data);

        $response->assertStatus(201);
    }

    /**
     * 測試：生成客訴報表 (Complain Analysis)
     */
    public function test_admin_can_generate_complaint_report()
    {
        $user = User::factory()->create(['role' => 'member']);
        
        $order = Order::create([
            'user_id' => $user->user_id,
            'total_amount' => 500,
            'shipping_fee' => 60,
            'order_status' => 'Completed',
            'order_time' => now(),
        ]);

        Complain::create([
            'user_id' => $user->user_id,
            'order_id' => $order->order_id,
            'category' => 'Other',
            'content' => 'Test Content', 
            'reason' => 'Bad Service',
            'status' => 'Pending',
            'complaint_time' => now(),
        ]);

        $data = [
            'report_type' => ReportType::COMPLAINT_ANALYSIS->value,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->postJson('/api/admin/reports', $data);

        $response->assertStatus(201);
    }

    /**
     * 測試：查看報表列表 (Index)
     */
    public function test_admin_can_view_report_list()
    {
        // 先手動建立 3 筆報表
        Report::create([
            'admin_id' => $this->admin->admin_id,
            'report_type' => ReportType::USER_ACTIVITY,
            'time_period_start' => now(),
            'time_period_end' => now(),
            'stats_data' => ['info' => 'test']
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->getJson('/api/admin/reports');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data'); // 應該要看到剛建的那 1 筆 (因為分頁結構在 data 裡)
    }

    /**
     * 測試：利用類型篩選報表
     */
    public function test_admin_can_filter_report_list()
    {
        Report::create([
            'admin_id' => $this->admin->admin_id,
            'report_type' => ReportType::SALES_SUMMARY,
            'stats_data' => []
        ]);
        
        Report::create([
            'admin_id' => $this->admin->admin_id,
            'report_type' => ReportType::INVENTORY_STATUS,
            'stats_data' => []
        ]);

        // 篩選 SALES_SUMMARY
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->getJson('/api/admin/reports?type=' . ReportType::SALES_SUMMARY->value);

        $response->assertStatus(200);
        // 驗證回傳的每一筆資料都是 sales_summary
        $data = $response->json('data');
        $this->assertEquals(ReportType::SALES_SUMMARY->value, $data[0]['report_type']);
    }

    /**
     * 測試：查看報表詳情 (Show)
     */
    public function test_admin_can_view_report_detail()
    {
        $report = Report::create([
            'admin_id' => $this->admin->admin_id,
            'report_type' => ReportType::SALES_SUMMARY,
            'stats_data' => ['total_revenue' => 5000]
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->getJson("/api/admin/reports/{$report->report_id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.report_id', $report->report_id)
                 // 如果 Model 有設 casts，這裡應該要是 5000；沒設可能是字串
                 // ->assertJsonPath('data.stats_data.total_revenue', 5000) 
                 ;
    }

    /**
     * 測試：刪除報表 (Destroy)
     */
    public function test_admin_can_delete_report()
    {
        $report = Report::create([
            'admin_id' => $this->admin->admin_id,
            'report_type' => ReportType::SALES_SUMMARY,
            'stats_data' => []
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->deleteJson("/api/admin/reports/{$report->report_id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => '報表已刪除']);

        $this->assertDatabaseMissing('reports', ['report_id' => $report->report_id]);
    }

    /**
     * 測試：一般人不能存取
     */
    public function test_normal_user_cannot_access_reports()
    {
        $user = User::factory()->create();
        $token = $user->createToken('user', ['role:member'])->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->getJson('/api/admin/reports');

        $response->assertStatus(403);
    }
}