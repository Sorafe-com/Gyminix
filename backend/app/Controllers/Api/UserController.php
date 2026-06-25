<?php

namespace App\Controllers\Api;

use App\Libraries\ApiResponse;
use App\Services\UserService;
use OpenApi\Attributes as OA;

class UserController extends BaseApiController
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    #[OA\Get(
        path: '/users',
        summary: 'Listar usuarios',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'integer', enum: [0, 1])),
            new OA\Parameter(name: 'role_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada de usuarios'),
            new OA\Response(response: 401, description: 'No autorizado'),
        ]
    )]
    public function index()
    {
        $gymId   = $this->getGymId();
        $filters = [
            'search'  => $this->request->getVar('search'),
            'status'  => $this->request->getVar('status'),
            'role_id' => $this->request->getVar('role_id'),
        ];
        $page    = (int) ($this->request->getVar('page') ?? 1);
        $perPage = (int) ($this->request->getVar('per_page') ?? 15);
        return $this->paginate($this->userService->list($gymId, $filters, $page, $perPage));
    }

    #[OA\Get(
        path: '/users/{id}',
        summary: 'Obtener usuario por ID',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Datos del usuario'),
            new OA\Response(response: 404, description: 'Usuario no encontrado'),
        ]
    )]
    public function show($id = null)
    {
        $user = $this->userService->getById((int) $id);
        if (!$user) {
            return ApiResponse::error('Usuario no encontrado', [], 404);
        }
        return ApiResponse::success($user);
    }

    #[OA\Post(
        path: '/users',
        summary: 'Crear usuario',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'last_name', 'email', 'password', 'role_id'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'Juan'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Pérez'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', minLength: 6),
                    new OA\Property(property: 'role_id', type: 'integer'),
                    new OA\Property(property: 'phone', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Usuario creado'),
            new OA\Response(response: 422, description: 'Datos inválidos'),
        ]
    )]
    public function create()
    {
        $rules = [
            'first_name' => 'required|min_length[2]',
            'last_name'  => 'required|min_length[2]',
            'email'      => 'required|valid_email|is_unique[users.email]',
            'password'   => 'required|min_length[6]',
            'role_id'    => 'required|integer',
        ];
        if (!$this->validate($rules)) {
            return ApiResponse::error('Datos inválidos', $this->validator->getErrors());
        }
        $user = $this->userService->create($this->request->getJSON(true), $this->getUserId(), $this->getGymId());
        return ApiResponse::success($user, 'Usuario creado', 201);
    }

    #[OA\Put(
        path: '/users/{id}',
        summary: 'Actualizar usuario',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'first_name', type: 'string'),
                    new OA\Property(property: 'last_name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'role_id', type: 'integer'),
                    new OA\Property(property: 'phone', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Usuario actualizado'),
            new OA\Response(response: 404, description: 'Usuario no encontrado'),
        ]
    )]
    public function update($id = null)
    {
        $user = model('UserModel')->find($id);
        if (!$user) {
            return ApiResponse::error('Usuario no encontrado', [], 404);
        }
        $data = $this->request->getJSON(true);
        $user = $this->userService->update((int) $id, $data, $this->getUserId(), $this->getGymId());
        return ApiResponse::success($user, 'Usuario actualizado');
    }

    #[OA\Delete(
        path: '/users/{id}',
        summary: 'Eliminar usuario',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Usuario eliminado'),
            new OA\Response(response: 404, description: 'Usuario no encontrado'),
        ]
    )]
    public function delete($id = null)
    {
        $user = model('UserModel')->find($id);
        if (!$user) {
            return ApiResponse::error('Usuario no encontrado', [], 404);
        }
        $this->userService->delete((int) $id, $this->getUserId(), $this->getGymId());
        return ApiResponse::success(null, 'Usuario eliminado');
    }

    #[OA\Patch(
        path: '/users/{id}/status',
        summary: 'Activar / desactivar usuario',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Estado actualizado'),
            new OA\Response(response: 404, description: 'Usuario no encontrado'),
        ]
    )]
    public function toggleStatus($id = null)
    {
        $user = model('UserModel')->find($id);
        if (!$user) {
            return ApiResponse::error('Usuario no encontrado', [], 404);
        }
        $user = $this->userService->toggleStatus((int) $id, $this->getUserId(), $this->getGymId());
        return ApiResponse::success($user, 'Estado actualizado');
    }
}
