<?php

namespace App\Filters;

use App\Libraries\ApiResponse;
use App\Libraries\JWTHandler;
use App\Libraries\UserContext;
use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class JWTAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return ApiResponse::error('Token requerido', [], 401);
        }

        $token   = substr($authHeader, 7);
        $jwt     = new JWTHandler();
        $decoded = $jwt->decode($token);

        if (!$decoded || ($decoded->type ?? '') !== 'access') {
            return ApiResponse::error('Token inválido o expirado', [], 401);
        }

        $userModel = new UserModel();
        $user      = $userModel->find($decoded->sub);

        if (!$user || $user['status'] == 0 || $user['deleted_at'] !== null) {
            return ApiResponse::error('Usuario no autorizado', [], 403);
        }

        UserContext::set($user);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
