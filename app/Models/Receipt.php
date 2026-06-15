<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $table = 'receipts';

    protected $primaryKey = 'receipt_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [

    'receipt_id',

    'receipt_number',

    'payment_id',

    'student_id',

    'amount_paid',

    'balance',

    'payment_method',

    'note',

    'paid_at'

];

    protected $casts = [

        'amount_paid' => 'float',

        'balance' => 'float',

        'paid_at' => 'datetime'

    ];

    // Receipt belongs to Payment
    public function payment()
    {
        return $this->belongsTo(
            PaymentTransaction::class,
            'payment_id',
            'payment_id'
        );
    }

    // Receipt belongs to Student
    public function student()
    {
        return $this->belongsTo(
            Student::class,
            'student_id',
            'studentID'
        );
    }
}