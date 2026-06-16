<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cocurriculum;

class CocurriculumController extends Controller
{
    // Get all co-curriculum subjects for a student
    public function getByStudent($studentID)
    {
        $data = Cocurriculum::where('studentID', $studentID)->get();
        return response()->json($data);
    }

    // Student claims credit
    public function claimCredit($id)
    {
        $record = Cocurriculum::find($id);

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Record not found'], 404);
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

        return response()->json(['success' => true, 'message' => 'Credit claim submitted to Pusat Adab!']);
    }

    // Pusat Adab approves claim
    public function approveCredit($id)
    {
        $record = Cocurriculum::find($id);
        $record->status = 'Credit Awarded';
        $record->save();

        return response()->json(['success' => true, 'message' => 'Credit awarded!']);
    }

    // Pusat Adab rejects claim
    public function rejectCredit(Request $request, $id)
    {
        $record = Cocurriculum::find($id);
        $record->status = 'Rejected';
        $record->rejection_reason = $request->reason;
        $record->save();

        return response()->json(['success' => true, 'message' => 'Claim rejected.']);
    }

    // Get all pending claims (for Pusat Adab staff)
    public function getPendingClaims()
    {
        $data = Cocurriculum::where('status', 'Pending Review')->get();
        return response()->json($data);
    }
}