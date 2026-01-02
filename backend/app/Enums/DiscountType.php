<?php

namespace App\Enums;

// : string 表示這是一個 Backing Enum，每個案例都有對應的字串值
enum DiscountType: string
{
    case PERCENT_OFF = 'percent_off';
    case FIXED = 'fixed';
}