<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'OrderId';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    protected $casts = [
        'UserId' => 'int',
        'OrderDate' => 'datetime',
        'TotalAmount' => 'float',
        'PromotionId' => 'int',
        'CreatedAt' => 'datetime',
        'UpdatedAt' => 'datetime',
        'CreatedBy' => 'int',
        'UpdatedBy' => 'int'
    ];

    protected $fillable = [
        'UserId',
        'OrderDate',
        'TotalAmount',
        'PromotionId',
        'Status',
        'CreatedBy',
        'UpdatedBy',
        'CreatedAt',
        'UpdatedAt'
    ];

    // Quan hệ với User (KHÁCH HÀNG)
    public function user()
    {
        return $this->belongsTo(User::class, 'UserId', 'UserId');
    }

    // Quan hệ với Promotion
    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'PromotionId', 'PromotionId');
    }

    // Quan hệ với OrderDetails
    public function orderDetails()
    {
        return $this->hasMany(Orderdetail::class, 'OrderId', 'OrderId');
    }
}