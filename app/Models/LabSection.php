<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabSection extends Model
{
    protected $table = 'lab_sections'; // Verify this matches your DB table name
    protected $primaryKey = 'labID';   // Verify this matches your DB primary key
    public $timestamps = false;
    protected $fillable = ['labID', 'course_code', 'lab_num', 'date', 'time', 'date_2', 'time_2', 'max_capacity', 'current_capacity'];

    public static function getAvailableCapacity($labID)
    {
        $lab = self::find($labID);
        return $lab ? ($lab->max_capacity - $lab->current_capacity) : 0;
    }

    public function updateCapacity($operation)
    {
        if ($operation == 'increment') {
            $this->current_capacity++;
        } else if ($operation == 'decrement') {
            $this->current_capacity--;
        }
        return $this->save();
    }
}