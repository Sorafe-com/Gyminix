<?php

namespace App\Services;

use App\Libraries\JWTHandler;
use App\Models\AuditLogModel;
use App\Models\RefreshTokenModel;
use App\Models\UserModel;

class AuthService
{
    private UserModel $userModel;
    private RefreshTokenModel $tokenModel;
    private JWTHandler $jwt;
    private AuditLogModel $auditModel;

    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;

    public function __construct()
    {
        $this->userModel  = new UserModel();
        $this->tokenModel = new RefreshTokenModel();
        $this->jwt        = new JWTHandler();
        $this->auditModel = new AuditLogModel();
    }

    public function login(string $email, string $password, bool $remember = false): array
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return ['success' => false, 'message' => 'Credenciales incorrectas'];
        }

        if ($user['status'] == 0) {
            return ['success' => false, 'message' => 'Usuario inactivo'];
        }

        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'message' => "Cuenta bloqueada. Intente en {$remaining} minuto(s)"];
        }

        if (!password_verify($password, $user['password'])) {
            $this->handleFailedAttempt($user);
            return ['success' => false, 'message' => 'Credenciales incorrectas'];
        }

        $this->userModel->update($user['id'], [
            'failed_attempts' => 0,
            'locked_until'    => null,
            'last_login'      => date('Y-m-d H:i:s'),
        ]);

        $tokenPayload = [
            'sub'          => $user['id'],
            'gym_id'       => $user['gym_id'],
            'role_id'      => $user['role_id'],
            'is_super_admin' => (bool) $user['is_super_admin'],
        ];

        $accessToken  = $this->jwt->generateAccessToken($tokenPayload);
        $refreshToken = $this->jwt->generateRefreshToken($tokenPayload);

        $request = service('request');
        $this->tokenModel->insert([
            'user_id'    => $user['id'],
            'token'      => $refreshToken,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'expires_at' => date('Y-m-d H:i:s', time() + $this->jwt->getRefreshExpireTime()),
            'revoked'    => 0,
        ]);

        $this->auditModel->log([
            'gym_id'  => $user['gym_id'],
            'user_id' => $user['id'],
            'module'  => 'auth',
            'action'  => 'login',
            'description' => 'Inicio de sesión exitoso',
        ]);

        unset($user['password'], $user['remember_token']);

        return [
            'success'       => true,
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in'    => $this->jwt->getExpireTime(),
            'user'          => $user,
        ];
    }

    public function refresh(string $refreshToken): array
    {
        $tokenRecord = $this->tokenModel->findValid($refreshToken);
        if (!$tokenRecord) {
            return ['success' => false, 'message' => 'Refresh token inválido o expirado'];
        }

        $decoded = $this->jwt->decode($refreshToken);
        if (!$decoded || $decoded->type !== 'refresh') {
            return ['success' => false, 'message' => 'Token inválido'];
        }

        $user = $this->userModel->find($decoded->sub);
        if (!$user || $user['status'] == 0) {
            return ['success' => false, 'message' => 'Usuario no válido'];
        }

        $this->tokenModel->update($tokenRecord['id'], ['revoked' => 1]);

        $tokenPayload = [
            'sub'           => $user['id'],
            'gym_id'        => $user['gym_id'],
            'role_id'       => $user['role_id'],
            'is_super_admin' => (bool) $user['is_super_admin'],
        ];

        $request = service('request');
        $newAccessToken  = $this->jwt->generateAccessToken($tokenPayload);
        $newRefreshToken = $this->jwt->generateRefreshToken($tokenPayload);

        $this->tokenModel->insert([
            'user_id'    => $user['id'],
            'token'      => $newRefreshToken,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'expires_at' => date('Y-m-d H:i:s', time() + $this->jwt->getRefreshExpireTime()),
            'revoked'    => 0,
        ]);

        return [
            'success'       => true,
            'access_token'  => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'expires_in'    => $this->jwt->getExpireTime(),
        ];
    }

    public function logout(int $userId, string $refreshToken = ''): void
    {
        if ($refreshToken) {
            $this->tokenModel->where('token', $refreshToken)->set('revoked', 1)->update();
        }
        $this->auditModel->log([
            'user_id' => $userId,
            'module'  => 'auth',
            'action'  => 'logout',
            'description' => 'Cierre de sesión',
        ]);
    }

    public function logoutAll(int $userId): void
    {
        $this->tokenModel->revokeAllByUser($userId);
        $this->auditModel->log([
            'user_id' => $userId,
            'module'  => 'auth',
            'action'  => 'logout_all',
            'description' => 'Cierre de todas las sesiones',
        ]);
    }

    private function handleFailedAttempt(array $user): void
    {
        $attempts = $user['failed_attempts'] + 1;
        $data     = ['failed_attempts' => $attempts, 'locked_until' => null];

        if ($attempts >= self::MAX_ATTEMPTS) {
            $data['locked_until'] = date('Y-m-d H:i:s', time() + self::LOCK_MINUTES * 60);
            $data['failed_attempts'] = 0;
        }

        $this->userModel->update($user['id'], $data);
    }
}
