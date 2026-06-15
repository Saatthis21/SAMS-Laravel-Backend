<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $table = 'attendance_sessions';
    protected $primaryKey = 'session_id';

    // Protects against Mass Assignment vulnerabilities
    protected $fillable = [
        'labID',
        'session_code',
        'duration_minutes',
        'expires_at',
        'status',
    ];

    // Ensures Laravel treats this column as a Carbon date object
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // Relationship: One Session has Many Attendance Records
    public function records()
    {
        return $this->hasMany(StudentAttendance::class, 'session_id', 'session_id');
    }

    // Relationship: A Session belongs to one Lab Section
    public function labSection()
    {
        return $this->belongsTo(LabSection::class, 'labID', 'labID');
    }
}
