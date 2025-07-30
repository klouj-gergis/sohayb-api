<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;


class Order extends Model
{
    //
    protected $fillable = [
        'user_id',
        'status',
        'shipping_name',
        'shipping_phone',
        'subtotal',
        'tax',
        'shipping_cost',
        'total',
        'shipping_address',
        'canceled_at',
        'payment_method',
        'payment_status',
        'order_number',
        'notes',
        'transaction_id',
        'paid_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function generateOrderNumber()
    {
        $year = date('Y');
        $month = date('m');
        $randomNumber = strtoupper(substr(uniqid(), -6));
        return "ORD-{$year}-{$month}-{$randomNumber}";
    }

    public function canBeCancelled()
    {
        return in_array($this->status, [
            OrderStatus::PENDING,
            OrderStatus::PAID
        ]);
    }

    public function markAsPaid($transactionid)
    {
        $this->update([
            'status' => OrderStatus::PAID,
            'payment_status' => PaymentStatus::COMPLETED,
            'transaction_id' => $transactionid,
            'paid_at' => now(),
        ]);
    }

    public function markAsFaild(){
        $this->update([
            'payment_status' => PaymentStatus::FAILD,
        ]);
    }

    public function canAcceptPayment(){
        return $this->payment_status === PaymentStatus::PENDING  || 
               $this->payment_status === PaymentStatus::FAILD;
    }
}
