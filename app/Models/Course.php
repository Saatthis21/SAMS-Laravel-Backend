<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
   // 1. Tell it exactly which table to look at
    protected $table = 'courses';

    // 2. Tell it your custom primary key from your SDD
    protected $primaryKey = 'course_code';

    // 3. Since your primary key is a string (like "BCS2143") and not an auto-number, we MUST add these two lines:
    public $incrementing = false;
    protected $keyType = 'string';

    // 4. Protect the database by allowing only these specific columns to be filled
    protected $fillable = ['course_code', 'course_name', 'credit_hours'];
}
