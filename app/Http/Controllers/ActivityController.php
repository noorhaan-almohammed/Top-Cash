<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function addActivity($userId, $notifyId, $value = null)
    {
        if (!empty($userId) && !empty($notifyId)) {
            Activity::create([
                'user_id' => $userId,
                'notify_id' => $notifyId,
                'value' => $value ?? null,
            ]);
        }
    }

    public function getUserActivities($userId)
    {
        $activities = Activity::where('user_id', $userId)
                              ->get();

        return response()->json($activities);
    }
}
