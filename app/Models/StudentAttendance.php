<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use HasFactory;

    // Explicitly define table since the model name doesn't natively match
    protected $table = 'attendance_records';
    protected $primaryKey = 'record_id';

    protected $fillable = [
        'session_id',
        'studentID',
        'submitted_code',
        'gps_latitude',
        'gps_longitude',
        'gps_verified',
        'sync_pusat_adab',
    ];

    // Ensure strict data typing when retrieving from the database
    protected $casts = [
        'gps_latitude' => 'decimal:8',
        'gps_longitude' => 'decimal:8',
        'gps_verified' => 'boolean',
        'sync_pusat_adab' => 'boolean',
    ];

    // Relationship: This Record belongs to a specific Session
    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id', 'session_id');
    }

    // Relationship: This Record belongs to a specific Student
    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }
}