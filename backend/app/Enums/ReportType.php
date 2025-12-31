<?php

namespace App\Enums;

// : string 表示這是一個 Backing Enum，每個案例都有對應的字串值
enum ReportType: string
{
    case SALES_SUMMARY = 'sales_summary';
    case INVENTORY_STATUS = 'inventory_status';
    case USER_ACTIVITY = 'user_activity';
    case COMPLAINT_ANALYSIS = 'complaint_analysis';

    // 您甚至可以像 C# 一樣寫方法，例如取得中文標籤
    public function label(): string
    {
        return match($this) {
            self::SALES_SUMMARY => '銷售總結報表',
            self::INVENTORY_STATUS => '庫存狀態報表',
            self::USER_ACTIVITY => '用戶活動報表',
            self::COMPLAINT_ANALYSIS => '投訴分析報表',
        };
    }
}