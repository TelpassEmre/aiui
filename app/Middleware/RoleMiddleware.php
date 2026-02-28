<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class RoleMiddleware implements MiddlewareInterface
{
    public function __construct(private string $requiredRole) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userRole = $_SESSION['user_role'] ?? 'viewer';
        $hierarchy = ['viewer' => 0, 'editor' => 1, 'admin' => 2];

        if (($hierarchy[$userRole] ?? 0) < ($hierarchy[$this->requiredRole] ?? 99)) {
            $response = new Response();
            $response->getBody()->write('<h1>403 — Yetkisiz Erişim</h1>');
            return $response->withStatus(403);
        }

        return $handler->handle($request);
    }
}
