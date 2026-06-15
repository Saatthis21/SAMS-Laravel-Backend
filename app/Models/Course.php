<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{ 
    protected $table = 'courses';
    protected $primaryKey = 'course_code';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['course_code', 'course_name', 'credit_hours'];

    public static function getCourseDetails($courseCode)
    {
        return self::where('course_code', $courseCode)->first();
    }
}
