<?php

namespace App\Controllers\Api;

use App\Libraries\ApiResponse;
use App\Models\AuditLogModel;
use App\Models\MenuModel;
use App\Models\RolePermissionModel;
use OpenApi\Attributes as OA;

class MenuController extends BaseApiController
{
    private MenuModel $menuModel;

    public function __construct()
    {
        $this->menuModel = new MenuModel();
    }

    #[OA\Get(
        path: '/menus',
        summary: 'Listar menús activos',
        tags: ['Menus'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de menús'),
            new OA\Response(response: 401, description: 'No autorizado'),
        ]
    )]
    public function index()
    {
        $menus = $this->menuModel->where('status', 1)->orderBy('`order`', 'ASC')->findAll();
        return ApiResponse::success($menus);
    }

    #[OA\Get(
        path: '/menus/tree',
        summary: 'Árbol jerárquico de menús',
        tags: ['Menus'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Árbol de menús con hijos anidados'),
        ]
    )]
    public function tree()
    {
        return ApiResponse::success($this->menuModel->getTree());
    }

    #[OA\Get(
        path: '/menus/user',
        summary: 'Menús filtrados por permisos del usuario autenticado',
        tags: ['Menus'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Menús accesibles para el usuario'),
        ]
    )]
    public function userMenus()
    {
        $user    = $this->getAuthUser();
        $allMenus = $this->menuModel->getTree();

        if ($user['is_super_admin']) {
            return ApiResponse::success($allMenus);
        }

        $rpModel = new RolePermissionModel();
        $perms   = array_column($rpModel->getPermissionsByRole($user['role_id']), 'slug');
        $filtered = $this->filterMenusByPermissions($allMenus, $perms);
        return ApiResponse::success($filtered);
    }

    #[OA\Post(
        path: '/menus',
        summary: 'Crear menú',
        tags: ['Menus'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'slug'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Usuarios'),
                    new OA\Property(property: 'slug', type: 'string', example: 'users'),
                    new OA\Property(property: 'icon', type: 'string', example: 'users'),
                    new OA\Property(property: 'route', type: 'string', example: '/users'),
                    new OA\Property(property: 'parent_id', type: 'integer', nullable: true),
                    new OA\Property(property: 'order', type: 'integer', example: 1),
                    new OA\Property(property: 'permission_slug', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Menú creado'),
            new OA\Response(response: 422, description: 'Datos inválidos'),
        ]
    )]
    public function create()
    {
        $rules = ['name' => 'required', 'slug' => 'required'];
        if (!$this->validate($rules)) {
            return ApiResponse::error('Datos inválidos', $this->validator->getErrors());
        }
        $id   = $this->menuModel->insert($this->request->getJSON(true), true);
        $menu = $this->menuModel->find($id);
        (new AuditLogModel())->log(['user_id' => $this->getUserId(), 'module' => 'menus', 'action' => 'create', 'record_id' => $id]);
        return ApiResponse::success($menu, 'Menú creado', 201);
    }

    #[OA\Put(
        path: '/menus/{id}',
        summary: 'Actualizar menú',
        tags: ['Menus'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'icon', type: 'string'),
                    new OA\Property(property: 'route', type: 'string'),
                    new OA\Property(property: 'order', type: 'integer'),
                    new OA\Property(property: 'status', type: 'integer', enum: [0, 1]),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Menú actualizado'),
            new OA\Response(response: 404, description: 'Menú no encontrado'),
        ]
    )]
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $this->menuModel->update($id, $data);
        (new AuditLogModel())->log(['user_id' => $this->getUserId(), 'module' => 'menus', 'action' => 'update', 'record_id' => $id]);
        return ApiResponse::success($this->menuModel->find($id), 'Menú actualizado');
    }

    #[OA\Delete(
        path: '/menus/{id}',
        summary: 'Eliminar menú',
        tags: ['Menus'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Menú eliminado'),
        ]
    )]
    public function delete($id = null)
    {
        $this->menuModel->delete($id);
        (new AuditLogModel())->log(['user_id' => $this->getUserId(), 'module' => 'menus', 'action' => 'delete', 'record_id' => $id]);
        return ApiResponse::success(null, 'Menú eliminado');
    }

    private function filterMenusByPermissions(array $menus, array $perms): array
    {
        $result = [];
        foreach ($menus as $menu) {
            if (!$menu['permission_slug'] || in_array($menu['permission_slug'], $perms)) {
                if (!empty($menu['children'])) {
                    $menu['children'] = $this->filterMenusByPermissions($menu['children'], $perms);
                }
                $result[] = $menu;
            }
        }
        return $result;
    }
}
