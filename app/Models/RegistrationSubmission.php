<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationSubmission extends Model
{
    // Link to the parent table
    protected $primaryKey = 'submissionID';
    protected $table = 'registration_submissions';
    

    // Protect the database by allowing only these columns to be filled
    protected $fillable = [
        'studentID', 
        'date', 
        'overall_status', 
        'rejection_reason'
    ];
}