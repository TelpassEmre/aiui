<?php
declare(strict_types=1);

namespace App\Controllers;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

class AuthController
{
    public function loginPage(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        ob_start();
        include BASE_PATH . '/app/Views/auth/login.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    }

    public function loginPost(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $data     = $request->getParsedBody();
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        $user = Capsule::connection('local')
            ->table('users')
            ->where('username', $username)
            ->where('is_active', 1)
            ->first();

        if (!$user || !password_verify($password, $user->password_hash)) {
            $error = 'Kullanıcı adı veya şifre hatalı.';
            ob_start();
            include BASE_PATH . '/app/Views/auth/login.php';
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response;
        }

        // Session başlat
        session_regenerate_id(true);
        $_SESSION['user_id']       = $user->id;
        $_SESSION['user_username'] = $user->username;
        $_SESSION['user_role']     = $user->role;
        $_SESSION['user_email']    = $user->email;

        // Son giriş güncelle
        Capsule::connection('local')->table('users')
            ->where('id', $user->id)
            ->update(['last_login' => date('Y-m-d H:i:s')]);

        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    public function logout(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
