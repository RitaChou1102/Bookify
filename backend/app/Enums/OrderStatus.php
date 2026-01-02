<?php

namespace App\Enums;

enum OrderStatus: string {
    case RECEIVED   = 'Received';
    case PROCESSING = 'Processing';
    case SHIPPED    = 'Shipped';
    case COMPLETED  = 'Completed';
    case CANCELLED  = 'Cancelled';
}