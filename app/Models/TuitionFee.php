<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TuitionFee extends Model
{
    use HasFactory;

    protected $table = 'fees';

    protected $primaryKey = 'fees_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'fees_id',
        'student_id',
        'program',
        'semester',
        'total_fee',
        'paid_amount',
        'balance',
        'status',
        'deadline_week'
    ];

    protected $casts = [
        'total_fee' => 'float',
        'paid_amount' => 'float',
        'balance' => 'float',
        'deadline_week' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Fee belongs to one student
    public function student()
    {
        return $this->belongsTo(
            Student::class,
            'student_id',
            'student_id'
        );
    }

    // One fee can have many payments
    public function payments()
    {
        return $this->hasMany(
            PaymentTransaction::class,
            'fee_id',
            'fees_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function getPaymentStatusAttribute()
    {
        if ($this->balance <= 0) {
            return 'PAID';
        }

        if ($this->paid_amount > 0) {
            return 'PARTIAL';
        }

        return 'UNPAID';
    }

    public function isBlocked()
    {
        return $this->balance > 0 &&
               $this->deadline_week <= 5;
    }
}
