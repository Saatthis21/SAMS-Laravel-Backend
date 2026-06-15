<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    protected $table = 'fee_structures';

    protected $fillable = [
        'program',
        'semester',
        'fee_amount',
        'deadline_week'
    ];
}