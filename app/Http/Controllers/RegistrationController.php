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

            if ($submission) {
                // Check if the cart is actually empty
                $courseCount = DB::table('registered_course')->where('submissionID', $submission->submissionID)->count();

                if ($courseCount == 0 && $submission->overall_status == 'Pending Edit') {
                    $submission->overall_status = 'Pending';
                    $submission->rejection_reason = null;
                    $submission->save();
                }
            }

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

            // --- THE FIX: Smart Clash Detection ---
            if ($this->checkTimetableClash($activeSubmissionID, $newLab)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timetable clash detected with an existing course in your cart.'
                ], 400);
            }
            // --------------------------------------

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
                    'Pending', 'Pending Review', 'Pending Edit', 'Confirmed', 'Rejected'
                ])
                ->select(
                    'registered_course.registeredID',
                    'courses.course_code',
                    'courses.course_name',
                    'courses.credit_hours',
                    'lab_sections.lab_num',
                    'lab_sections.time',
                    'registration_submissions.overall_status as status',
                    // --- 1. GRAB THE TIMESTAMPS ---
                    'registered_course.created_at as course_created_at',
                    'registration_submissions.updated_at as cart_updated_at'
                )
                ->get();

            // --- 2. THE SMART LOGIC ---
            // Loop through the courses to separate the "New" ones from the "Rejected" ones
            foreach ($myCourses as $course) {
                if ($course->status === 'Pending Edit') {
                    // If the course was added AFTER the cart was rejected, it is a brand new course!
                    if (strtotime($course->course_created_at) > strtotime($course->cart_updated_at)) {
                        $course->status = 'Pending';
                    }
                }
            }

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

    public function dropCourseFromDraft($registeredID)
    {
        try {
            return DB::transaction(function () use ($registeredID) {
                $registration = RegisteredCourse::find($registeredID);

                if (!$registration) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Registration not found.'
                    ], 404);
                }

                $submissionID = $registration->submissionID;
                $lab = LabSection::find($registration->labID);

                if ($lab && $lab->current_capacity > 0) {
                    $affectedRows = DB::update(
                        'UPDATE lab_sections SET current_capacity = current_capacity - 1 WHERE labID = ?',
                        [$registration->labID]
                    );

                    if ($affectedRows === 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Database Error: Could not update lab capacity'
                        ], 500);
                    }
                }

                $registration->delete();

                $remainingCourses = RegisteredCourse::where('submissionID', $submissionID)->count();
                $submission = RegistrationSubmission::find($submissionID);

                if ($submission) {
                    if ($remainingCourses === 0 && in_array($submission->overall_status, ['Pending', 'Pending Edit'])) {
                        $submission->delete();
                    } else if ($submission->overall_status === 'Pending Edit') {
                        $submission->rejection_reason = null;
                        // --- THE FIX: Freeze the clock ---
                        $submission->timestamps = false;
                        $submission->save();
                        // ---------------------------------
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Course dropped successfully.'
                ]);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function changeLabSection(Request $request, $registeredID)
    {
        try {
            return DB::transaction(function () use ($request, $registeredID) {
                $newLabID = $request->input('new_lab_id');
                $record = RegisteredCourse::find($registeredID);

                $newLab = LabSection::find($newLabID);

                if ($this->checkTimetableClash($record->submissionID, $newLab, $registeredID)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timetable clash detected with your existing courses.'
                ], 400);
            }

                if (!$record) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Registration not found'
                    ]);
                }

                if ($record->labID == $newLabID) {
                    return response()->json(['success' => true]);
                }

                $newLab = LabSection::find($newLabID);

                if (!$newLab) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Lab section not found'
                    ]);
                }

                if ($newLab->current_capacity >= $newLab->max_capacity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This section is fully booked!'
                    ]);
                }

                DB::update(
                    'UPDATE lab_sections SET current_capacity = current_capacity - 1 WHERE labID = ? AND current_capacity > 0',
                    [$record->labID]
                );

                DB::update(
                    'UPDATE lab_sections SET current_capacity = current_capacity + 1 WHERE labID = ?',
                    [$newLabID]
                );

                $record->labID = $newLabID;
                $record->save();

                $submission = RegistrationSubmission::find($record->submissionID);

                if ($submission && $submission->overall_status === 'Pending Edit') {
                    $submission->rejection_reason = null;
                    // --- THE FIX: Freeze the clock ---
                    $submission->timestamps = false; 
                    $submission->save();
                    // ---------------------------------
                }

                return response()->json(['success' => true]);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function submitRegistration($studentID)
    {
        try {
            $submission = RegistrationSubmission::where('studentID', $studentID)
                ->whereIn('overall_status', ['Pending', 'Pending Edit'])
                ->first();

            if ($submission) {
                $submission->overall_status = 'Pending Review';
                $submission->rejection_reason = null;
                $submission->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully submitted for review!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No active draft found to submit.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function fetchPendingSubmissions()
    {
        try {
            $submissions = RegistrationSubmission::where('overall_status', 'Pending Review')
                ->orderBy('date', 'asc')
                ->get();

            $data = [];

            foreach ($submissions as $sub) {
                $student = DB::table('students')
                    ->where('studentID', $sub->studentID)
                    ->first();

                $credits = DB::table('registered_course')
                    ->join('courses', 'registered_course.course_code', '=', 'courses.course_code')
                    ->where('registered_course.submissionID', $sub->submissionID)
                    ->sum('courses.credit_hours');

                $data[] = [
                    'submissionID' => $sub->submissionID,
                    'studentID' => $sub->studentID,
                    'student_name' => $student ? $student->student_name : 'Unknown Student',
                    'date' => date('j M', strtotime($sub->date ?? now())),
                    'total_credits' => $credits
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function fetchSubmissionDetails($submissionID)
    {
        try {
            $submission = RegistrationSubmission::where('submissionID', $submissionID)->first();

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission not found'
                ], 404);
            }

            $student = DB::table('students')
                ->where('studentID', $submission->studentID)
                ->first();

            $studentDetails = [
                'submissionID' => $submission->submissionID,
                'studentID' => $submission->studentID,
                'student_name' => $student ? $student->student_name : 'Unknown Student',
                'student_course' => $student ? $student->student_course : 'Unknown Program',
            ];

            $courses = DB::table('registered_course')
                ->join('courses', 'registered_course.course_code', '=', 'courses.course_code')
                ->join('lab_sections', 'registered_course.labID', '=', 'lab_sections.labID')
                ->where('registered_course.submissionID', $submissionID)
                ->select(
                    'courses.course_code',
                    'courses.course_name',
                    'courses.credit_hours',
                    'lab_sections.lab_num'
                )
                ->get();

            return response()->json([
                'success' => true,
                'student' => $studentDetails,
                'courses' => $courses
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function processReviewDecision(Request $request)
    {
        try {
            $submissionID = $request->input('submissionID');
            $decision = $request->input('decision');
            $reason = $request->input('rejection_reason');

            $submission = RegistrationSubmission::find($submissionID);

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission not found'
                ], 404);
            }

            if ($decision === 'Approve') {
                $submission->overall_status = 'Confirmed';
                $submission->rejection_reason = null;
            } else if ($decision === 'Reject') {
                $submission->overall_status = 'Pending Edit';
                $submission->rejection_reason = $reason;
            }

            $submission->save();

            return response()->json(['success' => true], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function checkTimetableClash($submissionID, $newLab, $excludeRegisteredID = null)
    {
        $submission = DB::table('registration_submissions')->where('submissionID', $submissionID)->first();
        if (!$submission) return false;

        $studentID = $submission->studentID;

        $query = DB::table('registered_course')
            ->join('registration_submissions', 'registered_course.submissionID', '=', 'registration_submissions.submissionID')
            ->join('lab_sections', 'registered_course.labID', '=', 'lab_sections.labID')
            ->where('registration_submissions.studentID', $studentID)
            ->whereIn('registration_submissions.overall_status', ['Pending', 'Pending Edit', 'Pending Review', 'Confirmed']);

        if ($excludeRegisteredID !== null) {
            $query->where('registered_course.registeredID', '!=', $excludeRegisteredID);
        }

        $existingLabs = $query->select('lab_sections.date', 'lab_sections.time', 'lab_sections.date_2', 'lab_sections.time_2')->get();

        // Kept perfectly at 120 minutes (2 Hours)
        $duration = 120; 

        $newDates = [
            ['date' => strtolower(trim($newLab->date ?? '')), 'time' => $this->timeToMins($newLab->time ?? '')],
            ['date' => strtolower(trim($newLab->date_2 ?? '')), 'time' => $this->timeToMins($newLab->time_2 ?? '')]
        ];

        foreach ($existingLabs as $ex) {
            $existingDates = [
                ['date' => strtolower(trim($ex->date ?? '')), 'time' => $this->timeToMins($ex->time ?? '')],
                ['date' => strtolower(trim($ex->date_2 ?? '')), 'time' => $this->timeToMins($ex->time_2 ?? '')]
            ];

            foreach ($newDates as $newD) {
                if (empty($newD['date']) || $newD['time'] === null) continue;

                foreach ($existingDates as $exD) {
                    if (empty($exD['date']) || $exD['time'] === null) continue;

                    if ($newD['date'] === $exD['date']) {
                        
                        $start1 = $newD['time'];
                        $end1 = $start1 + $duration;
                        
                        $start2 = $exD['time'];
                        $end2 = $start2 + $duration;

                        if ($start1 < $end2 && $start2 < $end1) {
                            return true; // CLASH DETECTED!
                        }
                    }
                }
            }
        }
        return false; 
    }

    private function timeToMins($timeString) 
    {
        if (empty($timeString)) return null;
        $parts = explode(':', trim($timeString));
        if (count($parts) < 2) return null;
        
        $hours = (int)$parts[0];
        $minutes = (int)$parts[1];
        
        return ($hours * 60) + $minutes;
    }

    }