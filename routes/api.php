<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HealthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Health check endpoint para monitoramento
Route::get('/health', [HealthController::class, 'index']);

// Rota de teste ping-pong
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::get('/test', function () {
    return response()->json(['status' => 'API is working!']);
});

// Exemplo de rota com middleware auth (comentado)
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
