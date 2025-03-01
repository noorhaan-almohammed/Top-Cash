<?php
namespace App\Http\Controllers;

use App\Jobs\sendSupportMail;
use App\Mail\SupportMail;
use App\Models\UserSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserSupportController extends Controller
{
    public function createSupportMail(Request $request)
    {
        $user = Auth::user();
        if (!$user || empty($user->email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access or invalid email.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:10',
            'question' => 'required|string|min:5|max:4000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        UserSupport::create([
            'phone' => $request->phone,
            'email' => $user->email,
            'question' => $request->question,
        ]);

        $details = [
            'user_name' => $user->username,
            'user_email' => $user->email,
            'phone' => $request->phone,
            'question' => $request->question,
        ];

        try {
            Mail::to('support@foancash.com')->send(new SupportMail($details));

            return response()->json([
                'status' => 'success',
                'message' => 'Your support request has been sent successfully!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send support request. ' . $e->getMessage(),
            ], 500);
        }
    }
        // public function sendMail(Request $request)
        // {
        //     $email = $request->input('email');
        //     $message = $request->input('message');
        //     $subject = $request->input('subject');

            // Mail::raw($message, function ($message) use ($email, $subject) {
            //     $message->from('support@foancash.com', env('APP_NAME', 'foancash'));
            //     $message->subject($subject?? 'Your Subject Here');
            //     $message->to($email);
            // });
        // }

}
