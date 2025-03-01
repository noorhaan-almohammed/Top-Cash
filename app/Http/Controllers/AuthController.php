<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\ConfigSite;
use Illuminate\Http\Request;
use App\Mail\PasswordRecoveryMail;

use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use function Laravel\Prompts\select;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:32',
            'email' => 'required|string|email|max:35|unique:users',
            'password' => 'required|string|min:6|max:20|confirmed',
            'bounce_code' => 'nullable|string|max:35',
            'gender' => 'nullable|integer'
        ]);
        $referrer = User::where('ref_code', $request->bounce_code)->value('id');

        $encryptedPassword = $this->encryptPassword($request->password);
        $refCode = $this->generateRefCode($request->email);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $encryptedPassword,
            'gender' => $request->gender ?? 0,
            'reg_ip' => $request->ip(),
            'log_ip' => $request->ip(),
            'ref_code' => $refCode,
            'ref' => $referrer ?? 0,
            'reg_time' => time(),
            'last_activity' => time(),
        ]);
        if ($user->ref > 0) {
            app(ActivityController::class)->addActivity($user->ref, 4, serialize(['id' => $user->id]));
        }
        $bounce_user_limit = ConfigSite::where('config_name','bounce_user_limit')->value('config_value');
        $bounce_code = ConfigSite::where('config_name','bounce_code')->value('config_value');
        $bounce_amount = ConfigSite::where('config_name','bounce_amount')->value('config_value');
        $usedCount = User::where('bounce_code_used',$bounce_code)->count();

        $user = User::findOrFail($user->id);

        $message = "";
        if($request->has('bounce_code') && $request->bounce_code == $bounce_code && $usedCount < $bounce_user_limit){
           $user->update(['bounce_code_used'=> $request->bounce_code]);
           $user->increment('account_balance', $bounce_amount);
           $message = __('messages.bounce_code');
        }elseif($user->ref == 0){
            $message = __('messages.expaired_code');
        }
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'message' => __('messages.register_success'),
            'code_bounse_msg' => $message,
            'user' => $user,
            'token' => $token
        ], 201);
    }
    public function generateRefCode($email)
    {
        $hash = hash('sha256', $email . time());
        return substr($hash, 0, 18);
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
            return response()->json(['message' => __('messages.login_failed')], 401);
        }

        if ($user->disabled) {
            return response()->json(['message' => __('messages.account_disabled')], 403);
        }

        RateLimiter::clear($key);

        $user->update([
            'last_activity' => time(),
            'log_ip' => $request->ip()
        ]);

        DB::table('user_logins')->insert([
            'uid' => $user->id,
            'ip' => $request->ip(),
            'info' => $request->header('User-Agent'),
            'time' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => __('messages.login_success'),
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken()); // حذف التوكن

        return response()->json(['message' => __('messages.logout_success')]);
    }

    public function refresh()
    {
        $newToken = JWTAuth::refresh(JWTAuth::getToken()); // تحديث التوكن

        return response()->json([
            'message' => __('messages.token-refreshed'),
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
            'username' => 'sometimes|string|max:32',
            'email' => 'sometimes|string|email|max:128|unique:users,email,' . $user->id,
            'gender' => 'nullable|integer'
        ]);

        $newUser = array_filter([
            'username' => $request->username ?? $user->username,
            'email' => $request->email && $request->email !== $user->email ? $request->email : $user->email,
            'gender' => $request->gender ?? $user->gender,
        ], fn($value) => $value !== null);
        $user->update($newUser);

        return response()->json(['message' => __('messages.user-update'), 'user' => $user]);
    }

    private function encryptPassword($password)
    {
        return md5(md5(sha1($password) . sha1(md5($password))));
    }

    public function forgetPasswword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:128|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'message' => Lang::get($validator->errors()->first())
            ]);
        }

        $email = $request->input('email');
        $recUser = User::where('email' , $email)->first();
        $newhash = bin2hex(random_bytes(16));
        $recover_url = url('/api/reset_password?newhash=' . $newhash);

        DB::table('users_recovery')->updateOrInsert(
            ['user_id' => $recUser->id],
            ['hash_key' => $newhash, 'time' => time()]
        );

        Mail::to($email)->send(new PasswordRecoveryMail($recUser, $recover_url));

        return response()->json([
            'status' => 200,
            'msg' => __('messages.send_email_forget_passwird')
        ]);
    }
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'newhash'   => 'required|string|exists:users_recovery,hash_key',
            'password'  => 'required|string|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'message' => $validator->errors()->first()
            ]);
        }

        $recoveryData = DB::table('users_recovery')->where('hash_key', $request->newhash)->first();

        $createdAt = Carbon::createFromTimestamp((int) $recoveryData->time);

        if ($createdAt->diffInMinutes(Carbon::now()) > 30) {
            return response()->json([
                'status' => 403,
                'message' => __('messages.link_expired')
            ]);
        }

        $user = User::find($recoveryData->user_id);
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found!'
            ]);
        }

        $user->password = $this->encryptPassword($request->password);
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Your password has been successfully reset. You can now log in with your new password.'
        ]);
    }
}
