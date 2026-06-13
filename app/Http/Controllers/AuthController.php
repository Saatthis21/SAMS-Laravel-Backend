<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // A temporary shortcut to create your account!
    public function setupMyAccount()
    {
        Student::firstOrCreate(
            ['studentID' => 'CB23019'],
            [
                'student_name' => 'SAATHISH A/L SRIDAR',
                'student_year' => 3, 
                'student_course' => 'SOFTWARE ENGINEERING',
                'password' => Hash::make('password123') // This encrypts your password!
            ]
        );
        return "Account created! You can login with password123";
    }

    // The actual login logic for Flutter
    public function login(Request $request)
    {
        // 1. Find the student in the dummy database
        $student = Student::where('studentID', $request->student_id)->first();

        // 2. Check if they exist and if the password matches
        if (!$student || $student->password !== $request->password)  {
            return response()->json([
                'success' => false, 
                'message' => 'Wrong ID or Password!'
            ]);
        }

        // 3. NO TOKENS! Just return "success: true" and the profile data
        return response()->json([
            'success' => true,
            'student_id' => $student->studentID,
            'student_name' => $student->student_name,
            'student_course' => $student->student_course,
            'student_year' => $student->student_year
        ]);
    }
}