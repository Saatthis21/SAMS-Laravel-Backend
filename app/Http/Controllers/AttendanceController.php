<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceSession;
use App\Models\StudentAttendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Requirement: SAMS-PACK-303
     * Lecturer initiates a new live session.
     */
    public function initiateSession(Request $request)
    {
        // 1. VALIDATE INPUTS
        $validated = $request->validate([
            'labID' => 'required|integer',
            'session_code' => 'required|string|size:6',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        try {
            // 2. CREATE SESSION
            $session = AttendanceSession::create([
                'labID' => $validated['labID'],
                'session_code' => strtoupper($validated['session_code']),
                'duration_minutes' => $validated['duration_minutes'],
                'expires_at' => Carbon::now()->addMinutes($validated['duration_minutes']),
                'status' => 'Active',
            ]);

            // 3. RETURN SUCCESS
            return response()->json([
                'status' => 'success',
                'message' => 'Session created successfully.',
                'data' => [
                    'sessionID' => $session->session_id,
                    'sessionStatus' => $session->status,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'SESSION CREATION FAILED: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Requirement: SAMS-PACK-305 & 306
     * Student checks in with OTP and GPS coordinates.
     */
    public function checkIn(Request $request)
    {
        // 1. VALIDATE INPUTS
        $validated = $request->validate([
            'studentID' => 'required|string|max:15',
            'session_id' => 'required|integer',
            'submitted_code' => 'required|string|size:6',
            'gps_latitude' => 'required|numeric',
            'gps_longitude' => 'required|numeric',
            'gps_verified' => 'required|boolean',
        ]);

        try {
            // 2. VERIFY SESSION AND CODE
            $session = AttendanceSession::where('session_id', $validated['session_id'])
                ->where('status', 'Active')
                ->first();

            if (!$session) {
                return response()->json(['status' => 'error', 'message' => 'Session is inactive or does not exist.'], 400);
            }

            if (strtoupper($validated['submitted_code']) !== $session->session_code) {
                return response()->json(['status' => 'error', 'message' => 'INVALID SESSION CODE.'], 400);
            }

            // Check if expired
            if (Carbon::now()->greaterThan($session->expires_at)) {
                $session->update(['status' => 'Expired']);
                return response()->json(['status' => 'error', 'message' => 'This session has expired.'], 400);
            }

            // Prevent duplicate check-ins
            $existingRecord = StudentAttendance::where('session_id', $validated['session_id'])
                ->where('studentID', $validated['studentID'])
                ->first();

            if ($existingRecord) {
                return response()->json(['status' => 'error', 'message' => 'You have already checked into this session.'], 400);
            }

            // 3. RECORD ATTENDANCE
            $attendance = StudentAttendance::create([
                'session_id' => $validated['session_id'],
                'studentID' => $validated['studentID'],
                'submitted_code' => strtoupper($validated['submitted_code']),
                'gps_latitude' => $validated['gps_latitude'],
                'gps_longitude' => $validated['gps_longitude'],
                'gps_verified' => $validated['gps_verified'],
                'sync_pusat_adab' => false, // Default to false until external sync happens
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Check-in successful.',
                'data' => [
                    'checkInStatus' => 'PRESENT',
                    'checkInTime' => $attendance->created_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'ATTENDANCE RECORD FAILED: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Requirement: SAMS-PACK-307
     * Fetch attendance report for dashboard.
     */
    public function getAttendanceReport(Request $request)
    {
        $validated = $request->validate([
            'studentID' => 'required_without:lecturerID|string',
            'lecturerID' => 'required_without:studentID|string',
            'subjectCode' => 'nullable|string'
        ]);

        try {
            // Note: This is a simplified query. In a full system, you would join
            // with lab_sections and courses to filter by specific subjects.
            $query = StudentAttendance::with('session');

            if ($request->has('studentID')) {
                $query->where('studentID', $validated['studentID']);
            }

            $records = $query->get();

            $totalPresent = $records->where('gps_verified', true)->count();
            $totalClasses = $records->count(); // Assuming each record represents an attempted class

            $attendancePercentage = $totalClasses > 0 ? ($totalPresent / $totalClasses) * 100 : 0;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'reportData' => $records,
                    'totalPresent' => $totalPresent,
                    'totalClasses' => $totalClasses,
                    'attendancePercentage' => round($attendancePercentage, 2),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'REPORT RETRIEVAL FAILED: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLiveCount($session_id)
    {
        try {
            // Count how many records exist for this specific session in the database
            $count = StudentAttendance::where('session_id', $session_id)->count();

            return response()->json([
                'status' => 'success',
                'count' => $count
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch count'], 500);
        }
    }
}
