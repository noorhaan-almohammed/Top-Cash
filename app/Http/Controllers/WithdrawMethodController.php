<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WithdrawMethod;

class WithdrawMethodController extends Controller
{
    public function index(){
        $methods = WithdrawMethod::get();
        return response()->json($methods);
    }
}
