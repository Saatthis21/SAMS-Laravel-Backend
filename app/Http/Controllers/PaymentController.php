<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TuitionFee;
use App\Models\PaymentTransaction;
use App\Models\Receipt;
use App\Models\Student;
use App\Models\FeeStructure;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentController extends Controller
{

    //==================================================
    // GET FEE SUMMARY
    // GET /api/fee/summary/{student_id}
    //==================================================

    public function getFeeSummary($student_id)
    {
        $fee = TuitionFee::where(
            'student_id',
            $student_id
        )->first();

        if (!$fee) {

            return response()->json([
                'success' => false,
                'message' => 'Fee record not found.'
            ], 404);

        }

        return response()->json([
            'success' => true,
            'data' => $fee
        ]);
    }

    //==================================================
    // INITIATE PAYMENT
    // POST /api/payment/initiate
    //==================================================

    public function initiatePayment(Request $request)
    {

        $request->validate([

            'student_id' => 'required',

            'fee_id' => 'required',

            'amount' => 'required|numeric|min:1',

            'payment_method' =>
                'required|in:Online Banking,Debit Card,Credit Card'

        ]);


        $fee = TuitionFee::where(
            'fees_id',
            $request->fee_id
        )->first();


        if (!$fee) {

            return response()->json([

                'success' => false,

                'message' => 'Fee record not found.'

            ], 404);

        }


        if ($fee->status == "PAID") {

            return response()->json([

                'success' => false,

                'message' => 'Fee already settled.'

            ], 400);

        }


        if ($request->amount > $fee->balance) {

            return response()->json([

                'success' => false,

                'message' => 'Payment exceeds outstanding balance.'

            ], 400);

        }


        $payment = PaymentTransaction::create([

            'payment_id' => Str::upper(Str::random(20)),

            'fee_id' => $fee->fees_id,

            'student_id' => $request->student_id,

            'amount' => $request->amount,

            'payment_method' => $request->payment_method,

            'status' => 'SUCCESS',

            'transaction_ref' => Str::upper(Str::random(12)),

            'failure_reason' => null,

            'paid_at' => Carbon::now()

        ]);


        $fee->paid_amount =
            $fee->paid_amount + $request->amount;

        $fee->balance =
            $fee->total_fee - $fee->paid_amount;


        if ($fee->balance <= 0) {

            $fee->status = "PAID";

        } elseif ($fee->paid_amount > 0) {

            $fee->status = "PARTIAL";

        } else {

            $fee->status = "UNPAID";

        }

        $fee->save();


        $receipt = Receipt::create([

            'receipt_id' => Str::upper(Str::random(15)),

            'receipt_number' => 'RCT' . date('YmdHis'),

            'payment_id' => $payment->payment_id,

            'student_id' => $request->student_id,

            'amount_paid' => $request->amount,

            'balance' => $fee->balance,

            'payment_method' => $request->payment_method,

            'note' => 'Official Tuition Fee Receipt',

            'paid_at' => Carbon::now()

        ]);


        $payment->receipt_id = $receipt->receipt_id;
        $payment->save();


        return response()->json([

            'success' => true,

            'message' => 'Payment Successful',

            'payment' => $payment,

            'receipt' => $receipt

        ]);

    }

    //==================================================
    // PAYMENT HISTORY
    // GET /api/payment/history/{student_id}
    //==================================================

    public function getPaymentHistory($student_id)
    {

        $history = PaymentTransaction::where(
            'student_id',
            $student_id
        )
        ->orderBy('paid_at', 'desc')
        ->get();

        return response()->json([

            'success' => true,

            'data' => $history

        ]);

    }


    //==================================================
    // GET RECEIPT
    // GET /api/receipt/{receipt_id}
    //==================================================

    public function getReceipt($receipt_id)
    {

        $receipt = Receipt::where(
            'receipt_id',
            $receipt_id
        )->first();

        if (!$receipt) {

            return response()->json([

                'success' => false,

                'message' => 'Receipt not found.'

            ], 404);

        }

        return response()->json([

            'success' => true,

            'data' => $receipt

        ]);

    }

    //==================================================
    // BLOCK STATUS
    // GET /api/block/status/{student_id}
    //==================================================

    public function getBlockStatus($student_id)
    {

        $fee = TuitionFee::where(
            'student_id',
            $student_id
        )->first();

        if (!$fee) {

            return response()->json([

                'success' => false,

                'message' => 'Fee record not found.'

            ], 404);

        }

        $blocked = false;

        if (
            $fee->balance > 0 &&
            $fee->deadline_week <= 5
        ) {

            $blocked = true;

        }

        return response()->json([

            'success' => true,

            'student_id' => $student_id,

            'blocked' => $blocked,

            'balance' => $fee->balance,

            'status' => $fee->status

        ]);

    }

    //==================================================
    // TREASURY - SET FEE STRUCTURE
    // POST /api/fee/set
    //==================================================
public function setFee(Request $request)
{
    $request->validate([

        'program' => 'required|string',

        'semester' => 'required|string',

        'fee_amount' => 'required|numeric|min:0',

        'deadline_week' => 'required|integer|min:1|max:14'

    ]);

    $fee = FeeStructure::create([

        'program' => $request->program,

        'semester' => $request->semester,

        'fee_amount' => $request->fee_amount,

        'deadline_week' => $request->deadline_week

    ]);

    return response()->json([

        'success' => true,

        'message' => 'Fee structure created successfully.',

        'data' => $fee

    ]);
}
    //==================================================
    // TREASURY - UPDATE FEE STRUCTURE
    // PUT /api/fee/update/{id}
    //==================================================

    public function updateFee(Request $request, $id)
    {

        $fee = FeeStructure::find($id);

        if (!$fee) {

            return response()->json([

                'success' => false,

                'message' => 'Fee structure not found.'

            ], 404);

        }


        $request->validate([

            'program' => 'nullable|string',

            'semester' => 'nullable|string',

            'fee_amount' => 'nullable|numeric|min:0',

            'deadline_week' => 'nullable|integer|min:1|max:14'

        ]);


        $fee->update([

            'program' => $request->program ?? $fee->program,

            'semester' => $request->semester ?? $fee->semester,

            'fee_amount' => $request->fee_amount ?? $fee->fee_amount,

            'deadline_week' => $request->deadline_week ?? $fee->deadline_week

        ]);


        return response()->json([

            'success' => true,

            'message' => 'Fee structure updated successfully.',

            'data' => $fee

        ]);

    }

    //==================================================
    // TREASURY - VIEW ALL PAYMENTS
    // GET /api/treasury/payments
    //==================================================

    public function getAllPayments()
    {

        $payments = PaymentTransaction::orderBy(
            'paid_at',
            'desc'
        )->get();

        return response()->json([

            'success' => true,

            'data' => $payments

        ]);

    }

    //==================================================
    // TREASURY - OUTSTANDING STUDENTS
    // GET /api/treasury/outstanding
    //==================================================

    public function getOutstandingStudents()
    {

        $students = TuitionFee::where(
            'balance',
            '>',
            0
        )
        ->orderBy(
            'balance',
            'desc'
        )
        ->get();

        return response()->json([

            'success' => true,

            'data' => $students

        ]);

    }

    //==================================================
    // TREASURY DASHBOARD
    // GET /api/treasury/dashboard
    //==================================================

    public function getDashboard()
    {

        $totalStudents = Student::count();

        $totalCollected = PaymentTransaction::where(
            'status',
            'SUCCESS'
        )->sum('amount');

        $totalOutstanding = TuitionFee::sum('balance');

        $blockedStudents = TuitionFee::where(
            'balance',
            '>',
            0
        )
        ->where(
            'deadline_week',
            '<=',
            5
        )
        ->count();


        return response()->json([

            'success' => true,

            'data' => [

                'total_students' => $totalStudents,

                'total_collected' => $totalCollected,

                'total_outstanding' => $totalOutstanding,

                'blocked_students' => $blockedStudents

            ]

        ]);

    }

//==================================================
// TREASURY - GET ALL FEE STRUCTURES
// GET /api/fee/list
//==================================================

public function getFeeList()
{
    $fees = FeeStructure::orderBy('program')->get();

    return response()->json([
        'success' => true,
        'data' => $fees
    ]);
}

}