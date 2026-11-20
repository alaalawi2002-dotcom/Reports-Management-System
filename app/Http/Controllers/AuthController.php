<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * تسجيل حساب جديد
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:6|confirmed',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'user',
        ]);

        $deviceName = $validated['device_name'] ?? 'unknown-device';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'تم إنشاء الحساب بنجاح',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    /**
     * تسجيل الدخول
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'
            ], 401);
        }

        $user = Auth::user();
        $deviceName = $validated['device_name'] ?? 'unknown-device';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    /**
     * تسجيل الخروج
     */
   public function logout(Request $request)
{
    $user = $request->user();

    $token = $user->currentAccessToken();

    if (!$token) {
        return response()->json([
            'message' => 'لا يوجد توكن صالح لتسجيل الخروج'
        ], 401);
    }

    $token->delete();

    return response()->json([
        'message' => 'تم تسجيل الخروج بنجاح'
    ], 200);
}
}