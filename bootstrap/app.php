<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Meta posts to the WhatsApp webhook with no session and no CSRF
        // token. It is authenticated instead by the X-Hub-Signature-256 HMAC
        // over the raw body, which WhatsAppWebhookController checks on every
        // request before it reads anything.
        $middleware->validateCsrfTokens(except: [
            'webhooks/whatsapp',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
