<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TuitionFee extends Model
{
    use HasFactory;

    // Direct mapping to your SDD specification
    protected $table = 'fees';
    protected $primaryKey = 'fees_id';
    public $incrementing = false; // Because it's a string ID
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

    // Relationship to the Student model
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'studentID');
    }
}