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
            'amount' => 'required|numeric|min:0',
            'method' => 'required|exists:withdraw_methods,id',
            'info' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'message' => $validator->errors()->first()
            ]);
        }

        $method = WithdrawMethod::find($request->method);
        $coins_rate = SiteConfig::where('config_name', 'coins_rate')->value('config_value');
        $coinsValue = number_format($request->amount / $coins_rate, 2, '.', '');

        if ($user->account_balance < $request->amount) {
            return response()->json(['status' => 500, 'message' =>  __('messages.notEnoughCoins')]);
        }
        if ($coinsValue < $method->minimum) {
            return response()->json(['status' => 500, 'message' => __('messages.minimumDollar1') . $method->minimum . __('messages.minimumDollar2')]);
        }

        $user = User::findOrFail($user->id);
        $user->decrement('account_balance', $request->amount);

        Withdrawal::create([
            'user_id' => $user->id,
            'coins' => $request->amount,
            'amount' => $coinsValue,
            'method_id' => $method->id,
            'method_name' => $method->method,
            'payment_info' => $request->info,
            'ip_address' => $request->ip(),
            'time' => time(),
        ]);

        return response()->json(['status' => 200, 'message' => __('messages.successfullyWithdraw')]);
    }

    public function index(){
        $userId = Auth::id();
        $withdraw = Withdrawal::where('user_id',$userId)->get();
        return response()->json($withdraw);
    }
}
