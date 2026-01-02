<?php

namespace App\Enums;

// : string 表示這是一個 Backing Enum，每個案例都有對應的字串值
enum CouponType: string
{
    case SHIPPING = 'shipping';
    case SEASONAL = 'seasonal';
    case SPECIAL_EVENT = 'special_event';
}