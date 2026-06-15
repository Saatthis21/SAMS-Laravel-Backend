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

    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Payment belongs to Fee
    public function fee()
    {
        return $this->belongsTo(
            TuitionFee::class,
            'fee_id',
            'fees_id'
        );
    }

    // Payment belongs to Student
    public function student()
    {
        return $this->belongsTo(
            Student::class,
            'student_id',
            'student_id'
        );
    }

    // Payment has one Receipt
    public function receipt()
    {
        return $this->hasOne(
            Receipt::class,
            'payment_id',
            'payment_id'
        );
    }
}

