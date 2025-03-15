<?php

namespace App\Http\Controllers;

use App\Models\SiteConfig;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WithdrawMethod;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WithdrawalController extends Controller
{
    public function withdraw(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'method_id' => 'required|exists:withdraw_methods,id',
            'info' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'message' => $validator->errors()->first()
            ]);
        }

        $method = WithdrawMethod::find($request->method_id);
        $coins_rate = SiteConfig::where('config_name', 'coins_rate')->value('config_value');
        $coins_required = $method->minimum * $coins_rate;

        if ($user->account_balance < $coins_required) {
            return response()->json(['status' => 500, 'message' =>  __('messages.notEnoughCoins')]);
        }

        $user = User::findOrFail($user->id);
        $user->decrement('account_balance', $coins_required);

        Withdrawal::create([
            'user_id' => $user->id,
            'coins' => $coins_required,
            'amount' => $method->minimum,
            'method_id' => $method->id,
            'method_name' => $method->method,
            'payment_info' => $request->info,
            'ip_address' => $request->ip(),
            'time' => time(),
        ]);

        return response()->json(['status' => 200, 'message' => __('messages.successfullyWithdraw')]);
    }

    public function index(Request $request){
        $userId = Auth::id();
        $withdraw = Withdrawal::where('user_id', $userId)
                                ->status($request->input('status'))
                                ->get();
        return response()->json($withdraw);
    }
}
