<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Enums\ReportType; // 記得引入 Enum
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum; // 用於驗證 Enum

class ReportController extends Controller
{
    /**
     * 取得報表清單
     */
    public function index(Request $request)
    {
        $query = Report::with('admin:admin_id,username');

        // 篩選：利用 Enum 進行類型篩選
        if ($request->has('type')) {
            // 從字串轉換為 Enum 物件，若非法字串則 tryFrom 會回傳 null
            $type = ReportType::tryFrom($request->type);
            if ($type) {
                $query->where('report_type', $type);
            }
        }

        $reports = $query->orderBy('generation_date', 'desc')->paginate(10);

        return response()->json($reports);
    }

    /**
     * 取得報表詳細內容
     */
    public function show($id)
    {
        // 取得報表，若不存在回傳 404
        $report = Report::with('admin')->findOrFail($id);

        return response()->json([
            'id' => $report->report_id,
            'type_label' => $report->report_type->label(), // 使用我們在 Enum 寫的方法
            'type_value' => $report->report_type->value,
            'data' => $report
        ]);
    }

    /**
     * 生成報表 (範例實作)
     */
    public function store(Request $request)
    {
        // 驗證輸入內容
        $validated = $request->validate([
            // Laravel 內建支援驗證是否為指定的 Enum 值
            'report_type' => ['required', new Enum(ReportType::class)],
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
        ]);

        // 這裡您可以根據報表類型執行不同的統計邏輯
        // 建議實務上這段會放進一個 Service
        $statsData = $this->generateStatsData(
            ReportType::from($validated['report_type']),
            $validated['start_date'],
            $validated['end_date']
        );

        $report = Report::create([
            'admin_id' => $request->user()->admin_id,
            'report_type'       => $validated['report_type'],
            'time_period_start' => $validated['start_date'],
            'time_period_end'   => $validated['end_date'],
            'stats_data'        => $statsData,
        ]);

        return response()->json([
            'message' => '報表生成成功',
            'report'  => $report
        ], 201);
    }

    /**
     * 刪除報表
     */
    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return response()->json(['message' => '報表已刪除']);
    }

    /**
     * 模擬生成數據的私有方法
     */
    private function generateStatsData(ReportType $type, $start, $end)
    {
        // 根據 Enum 類型，可以在這裡寫不同的統計邏輯 (或調用對應的 Service)
        return match($type) {
            ReportType::SALES_SUMMARY       => $this->calculateSales($start, $end),
            ReportType::INVENTORY_STATUS    => $this->calculateInventory(),
            ReportType::USER_ACTIVITY       => $this->calculateUserActivity($start, $end),
            ReportType::COMPLAINT_ANALYSIS  => $this->calculateComplaints($start, $end),
            default => ['info' => '尚未實作此類型的統計邏輯'],
        };
    }

    private function calculateSales($start, $end){
        // 取得該區間內所有訂單，並關聯優惠券
        $orders = \App\Models\Order::with('coupon')
            ->whereBetween('order_time', [$start, $end])
            ->where('status', 'Completed') // 假設只計算已完成訂單
            ->get();
        $totalRevenue = $orders->sum(function ($order) {
            $subtotal = $order->total_amount + $order->shipping_fee;
            $discount = 0;

            if ($order->coupon) {
                if ($order->coupon->type === 'percent_off') {
                    // 趴數折扣 (例如 10% off)
                    $discount = $order->total_amount * ($order->coupon->discount_value / 100);
                } else {
                    // 固定金額折扣
                    $discount = $order->coupon->discount_value;
                }
            }
            return $subtotal - $discount;
        });

        return [
            'order_count' => $orders->count(),
            'total_revenue' => round($totalRevenue, 2),
            'period' => "{$start} to {$end}"
        ];
    }

    private function calculateInventory(){
        $inventory = \App\Models\Book::select('business_id', 'condition')
                ->selectRaw('SUM(stock) as total_stock')
                ->selectRaw('COUNT(book_id) as total_items')
                ->groupBy('business_id', 'condition')
                ->with('business:business_id,name') // 關聯商家名稱
                ->get();

        return [
            'generated_at' => now()->toDateTimeString(),
            'details' => $inventory->map(function ($item) {
                return [
                    'business' => $item->business->name ?? '未知商家',
                    'condition' => $item->condition,
                    'total_stock'   => (int) $item->total_stock,
                    'total_items'   => (int) $item->total_items,
                ];
            })
        ];
    }

    private function calculateUserActivity($start, $end){
        // Member: 搜尋紀錄與訂單數
        $members = \App\Models\User::where('role', 'member')
                   ->select('user_id', 'name')
                   ->withCount([
                       'searchHistories' => function ($query) use ($start, $end) {
                           $query->whereBetween('search_time', [$start, $end]);
                       },
                       'orders as successful_orders_count' => function ($query) use ($start, $end) {
                           $query->whereBetween('order_time', [$start, $end])
                                ->where('order_status', 'Completed');
                       },
                       'orders as cancelled_orders_count' => function ($query) use ($start, $end) {
                           $query->whereBetween('order_time', [$start, $end])
                                ->where('order_status', 'Cancelled');
                       }
                   ])
                   ->get()
                   ->map(function ($user) {
                       return [
                           'username'     => $user->name,
                           'search_count' => $user->search_logs_count, // withCount 會自動產生此欄位
                           'successful_orders'  => $user->successful_orders_count, // 注意別名
                           'cancelled_orders'   => $user->cancelled_orders_count,  // 注意別名
                       ];
                   });

        // Business: 訂單數 (賣出的)
        $businesses = \App\Models\User::where('role', 'business')
                     ->withCount([
                         // 1. 成功的銷售訂單
                         'salesOrders as successful_sales_count' => function ($query) use ($start, $end) {
                             $query->whereBetween('order_time', [$start, $end])
                                   ->where('order_status', 'Completed');
                         },
                         // 2. 取消的銷售訂單
                         'salesOrders as cancelled_sales_count' => function ($query) use ($start, $end) {
                             $query->whereBetween('order_time', [$start, $end])
                                   ->where('order_status', 'Cancelled');
                         },
                         // 3. 處理中的訂單 (選配：可以看出商家是否積壓訂單)
                         'salesOrders as processing_sales_count' => function ($query) use ($start, $end) {
                             $query->whereBetween('order_time', [$start, $end])
                                   ->whereIn('order_status', ['Received', 'Processing', 'Shipped']);
                         }
                     ])
                     ->get()
                     ->map(function ($user) {
                         return [
                             'business_name' => $user->business_name,
                             'successful_sales' => $user->successful_sales_count,
                             'cancelled_sales'  => $user->cancelled_sales_count,
                             'processing_sales' => $user->processing_sales_count,
                             'total_sales'      => $user->successful_sales_count +
                                                   $user->cancelled_sales_count +
                                                   $user->processing_sales_count,
                         ];
                     });

        return [
            'members_activity' => $members,
            'businesses_activity' => $businesses
        ];
    }

    private function calculateComplaints($start, $end){
        $complaints = \App\Models\Complaint::with(['user:id,username'])
                ->whereBetween('complaint_time', [$start, $end])
                ->orderBy('complaint_time', 'desc')
                ->get();

        return [
            'total_complaints' => $complaints->count(),
            'list' => $complaints->map(function ($c) {
                return [
                    'time' => $c->complaint_time,
                    'user' => $c->user->username ?? '匿名',
                    'reason' => $c->reason,
                    'status' => $c->status
                ];
            })
        ];
    }
}