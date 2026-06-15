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

    public static function addCourseToSubmission($data) {
        return self::create($data);
    }

    public function dropCourse() {
        return $this->delete();
    }

    public function updateLabSection($newLabID) {
        $this->labID = $newLabID;
        return $this->save();
    }


}