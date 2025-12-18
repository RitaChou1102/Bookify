<?php

namespace App\Http\Controllers;

use App\Models\Complain;
use App\Models\Order;
use Illuminate\Http\Request;

class ComplainAdminController extends Controller
{
    // 1. 管理員點開投訴時呼叫
    public function markAsInProgress($id)
    {
        $complain = Complain::findOrFail($id);
        $warning = null;
        // 只有在 pending 狀態才需要改為處理中
        if ($complain->complaint_status === 'pending') {
            $complain->update([
                'complaint_status' => 'in_progress'
            ]);
        }
        elseif ($complain->complaint_status === 'resolved') {
            $warning = '提醒：此投訴已於 ' . $complain->complaint_time->format('Y-m-d H:i') . ' 結案，再次送出將會覆蓋原有的處理結果。';
        }

        return response()->json([
            'message' => '投訴已進入處理中狀態',
            'warning' => $warning,
            'data' => $complain
        ]);
    }

    // 2. 管理員輸入處理結果並點擊「送出」時呼叫
    public function resolveComplain(Request $request, $id)
    {
        $request->validate([
            'result' => 'required|string'
        ]);
        $complain = Complain::findOrFail($id);

        $complain->update([
            'result' => $request->result,
            'complaint_status' => 'resolved' // 狀態改為已解決
        ]);
        return response()->json([
            'message' => '投訴已成功處理完畢'
        ]);
    }
}