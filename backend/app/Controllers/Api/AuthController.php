<?php

namespace App\Controllers\Api;

use App\Libraries\ApiResponse;
use App\Services\AuthService;
use OpenApi\Attributes as OA;

class AuthController extends BaseApiController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    #[OA\Post(
        path: '/auth/login',
        summary: 'Iniciar sesión',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@gyminix.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'secret123'),
                    new OA\Property(property: 'remember', type: 'boolean', example: false),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login exitoso — retorna access_token y refresh_token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Inicio de sesión exitoso'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'access_token', type: 'string'),
                            new OA\Property(property: 'refresh_token', type: 'string'),
                            new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Credenciales inválidas'),
            new OA\Response(response: 422, description: 'Datos de entrada inválidos'),
        ]
    )]
    public function login()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[4]',
        ];

        if (!$this->validate($rules)) {
            return ApiResponse::error('Datos inválidos', $this->validator->getErrors());
        }

        $result = $this->authService->login(
            $this->request->getVar('email'),
            $this->request->getVar('password'),
            (bool) $this->request->getVar('remember')
        );

        if (!$result['success']) {
            return ApiResponse::error($result['message'], [], 401);
        }

        return $this->ok([
            'access_token'  => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
            'expires_in'    => $result['expires_in'],
            'user'          => $result['user'],
        ], 'Inicio de sesión exitoso');
    }

    #[OA\Post(
        path: '/auth/refresh',
        summary: 'Renovar access token',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'refresh_token', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Token renovado'),
            new OA\Response(response: 401, description: 'Refresh token inválido o expirado'),
        ]
    )]
    public function refresh()
    {
        $token = $this->request->getVar('refresh_token') ?? $this->request->getHeaderLine('X-Refresh-Token');
        if (!$token) {
            return $this->error('Refresh token requerido', [], 400);
        }

        $result = $this->authService->refresh($token);
        if (!$result['success']) {
            return $this->error($result['message'], [], 401);
        }

        return $this->ok($result, 'Token renovado');
    }

    #[OA\Post(
        path: '/auth/logout',
        summary: 'Cerrar sesión actual',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'refresh_token', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Sesión cerrada'),
            new OA\Response(response: 401, description: 'No autorizado'),
        ]
    )]
    public function logout()
    {
        $userId = $this->getAuthUser()['id'] ?? 0;
        $refreshToken = $this->request->getVar('refresh_token') ?? '';
        $this->authService->logout($userId, $refreshToken);
        return $this->ok(null, 'Sesión cerrada');
    }

    #[OA\Post(
        path: '/auth/logout-all',
        summary: 'Cerrar todas las sesiones',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Todas las sesiones cerradas'),
            new OA\Response(response: 401, description: 'No autorizado'),
        ]
    )]
    public function logoutAll()
    {
        $userId = $this->getAuthUser()['id'] ?? 0;
        $this->authService->logoutAll($userId);
        return $this->ok(null, 'Todas las sesiones cerradas');
    }

    #[OA\Get(
        path: '/auth/me',
        summary: 'Obtener usuario autenticado',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Datos del usuario autenticado'),
            new OA\Response(response: 401, description: 'No autorizado'),
        ]
    )]
    public function me()
    {
        $user = $this->getAuthUser();
        unset($user['password'], $user['remember_token']);
        return $this->ok($user, 'Usuario autenticado');
    }
}
