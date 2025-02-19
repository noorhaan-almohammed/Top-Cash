<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function showUserCoins(){
        $user = Auth::user();
        $coins = $user->account_balance;
        return response()->json($coins);
    }
}
