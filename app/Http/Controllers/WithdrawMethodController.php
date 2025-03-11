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

    // public function index(Request $request){
    //     $method = $request->query('method');
    //     $methods = WithdrawMethod::when($method, fn($q)=>$q->where('method',$method))->get();
    //     return response()->json($methods);
    // }
}
