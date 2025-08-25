<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

Route::middleware('auth:api')->group(function () {
    // Auth routes that need authentication
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::apiResource('tasks', TaskController::class);

    // Profile routes
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::put('profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('profile/image', [ProfileController::class, 'updateImage']);
});

// Broadcasting authentication - Custom endpoint
Route::post('/broadcasting/auth', function (Illuminate\Http\Request $request) {
    $user = auth('api')->user();

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Extract channel name from request
    $channelName = $request->input('channel_name');

    // Check if user can access this channel
    if (str_starts_with($channelName, 'private-tasks.') &&
        str_ends_with($channelName, '.' . $user->id)) {

        // Generate auth signature for Pusher
        $socketId = $request->input('socket_id');
        $pusherKey = config('broadcasting.connections.pusher.key');
        $pusherSecret = config('broadcasting.connections.pusher.secret');

        $stringToSign = $socketId . ':' . $channelName;
        $signature = hash_hmac('sha256', $stringToSign, $pusherSecret);
        $auth = $pusherKey . ':' . $signature;

        return response()->json([
            'auth' => $auth
        ]);
    }

    return response()->json(['error' => 'Forbidden'], 403);
})->middleware('auth:api');
Route::get('/auth/test', function () {
    return response()->json(['message' => 'API works!']);
});

Route::post('/auth/test-register', function (Illuminate\Http\Request $request) {
    \Log::info('Test registration endpoint hit', ['data' => $request->all()]);
    return response()->json([
        'message' => 'Test endpoint reached',
        'data' => $request->all()
    ]);
});