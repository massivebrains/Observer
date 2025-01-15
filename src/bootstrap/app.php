<?php declare(strict_types=1);

use App\Http\Middleware\InjectFingerprintToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/healthy',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(InjectFingerprintToResponse::class);

        $middleware->api([
            'auth.token' => \App\Http\Middleware\AuthTokenMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn () => true);

        /** @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter */
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json(null, 404);
        });
    })->create();
