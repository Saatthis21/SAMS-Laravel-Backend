<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationSubmission extends Model
{
    // Link to the parent table
    protected $primaryKey = 'submissionID';
    protected $table = 'registration_submissions';
    protected $fillable = [
        'studentID', 
        'date', 
        'overall_status', 
        'rejection_reason'
    ];

    public static function createSubmission($data) {
        // Automatically sets status to 'Pending Review' as per SDD
        $data['overall_status'] = 'Pending Review';
        $data['date'] = now();
        return self::create($data);
    }

    public function updateStatus($newStatus, $providedReason = null) {
        $this->overall_status = $newStatus;
        if ($newStatus === 'Rejected') {
            $this->rejection_reason = $providedReason;
        }
        return $this->save();
    }
}