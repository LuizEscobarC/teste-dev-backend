<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReport([
            // 
        ]);
        
        $exceptions->renderable(function (Throwable $e, $request) {
            if ($e->getPrevious() instanceof ValidationException) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'errors' => $e->errors(),
                    ], $e->status);
                }
                return null;
            }

            if ($e->getPrevious() instanceof ModelNotFoundException) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => 'Registro não encontrado.',
                        'error' => 'not_found'
                    ], 404);
                }
                return null;
            }
            if ($e->getPrevious() instanceof \Illuminate\Database\QueryException) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => 'Erro de consulta ao banco de dados.',
                        'error' => 'query_exception'
                    ], 500);
                }
                return null;
            }

            if ($e->getPrevious() instanceof TypeError) {
                if (($request->expectsJson() || $request->is('api/*')) && 
                    str_contains($e->getMessage(), 'must be of type') && 
                    str_contains($e->getMessage(), 'none returned')) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'error' => $e->getTraceAsString(),
                    ], 404);
                }
                return null;
            }

            return null;
        });

    })->create();
