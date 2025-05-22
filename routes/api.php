<?php

use Illuminate\Support\Facades\Route;

// Exemplo de rota de API
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});
