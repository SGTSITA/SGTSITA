<?php

namespace App\Helpers;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Models\ErrorLog;

class LogHelper
{
    public static function logError($exception)
    {
        ErrorLog::create([
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'user_id' => Auth::id(),
            'ip' => Request::ip(),
            'request' => json_encode(Request::all()),
        ]);
    }
}
