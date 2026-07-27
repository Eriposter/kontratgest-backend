<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ─── Middleware Global ──────────────────────────────
        $middleware->trustProxies(at: '*');

        // ─── Aliases de Middleware ──────────────────────────
        $middleware->alias([
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // ─── Middleware por Grupo ───────────────────────────
        $middleware->group('api', [
            \App\Http\Middleware\ForceJsonResponse::class, // ← Adicionar aqui
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        // ─── Prioridade de Middleware ───────────────────────
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Contracts\Session\Middleware\AuthenticatesSessions::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);

        // ─── Sanitização de Input ───────────────────────────
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ─── Exceções de Autenticação ───────────────────────
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Não autenticado.',
                    'error' => 'unauthenticated',
                ], 401);
            }
        });

        // ─── Exceções de Autorização ────────────────────────
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Não autorizado a realizar esta ação.',
                    'error' => 'forbidden',
                ], 403);
            }
        });

        // ─── Exceções de Validação ──────────────────────────
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Os dados fornecidos são inválidos.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // ─── Model Not Found ────────────────────────────────
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Recurso não encontrado.',
                    'error' => 'not_found',
                ], 404);
            }
        });

        // ─── Exceção Genérica (produção) ────────────────────
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                return response()->json([
                    'message' => app()->isProduction()
                        ? 'Ocorreu um erro interno no servidor.'
                        : $e->getMessage(),
                    'error' => 'server_error',
                    'exception' => app()->isProduction() ? null : get_class($e),
                    'file' => app()->isProduction() ? null : $e->getFile(),
                    'line' => app()->isProduction() ? null : $e->getLine(),
                    'trace' => app()->isProduction() ? null : $e->getTraceAsString(),
                ], $status);
            }
        });
    })
    ->create();