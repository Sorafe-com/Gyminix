<?php

namespace App\Controllers\Api;

use App\Libraries\ApiResponse;
use App\Services\RoleService;
use OpenApi\Attributes as OA;

class RoleController extends BaseApiController
{
    private RoleService $roleService;

    public function __construct()
    {
        $this->roleService = new RoleService();
    }

    #[OA\Get(
        path: '/roles',
        summary: 'Listar roles del gimnasio',
        tags: ['Roles'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de roles'),
            new OA\Response(response: 401, description: 'No autorizado'),
        ]
    )]
    public function index()
    {
        $roles = $this->roleService->listRoles($this->getGymId());
        return ApiResponse::success($roles);
    }

    #[OA\Post(
        path: '/roles',
        summary: 'Crear rol',
        tags: ['Roles'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Entrenador'),
                    new OA\Property(property: 'description', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Rol creado'),
            new OA\Response(response: 422, description: 'Datos inválidos'),
        ]
    )]
    public function create()
    {
        $rules = ['name' => 'required|min_length[2]'];
        if (!$this->validate($rules)) {
            return ApiResponse::error('Datos inválidos', $this->validator->getErrors());
        }
        $role = $this->roleService->createRole($this->request->getJSON(true), $this->getGymId(), $this->getUserId());
        return ApiResponse::success($role, 'Rol creado', 201);
    }

    #[OA\Put(
        path: '/roles/{id}',
        summary: 'Actualizar rol',
        tags: ['Roles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Rol actualizado'),
            new OA\Response(response: 404, description: 'Rol no encontrado'),
        ]
    )]
    public function update($id = null)
    {
        $role = $this->roleService->updateRole((int) $id, $this->request->getJSON(true), $this->getUserId(), $this->getGymId());
        return ApiResponse::success($role, 'Rol actualizado');
    }

    #[OA\Delete(
        path: '/roles/{id}',
        summary: 'Eliminar rol',
        tags: ['Roles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Rol eliminado'),
            new OA\Response(response: 404, description: 'Rol no encontrado'),
        ]
    )]
    public function delete($id = null)
    {
        $this->roleService->deleteRole((int) $id, $this->getUserId(), $this->getGymId());
        return ApiResponse::success(null, 'Rol eliminado');
    }

    #[OA\Get(
        path: '/permissions',
        summary: 'Listar todos los permisos disponibles',
        tags: ['Roles'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de permisos'),
        ]
    )]
    public function permissions()
    {
        $perms = $this->roleService->listPermissions();
        return ApiResponse::success($perms);
    }

    #[OA\Get(
        path: '/roles/{id}/permissions',
        summary: 'Obtener permisos asignados a un rol',
        tags: ['Roles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Permisos del rol'),
        ]
    )]
    public function getRolePermissions($id = null)
    {
        $perms = $this->roleService->getRolePermissions((int) $id);
        return ApiResponse::success($perms);
    }

    #[OA\Post(
        path: '/roles/{id}/permissions/sync',
        summary: 'Sincronizar permisos de un rol',
        tags: ['Roles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'permission_ids',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [1, 2, 3]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Permisos sincronizados'),
        ]
    )]
    public function syncPermissions($id = null)
    {
        $permIds = $this->request->getJSON(true)['permission_ids'] ?? [];
        $this->roleService->syncRolePermissions((int) $id, $permIds, $this->getUserId(), $this->getGymId());
        return ApiResponse::success(null, 'Permisos sincronizados');
    }
}
