<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class PusatAdabAuthController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Pusat Adab credentials'
            ]);
        }

        return response()->json([
            'success' => true,
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'Pusat Adab'
        ]);
    }
}