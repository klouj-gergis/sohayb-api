<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processin';
    case SHIPPED = 'shipped';
    case PAID = 'paid';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';


    public static function values(){
        return array_column(self::cases(), 'value');
    }
}
