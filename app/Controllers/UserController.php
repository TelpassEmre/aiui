<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuditService;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{
    public function __construct(private AuditService $audit) {}

    private function db() { return Capsule::connection('local'); }

    public function index(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $users = $this->db()->table('users')->orderBy('created_at', 'desc')->get();
        ob_start(); include BASE_PATH . '/app/Views/users/index.php'; $html = ob_get_clean();
        $response->getBody()->write($html); return $response;
    }

    public function create(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        ob_start(); include BASE_PATH . '/app/Views/users/create.php'; $html = ob_get_clean();
        $response->getBody()->write($html); return $response;
    }

    public function store(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $data = $request->getParsedBody();

        $this->db()->table('users')->insert([
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'role'          => $data['role'] ?? 'viewer',
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->audit->log('user_create', 'users', null, ['username' => $data['username']]);
        return $response->withHeader('Location', '/users')->withStatus(302);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $user = $this->db()->table('users')->find((int)$args['id']);
        ob_start(); include BASE_PATH . '/app/Views/users/edit.php'; $html = ob_get_clean();
        $response->getBody()->write($html); return $response;
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id   = (int)$args['id'];
        $data = $request->getParsedBody();

        $update = [
            'username'   => $data['username'],
            'email'      => $data['email'],
            'role'       => $data['role'],
            'is_active'  => isset($data['is_active']) ? 1 : 0,
        ];

        if (!empty($data['password'])) {
            $update['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $this->db()->table('users')->where('id', $id)->update($update);
        $this->audit->log('user_update', "users:{$id}", null, $update);
        return $response->withHeader('Location', '/users')->withStatus(302);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id = (int)$args['id'];
        // Kendini silemesin
        if ($id === (int)$_SESSION['user_id']) {
            return $response->withHeader('Location', '/users')->withStatus(302);
        }
        $this->db()->table('users')->where('id', $id)->delete();
        $this->audit->log('user_delete', "users:{$id}");
        return $response->withHeader('Location', '/users')->withStatus(302);
    }
}
