<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Authenticatable
{
    protected $table = 'students';
    protected $primaryKey = 'studentID';
    public $incrementing = false;
    protected $keyType = 'string';

    // Matches your SDD exactly, plus the password
    protected $fillable = [
        'studentID', 
        'student_name', 
        'student_year', 
        'student_course', 
        'password'
    ];
}