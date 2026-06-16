<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceSession;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Support\Str; // REQUIRED for generating the random class code

class AttendanceController extends Controller
{
    /**
     * Requirement: SAMS-PACK-303
     * Lecturer initiates a new live session dynamically.
     */
    public function startSession(Request $request)
    {
        // 1. VALIDATE INPUTS from Flutter
        $validated = $request->validate([
            'lecturer_id' => 'required|string',
            'subject_code' => 'required|string',
        ]);

        try {
            // 2. GENERATE UNIQUE CODE (6 Characters, Uppercase)
            $uniqueCode = strtoupper(Str::random(6));

            // 3. CREATE SESSION IN DATABASE
            $session = AttendanceSession::create([
                'lecturer_id' => $validated['lecturer_id'], // Ensure this column exists in your DB!
                'subject_code' => $validated['subject_code'], // Ensure this column exists in your DB!
                'session_code' => $uniqueCode,
                'duration_minutes' => 15, // Hardcoded to 15 mins based on UI
                'expires_at' => Carbon::now()->addMinutes(15),
                'status' => 'Active',
            ]);

            // 4. RETURN SUCCESS TO FLUTTER
            return response()->json([
                'status' => 'success',
                'message' => 'Session created successfully.',
                'session_id' => $session->session_id ?? $session->id, // Adjust based on your primary key
                'class_code' => $uniqueCode
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
        // 1. Validate the incoming request from Flutter
        $validated = $request->validate([
            'lecturer_id' => 'required|string',
            'subject_code' => 'required|string',
        ]);

        try {
            // 2. Find the active/closed session for this subject
            $session = \App\Models\AttendanceSession::where('subject_code', $validated['subject_code'])
                        ->orderBy('created_at', 'desc')
                        ->first();

            if (!$session) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No attendance session found for this subject.'
                ], 404);
            }

            // 3. Get all the attendance records for this session
            $records = \App\Models\StudentAttendance::where('session_id', $session->session_id ?? $session->id)->get();

            // 4. Format the data for Flutter
            $formattedData = $records->map(function ($record) {
                return [
                    'studentID' => $record->studentID,
                    'name' => 'Student ' . $record->studentID, // Replace with actual name if you have a User relationship
                    'status' => $record->gps_verified ? 'Present' : 'Absent',
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Real-time polling for Lecturer Dashboard
     */
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
