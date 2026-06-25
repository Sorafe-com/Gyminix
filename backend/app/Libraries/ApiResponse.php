<?php

namespace App\Libraries;

use CodeIgniter\HTTP\ResponseInterface;

class ApiResponse
{
    public static function success($data = null, string $message = 'OK', int $code = 200): ResponseInterface
    {
        return response()->setStatusCode($code)->setJSON([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => [],
        ]);
    }

    public static function error(string $message = 'Error', array $errors = [], int $code = 400): ResponseInterface
    {
        return response()->setStatusCode($code)->setJSON([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ]);
    }

    public static function paginated(array $data, array $meta, string $message = 'OK'): ResponseInterface
    {
        return response()->setStatusCode(200)->setJSON([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta,
            'errors'  => [],
        ]);
    }
}
