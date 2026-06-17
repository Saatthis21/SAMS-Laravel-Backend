<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cocurriculum;

class CocurriculumController extends Controller
{
    // Get all available co-curriculum subjects
    public function getAvailableSubjects()
    {
        $subjects = [
            ['subject_code' => 'SAMS-CO-001', 'subject_name' => 'Uniform Unit (SUKSIS)', 'credits' => 2, 'slots' => 30],
            ['subject_code' => 'SAMS-CO-002', 'subject_name' => 'Sports Club (Badminton)', 'credits' => 2, 'slots' => 30],
            ['subject_code' => 'SAMS-CO-003', 'subject_name' => 'Community Service', 'credits' => 2, 'slots' => 30],
            ['subject_code' => 'SAMS-CO-004', 'subject_name' => 'Entrepreneurship Club', 'credits' => 2, 'slots' => 30],
            ['subject_code' => 'SAMS-CO-005', 'subject_name' => 'Red Crescent Society', 'credits' => 2, 'slots' => 30],
            ['subject_code' => 'SAMS-CO-006', 'subject_name' => 'Language & Cultural Club', 'credits' => 2, 'slots' => 30],
        ];

        return response()->json($subjects);
    }

    // Get all co-curriculum subjects for a student
    public function getByStudent($studentID)
    {
        $data = Cocurriculum::where('studentID', $studentID)->get();
        return response()->json($data);
    }

    // Student registers for a co-curriculum subject
    public function register(Request $request)
    {
        $existing = Cocurriculum::where('studentID', $request->studentID)
            ->where('subject_code', $request->subject_code)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Already registered for this subject'
            ]);
        }

        $record = Cocurriculum::create([
            'studentID'      => $request->studentID,
            'subject_code'   => $request->subject_code,
            'subject_name'   => $request->subject_name,
            'hours_recorded' => 0,
            'hours_required' => 40,
            'credits'        => 2,
            'status'         => 'In Progress',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully registered!',
            'data'    => $record
        ]);
    }

    // Student claims credit
    public function claimCredit($id)
    {
        $record = Cocurriculum::find($id);

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found'
            ], 404);
        }

        if ($record->hours_recorded < $record->hours_required) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient hours. Need ' . $record->hours_required . ' hours.'
            ], 400);
        }

        if ($record->status !== 'In Progress') {
            return response()->json([
                'success' => false,
                'message' => 'Already claimed or pending review.'
            ], 400);
        }

        $record->status = 'Pending Review';
        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Credit claim submitted to Pusat Adab!'
        ]);
    }

    // Pusat Adab approves claim
    public function approveCredit($id)
    {
        $record = Cocurriculum::find($id);

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found'
            ], 404);
        }

        $record->status = 'Credit Awarded';
        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Credit awarded!'
        ]);
    }

    // Pusat Adab rejects claim
    public function rejectCredit(Request $request, $id)
    {
        $record = Cocurriculum::find($id);

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found'
            ], 404);
        }

        $record->status = 'Rejected';
        $record->rejection_reason = $request->reason;
        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Claim rejected.'
        ]);
    }

    // Get all pending claims for Pusat Adab staff
    public function getPendingClaims()
    {
        $data = Cocurriculum::where('status', 'Pending Review')->get();
        return response()->json($data);
    }
}