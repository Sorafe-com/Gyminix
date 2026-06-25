<?php

namespace App\Controllers\Api;

use App\Libraries\ApiResponse;
use App\Services\BranchService;
use OpenApi\Attributes as OA;

class BranchController extends BaseApiController
{
    private BranchService $branchService;

    public function __construct()
    {
        $this->branchService = new BranchService();
    }

    #[OA\Get(
        path: '/branches',
        summary: 'Listar sucursales',
        tags: ['Branches'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'integer', enum: [0, 1])),
            new OA\Parameter(name: 'gym_id', in: 'query', description: 'Solo super admin', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada de sucursales'),
            new OA\Response(response: 401, description: 'No autorizado'),
        ]
    )]
    public function index()
    {
        $gymId   = $this->isSuperAdmin() ? (int) ($this->request->getVar('gym_id') ?? $this->getGymId()) : $this->getGymId();
        $filters = ['search' => $this->request->getVar('search'), 'status' => $this->request->getVar('status')];
        $page    = (int) ($this->request->getVar('page') ?? 1);
        $perPage = (int) ($this->request->getVar('per_page') ?? 15);
        return $this->paginate($this->branchService->listByGym($gymId, $filters, $page, $perPage));
    }

    #[OA\Get(
        path: '/branches/{id}',
        summary: 'Obtener sucursal por ID',
        tags: ['Branches'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Datos de la sucursal'),
            new OA\Response(response: 404, description: 'Sucursal no encontrada'),
        ]
    )]
    public function show($id = null)
    {
        $branch = model('BranchModel')->find($id);
        if (!$branch) {
            return ApiResponse::error('Sucursal no encontrada', [], 404);
        }
        return ApiResponse::success($branch);
    }

    #[OA\Post(
        path: '/branches',
        summary: 'Crear sucursal',
        tags: ['Branches'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Sucursal Centro'),
                    new OA\Property(property: 'address', type: 'string'),
                    new OA\Property(property: 'phone', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Sucursal creada'),
            new OA\Response(response: 422, description: 'Datos inválidos'),
        ]
    )]
    public function create()
    {
        $rules = ['name' => 'required|min_length[2]'];
        if (!$this->validate($rules)) {
            return ApiResponse::error('Datos inválidos', $this->validator->getErrors());
        }
        $gymId  = $this->getGymId();
        $branch = $this->branchService->create($this->request->getJSON(true), $gymId, $this->getUserId());
        return ApiResponse::success($branch, 'Sucursal creada', 201);
    }

    #[OA\Put(
        path: '/branches/{id}',
        summary: 'Actualizar sucursal',
        tags: ['Branches'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'address', type: 'string'),
                    new OA\Property(property: 'phone', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Sucursal actualizada'),
            new OA\Response(response: 404, description: 'Sucursal no encontrada'),
        ]
    )]
    public function update($id = null)
    {
        $branch = model('BranchModel')->find($id);
        if (!$branch) {
            return ApiResponse::error('Sucursal no encontrada', [], 404);
        }
        $branch = $this->branchService->update((int) $id, $this->request->getJSON(true), $this->getUserId(), $this->getGymId());
        return ApiResponse::success($branch, 'Sucursal actualizada');
    }

    #[OA\Delete(
        path: '/branches/{id}',
        summary: 'Eliminar sucursal',
        tags: ['Branches'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Sucursal eliminada'),
            new OA\Response(response: 404, description: 'Sucursal no encontrada'),
        ]
    )]
    public function delete($id = null)
    {
        $branch = model('BranchModel')->find($id);
        if (!$branch) {
            return ApiResponse::error('Sucursal no encontrada', [], 404);
        }
        $this->branchService->delete((int) $id, $this->getUserId(), $this->getGymId());
        return ApiResponse::success(null, 'Sucursal eliminada');
    }

    #[OA\Patch(
        path: '/branches/{id}/status',
        summary: 'Activar / desactivar sucursal',
        tags: ['Branches'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Estado actualizado'),
            new OA\Response(response: 404, description: 'Sucursal no encontrada'),
        ]
    )]
    public function toggleStatus($id = null)
    {
        $branch = model('BranchModel')->find($id);
        if (!$branch) {
            return ApiResponse::error('Sucursal no encontrada', [], 404);
        }
        $branch = $this->branchService->toggleStatus((int) $id, $this->getUserId(), $this->getGymId());
        return ApiResponse::success($branch, 'Estado actualizado');
    }
}
