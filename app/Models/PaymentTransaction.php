<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'payment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'payment_id',
        'receipt_id',
        'fee_id',
        'student_id',
        'amount',
        'payment_method',
        'status',
        'transaction_ref',
        'failure_reason',
        'paid_at'
    ];
}