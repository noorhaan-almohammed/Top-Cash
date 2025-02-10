<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:32|unique:users',
            'email' => 'required|string|email|max:128|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $encryptedPassword = $this->encryptPassword($request->password);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $encryptedPassword,
            'reg_time' => time(),
            'last_activity' => time(),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $key = 'login_attempts_' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'تم تجاوز الحد الأقصى للمحاولات، يرجى المحاولة لاحقاً'], 429);
        }

        $encryptedPassword = $this->encryptPassword($request->password);
        $user = User::where('email', $request->email)->where('password', $encryptedPassword)->first();

        if (!$user) {
            RateLimiter::hit($key, 60);
            return response()->json(['message' => 'بيانات تسجيل الدخول غير صحيحة'], 401);
        }

        if ($user->disabled) {
            return response()->json(['message' => 'تم تعطيل هذا الحساب، يرجى التواصل مع الدعم'], 403);
        }

        RateLimiter::clear($key);

        $user->update([
            'last_activity' => time(),
            'log_ip' => $request->ip()
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken()); // حذف التوكن

        return response()->json(['message' => 'Logout successful']);
    }

    public function refresh()
    {
        $newToken = JWTAuth::refresh(JWTAuth::getToken()); // تحديث التوكن

        return response()->json([
            'message' => 'Token refreshed',
            'token' => $newToken
        ]);
    }

    public function getProfile()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return response()->json($user);
    }

    public function updateProfile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $request->validate([
            'username' => 'sometimes|string|max:32|unique:users,username,' . $user->id,
            'email' => 'sometimes|string|email|max:128|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only(['username', 'email']));

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    private function encryptPassword($password)
    {
        return md5(md5(sha1($password) . sha1(md5($password))));
    }
}
