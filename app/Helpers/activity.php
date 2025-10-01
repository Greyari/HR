<?php

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

if (!function_exists('activity_log')) {
    function activity_log($action, $module = null, $description = null, $userId = null) {
        ActivityLog::create([
            'user_id'    => $userId ?? Auth::id(),
            'action'     => $action,
            'module'     => $module,
            'description'=> $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}
