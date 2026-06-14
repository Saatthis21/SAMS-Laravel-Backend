<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TuitionFee;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentTransaction;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Get fee summary details and payment status for a specific student.
     * Maps to SAMS-REQ-401 and SAMS-REQ-402.
     */
    public function getFeeSummary($student_id)
    {
        // Query the database for the student's fee record based on the student_id
        $feeRecord = TuitionFee::where('student_id', $student_id)->first();

        // If no record exists for this student, return a 404 error response
        if (!$feeRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Fee record not found for student ID: ' . $student_id
            ], 404);
        }

        // Return the clean structural data back to the Flutter UI application
        return response()->json([
            'success' => true,
            'data' => [
                'fees_id'       => $feeRecord->fees_id,
                'student_id'    => $feeRecord->student_id,
                'program'       => $feeRecord->program,
                'semester'      => $feeRecord->semester,
                'total_fee'     => (float) $feeRecord->total_fee,
                'paid_amount'   => (float) $feeRecord->paid_amount,
                'balance'       => (float) $feeRecord->balance,
                'status'        => $feeRecord->status, // paid, unpaid, or partial
                'deadline_week' => (int) $feeRecord->deadline_week,
            ]
        ], 200);
    }

    /**
 * Process a student's online fee payment.
 * Enforces SAMS-REQ-404, SAMS-REQ-405, and SAMS-REQ-417.
 */
