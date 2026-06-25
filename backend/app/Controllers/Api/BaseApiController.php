<?php

namespace App\Controllers\Api;

use App\Libraries\ApiResponse;
use App\Libraries\UserContext;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

abstract class BaseApiController extends Controller
{
    protected function ok($data = null, string $message = 'OK', int $status = 200): ResponseInterface
    {
        return ApiResponse::success($data, $message, $status);
    }

    protected function error(string $message = 'Error', array $errors = [], int $status = 400): ResponseInterface
    {
        return ApiResponse::error($message, $errors, $status);
    }

    protected function paginate(array $result): ResponseInterface
    {
        return response()->setStatusCode(200)->setJSON([
            'success' => true,
            'message' => 'OK',
            'data'    => $result['data'],
            'meta'    => $result['meta'],
            'errors'  => [],
        ]);
    }

    protected function paginatedResponse(array $result): ResponseInterface
    {
        return $this->paginate($result);
    }

    protected function getAuthUser(): ?array
    {
        return UserContext::get();
    }

    protected function getGymId(): int
    {
        return (int) (UserContext::get()['gym_id'] ?? 0);
    }

    protected function getUserId(): int
    {
        return (int) (UserContext::get()['id'] ?? 0);
    }

    protected function isSuperAdmin(): bool
    {
        return (bool) (UserContext::get()['is_super_admin'] ?? false);
    }
}
