<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if (config('app.debug')) {
            header('HTTP/1.1 200 OK', true, 200);
            header('Content-Type: text/plain; charset=utf-8');
            echo "LARAVEL RAW EXCEPTION DEBUG:\n";
            echo "Type: " . get_class($e) . "\n";
            echo "Message: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
            echo "Trace:\n" . $e->getTraceAsString();
            exit;
        }

        return parent::render($request, $e);
    }
}
