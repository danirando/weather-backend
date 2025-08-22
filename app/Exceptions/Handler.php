<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    // altri metodi/proprietà...

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
                return response()->json(['message' => 'Risorsa non trovata'], 404);
            }

            // In produzione non mostrare dettagli
            if (app()->environment('production')) {
                return response()->json(['message' => 'Errore interno del server'], 500);
            }
        }

        return parent::render($request, $e);
    }
}
