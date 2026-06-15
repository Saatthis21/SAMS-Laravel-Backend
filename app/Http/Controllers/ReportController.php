<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentAttendance;
// use App\Models\RegistrationSubmission; // Teammate's models
// use App\Models\TuitionFee;
// use App\Models\CreditClaim;

class ReportController extends Controller
{
    /**
     * Requirement: SAMS-REQ-502 to SAMS-REQ-508
     * Centralized report generator based on type and filters.
     */
    public function generateReport(Request $request)
    {
        // 1. VALIDATE INPUTS (SAMS-REQ-507)
        $validated = $request->validate([
            'report_type' => 'required|string|in:attendance,registration,fee,cocurriculum',
            'course_code' => 'nullable|string',
            'semester' => 'nullable|string',
            'student_id' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $data = [];
            $summary = [];

            // 2. ROUTE TO CORRECT QUERY BASED ON REPORT TYPE
            switch ($validated['report_type']) {

                case 'attendance': // SAMS-REQ-505
                    $query = StudentAttendance::with('session', 'student');

                    if (!empty($validated['student_id'])) {
                        $query->where('studentID', $validated['student_id']);
                    }
                    if (!empty($validated['start_date']) && !empty($validated['end_date'])) {
                        $query->whereBetween('created_at', [$validated['start_date'], $validated['end_date']]);
                    }

                    $data = $query->get()->map(function ($record) {
                        return [
                            'Date' => $record->created_at->format('Y-m-d'),
                            'Student ID' => $record->studentID,
                            'Session Code' => $record->submitted_code,
                            'Status' => $record->gps_verified ? 'Present' : 'Absent',
                        ];
                    });

                    $summary = [
                        'Total Records' => $data->count(),
                        'Total Present' => $data->where('Status', 'Present')->count(),
                        'Total Absent' => $data->where('Status', 'Absent')->count(),
                    ];
                    break;

                case 'registration': // SAMS-REQ-506
                    // TODO: Saathish will add Registration logic here
                    break;

                case 'fee': // SAMS-REQ-507
                    // TODO: Jaclina will add Fee logic here
                    break;

                case 'cocurriculum': // SAMS-REQ-508
                    // TODO: Nur Aida will add Co-curriculum logic here
                    break;
            }

            // 3. HANDLE NO DATA FOUND (SAMS-REQ-514)
            if (empty($data) || $data->count() === 0) {
                return response()->json([
                    'status' => 'empty',
                    'message' => 'No Data Available for the selected criteria.'
                ], 404);
            }

            // 4. RETURN SUCCESS WITH DATA AND SUMMARY STATS (SAMS-REQ-509, 510)
            return response()->json([
                'status' => 'success',
                'summary' => $summary,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'REPORT GENERATION FAILED: ' . $e->getMessage()
            ], 500);
        }
    }
}