<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case PAID = 'paid';
    case REFUNDED = 'refunded';
    case FAILD = 'faild';


    public static function values(){
        return array_column(self::cases(), 'value');
    }
}