public function initiatePayment(Request $request)
{
    // 1. Validate payment details input fields on submission
    $validator = Validator::make($request->all(), [
        'student_id'      => 'required|string|exists:students,studentID',
        'fee_id'          => 'required|string|exists:fees,fees_id',
        'amount'          => 'required|numeric|min:1',
        'payment_method'  => 'required|string',
        'transaction_ref' => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    // 2. Defensive Rule: Prevent duplicate payments by checking transaction_ref
    $duplicateCheck = PaymentTransaction::where('transaction_ref', $request->transaction_ref)->first();
    if ($duplicateCheck) {
        return response()->json([
            'success' => false,
            'message' => 'Transaction reference already exists. Duplicate payment blocked.'
        ], 400);
    }

    // 3. Find the student's target fee record
    $fee = TuitionFee::where('fees_id', $request->fee_id)->first();

    // Verify they aren't paying more than what is left owing
    if ($request->amount > $fee->balance) {
        return response()->json([
            'success' => false,
            'message' => 'Payment amount exceeds outstanding balance of RM ' . $fee->balance
        ], 400);
    }

    // 4. Generate unique ID and save transaction record to payments table
    $paymentId = 'PAY-' . str_replace('.', '', microtime(true));
    $payment = PaymentTransaction::create([
        'payment_id'      => $paymentId,
        'fee_id'          => $request->fee_id,
        'student_id'      => $request->student_id,
        'amount'          => $request->amount,
        'payment_method'  => $request->payment_method,
        'status'          => 'success', // Simulating successful gateway completion response
        'transaction_ref' => $request->transaction_ref,
        'paid_at'         => Carbon::now()
    ]);

    // 5. Compute new overall balance and status values
    $newPaidAmount = $fee->paid_amount + $request->amount;
    $newBalance    = $fee->total_fee - $newPaidAmount;
    $newStatus     = ($newBalance <= 0) ? 'paid' : 'partial';

    // Update parent fee summary table reactively
    $fee->update([
        'paid_amount' => $newPaidAmount,
        'balance'     => $newBalance,
        'status'      => $newStatus
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Payment processed successfully!',
        'payment_details' => $payment
    ], 200);
}

/**
 * Retrieve all past payment transaction records for a specific student.
 * Maps to SAMS-REQ-403.
 */
public function getPaymentHistory($student_id)
{
    // Fetch all transaction entries for the student, sorted by newest first
    $history = PaymentTransaction::where('student_id', $student_id)
        ->orderBy('created_at', 'desc')
        ->get();

    // Even if no payments exist yet, return an empty array with a 200 status
    return response()->json([
        'success' => true,
        'count'   => $history->count(),
        'data'    => $history
    ], 200);
}

/**
 * Evaluate if a student is financially barred from academic activities.
 * Enforces SAMS-REQ-410 and SAMS-REQ-411.
 */
public function getBlockStatus($student_id)
{
    // Find the student's current tuition fee records
    $feeRecord = TuitionFee::where('student_id', $student_id)->first();

    if (!$feeRecord) {
        return response()->json([
            'success' => false,
            'message' => 'No active fee statement found for the requested profile.'
        ], 404);
    }

    // To test this dynamically before your team builds an academic calendar table,
    // we will capture the current week from a URL parameter, defaulting to Week 6 (past deadline).
    $currentWeek = request()->query('current_week', 6); 

    $isBlocked = false;
    $statusReason = 'Account active. No restrictions applied.';

    // Core rule: If balance remains and current timeline matches or exceeds Week 5
    if ($feeRecord->balance > 0 && $currentWeek >= $feeRecord->deadline_week) {
        $isBlocked = true;
        $statusReason = 'Academic access suspended. Outstanding balance of RM ' . $feeRecord->balance . ' must be cleared.';
    }

    return response()->json([
        'success' => true,
        'data' => [
            'student_id'     => $feeRecord->student_id,
            'outstanding_bal'=> (float) $feeRecord->balance,
            'deadline_week'  => (int) $feeRecord->deadline_week,
            'current_week'   => (int) $currentWeek,
            'is_blocked'     => $isBlocked,
            'reason'         => $statusReason
        ]
    ], 200);
}

/**
 * Create a new tuition fee structure for a student.
 * Maps to SAMS-REQ-407.
 */
public function setFee(Request $request)
{
    // 1. Validate administrative inputs
    $validator = Validator::make($request->all(), [
        'fees_id'       => 'required|string|unique:fees,fees_id',
        'student_id'    => 'required|string|exists:students,studentID',
        'program'       => 'required|string',
        'semester'      => 'required|string',
        'total_fee'     => 'required|numeric|min:0',
        'deadline_week' => 'required|integer|min:1|max:14'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    // 2. Automatically compute initial balance fields
    $fee = TuitionFee::create([
        'fees_id'       => $request->fees_id,
        'student_id'    => $request->student_id,
        'program'       => $request->program,
        'semester'      => $request->semester,
        'total_fee'     => $request->total_fee,
        'paid_amount'   => 0.00, // Initialized to 0
        'balance'       => $request->total_fee, // Balance equals total initially
        'status'        => 'unpaid',
        'deadline_week' => $request->deadline_week
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Tuition fee assigned successfully!',
        'data'    => $fee
    ], 201);
}

/**
 * Update an existing tuition fee record.
 * Maps to SAMS-REQ-408.
 */
public function updateFee(Request $request, $id)
{
    // 1. Check if the targeted invoice exists
    $fee = TuitionFee::find($id);
    if (!$fee) {
        return response()->json([
            'success' => false,
            'message' => 'Fee record not found.'
        ], 404);
    }

    // 2. Validate modification parameters
    $validator = Validator::make($request->all(), [
        'total_fee'     => 'numeric|min:0',
        'deadline_week' => 'integer|min:1|max:14'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    // 3. Update fields dynamically if present in request
    if ($request->has('total_fee')) {
        $fee->total_fee = $request->total_fee;
        // Recalculate remaining balance reactively
        $fee->balance = $fee->total_fee - $fee->paid_amount;
        
        // Recalculate status indicator dynamically
        if ($fee->balance <= 0) {
            $fee->status = 'paid';
        } elseif ($fee->paid_amount > 0) {
            $fee->status = 'partial';
        } else {
            $fee->status = 'unpaid';
        }
    }

    if ($request->has('deadline_week')) {
        $fee->deadline_week = $request->deadline_week;
    }

    $fee->save();

    return response()->json([
        'success' => true,
        'message' => 'Fee parameters updated successfully.',
        'data'    => $fee
    ], 200);
}

}