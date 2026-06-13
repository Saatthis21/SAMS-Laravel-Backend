<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisteredCourse extends Model
{
    // Link to the child table
    protected $table = 'registered_course';
    protected $primaryKey = 'registeredID';

    protected $fillable = [
        'submissionID', 
        'course_code', 
        'labID'
    ];
}