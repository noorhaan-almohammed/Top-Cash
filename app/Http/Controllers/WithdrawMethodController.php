<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WithdrawMethod;

class WithdrawMethodController extends Controller
{
    public function get_all_methods()
    {
        $methods = WithdrawMethod::select('method', 'icon_url')->distinct()->get()->makeHidden(['required_coins']);
        return response()->json($methods);
    }
    
    public function index(Request $request)
    {
        $method = $request->input('method');
        $methods = WithdrawMethod::select(['id', 'method', 'info', 'minimum'])
                                ->when($method, fn($q) => $q->where('method', $method))
                                ->get();
     return response()->json($methods);
    }

}
