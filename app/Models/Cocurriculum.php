<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cocurriculum extends Model
{
    protected $table = 'cocurriculum';

    protected $fillable = [
        'studentID',
        'subject_code',
        'subject_name',
        'hours_recorded',
        'hours_required',
        'credits',
        'status',
        'rejection_reason',
    ];
}