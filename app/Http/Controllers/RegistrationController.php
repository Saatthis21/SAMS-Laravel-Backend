<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\LabSection;
use App\Models\RegistrationSubmission;
use App\Models\RegisteredCourse;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function fetchAvailableCourses()
    {
        $courses = Course::all();

        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }

    public function fetchCourseLabs($course_code)
    {
        $labs = LabSection::where('course_code', $course_code)->get();

        return response()->json([
            'success' => true,
            'data' => $labs
        ]);
    }

    public function addCourseToDraft(Request $request)
    {
        try {
            $studentID = $request->input('studentID');
            $courseCode = $request->input('course_code');
            $requestedLabID = $request->input('labID');

            $submission = RegistrationSubmission::where('studentID', $studentID)
                ->whereIn('overall_status', ['Pending', 'Pending Edit'])
                ->first();

            if (!$submission) {
                $submission = new RegistrationSubmission();
                $submission->studentID = $studentID;
                $submission->overall_status = 'Pending';
                $submission->date = now()->toDateString();
                $submission->save();
            }

            $activeSubmissionID = $submission->submissionID;
            $cleanCourseCode = trim($courseCode);

            $existingCourses = DB::table('registered_course')
                ->join('registration_submissions', 'registered_course.submissionID', '=', 'registration_submissions.submissionID')
                ->where('registration_submissions.studentID', $studentID)
                ->whereIn('registration_submissions.overall_status', [
                    'Pending',
                    'Pending Edit',
                    'Pending Review',
                    'Confirmed',
                    'Rejected'
                ])
                ->pluck('registered_course.course_code')
                ->toArray();

            $cleanExistingCourses = array_map('trim', $existingCourses);

            if (in_array($cleanCourseCode, $cleanExistingCourses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already registered for a section of this course.'
                ], 400);
            }

            $currentCredits = DB::table('registered_course')
                ->join('courses', 'registered_course.course_code', '=', 'courses.course_code')
                ->where('registered_course.submissionID', $activeSubmissionID)
                ->sum('courses.credit_hours');

            $newCourse = DB::table('courses')->where('course_code', $courseCode)->first();

            if (!$newCourse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course not found.'
                ], 404);
            }

            if (($currentCredits + $newCourse->credit_hours) > 20) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credit hour maximum limit (20) exceeded.'
                ], 400);
            }

            $newLab = DB::table('lab_sections')->where('labID', $requestedLabID)->first();

            if (!$newLab) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lab section not found.'
                ], 404);
            }

            if ($newLab->current_capacity >= $newLab->max_capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'This section is currently full.'
                ], 400);
            }

            $existingLabs = DB::table('registered_course')
                ->join('lab_sections', 'registered_course.labID', '=', 'lab_sections.labID')
                ->where('registered_course.submissionID', $activeSubmissionID)
                ->select('lab_sections.date', 'lab_sections.time', 'lab_sections.date_2', 'lab_sections.time_2')
                ->get();

            foreach ($existingLabs as $ex) {
                if ($newLab->date == $ex->date && $newLab->time == $ex->time) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Clash detected on ' . $newLab->date . ' at ' . $newLab->time
                    ], 400);
                }

                if (!empty($ex->date_2) && $newLab->date == $ex->date_2 && $newLab->time == $ex->time_2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Clash detected on ' . $newLab->date . ' at ' . $newLab->time
                    ], 400);
                }

                if (!empty($newLab->date_2) && !empty($newLab->time_2)) {
                    if ($newLab->date_2 == $ex->date && $newLab->time_2 == $ex->time) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Clash detected on ' . $newLab->date_2 . ' at ' . $newLab->time_2
                        ], 400);
                    }

                    if (!empty($ex->date_2) && $newLab->date_2 == $ex->date_2 && $newLab->time_2 == $ex->time_2) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Clash detected on ' . $newLab->date_2 . ' at ' . $newLab->time_2
                        ], 400);
                    }
                }
            }

            DB::table('registered_course')->insert([
                'submissionID' => $activeSubmissionID,
                'course_code' => $courseCode,
                'labID' => $requestedLabID,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('lab_sections')
                ->where('labID', $requestedLabID)
                ->increment('current_capacity');

            return response()->json([
                'success' => true,
                'message' => 'Success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getMyCourses($studentID)
    {
        try {
            $submission = RegistrationSubmission::where('studentID', $studentID)
                ->where('overall_status', 'Pending Edit')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('registered_course')
                        ->whereColumn('registered_course.submissionID', 'registration_submissions.submissionID');
                })
                ->orderBy('updated_at', 'desc')
                ->first();

            if (!$submission) {
                $submission = RegistrationSubmission::where('studentID', $studentID)
                    ->whereIn('overall_status', ['Pending', 'Pending Review', 'Confirmed', 'Rejected'])
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('registered_course')
                            ->whereColumn('registered_course.submissionID', 'registration_submissions.submissionID');
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            $myCourses = DB::table('registered_course')
                ->join('registration_submissions', 'registered_course.submissionID', '=', 'registration_submissions.submissionID')
                ->join('courses', 'registered_course.course_code', '=', 'courses.course_code')
                ->join('lab_sections', 'registered_course.labID', '=', 'lab_sections.labID')
                ->where('registration_submissions.studentID', $studentID)
                ->whereIn('registration_submissions.overall_status', [
                    'Pending',
                    'Pending Review',
                    'Pending Edit',
                    'Confirmed',
                    'Rejected'
                ])
                ->select(
                    'registered_course.registeredID',
                    'courses.course_code',
                    'courses.course_name',
                    'courses.credit_hours',
                    'lab_sections.lab_num',
                    'lab_sections.time',
                    'registration_submissions.overall_status as status'
                )
                ->get();

            $totalCredits = $myCourses->sum('credit_hours');
            $balance = 20 - $totalCredits;

            if (!$submission) {
                return response()->json([
                    'status' => 'empty',
                    'message' => 'No drafted courses found.',
                    'data' => [],
                    'balanceCreditHours' => 20,
                    'overall_status' => 'Pending',
                    'rejection_reason' => null
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => $myCourses,
                'balanceCreditHours' => $balance,
                'overall_status' => $submission->overall_status,
                'rejection_reason' => $submission->rejection_reason
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}