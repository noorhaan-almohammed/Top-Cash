<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompletedOffer;
use Illuminate\Support\Facades\Auth;

class CompletedOfferController extends Controller
{
    public function index(Request $request){
        $userId = Auth::id();

        $offers = CompletedOffer::where('user_id', $userId)
                                ->status($request->input('status'))
                                ->get();

        return response()->json($offers);
    }
}
