<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabSection extends Model
{
    protected $table = 'lab_sections'; // Verify this matches your DB table name
    protected $primaryKey = 'labID';   // Verify this matches your DB primary key
    public $timestamps = false;
    
    // Crucial: ensure these are all listed
    protected $fillable = ['labID', 'course_code', 'lab_num', 'date', 'time', 'date_2', 'time_2', 'max_capacity', 'current_capacity'];
}