<?php

namespace App\Controllers\Api;

use App\Libraries\ApiResponse;
use App\Services\GymService;
use OpenApi\Attributes as OA;

class GymController extends BaseApiController
{
    private GymService $gymService;

    public function __construct()
    {
        $this->gymService = new GymService();
    }

    #[OA\Get(
        path: '/gyms',
        summary: 'Listar gimnasios',
        tags: ['Gyms'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'integer', enum: [0, 1])),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada de gimnasios'),
            new OA\Response(response: 401, description: 'No autorizado'),
        ]
    )]
    public function index()
    {
        $filters = [
            'search' => $this->request->getVar('search'),
            'status' => $this->request->getVar('status'),
        ];
        $page    = (int) ($this->request->getVar('page') ?? 1);
        $perPage = (int) ($this->request->getVar('per_page') ?? 15);
        return $this->paginate($this->gymService->list($filters, $page, $perPage));
    }

    #[OA\Get(
        path: '/gyms/{id}',
        summary: 'Obtener gimnasio por ID',
        tags: ['Gyms'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Datos del gimnasio'),
            new OA\Response(response: 404, description: 'Gimnasio no encontrado'),
        ]
    )]
    public function show($id = null)
    {
        $gym = model('GymModel')->find($id);
        if (!$gym) {
            return ApiResponse::error('Gimnasio no encontrado', [], 404);
        }
        return ApiResponse::success($gym);
    }

    #[OA\Post(
        path: '/gyms',
        summary: 'Crear gimnasio',
        tags: ['Gyms'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Gimnasio Ejemplo'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'phone', type: 'string'),
                    new OA\Property(property: 'address', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Gimnasio creado'),
            new OA\Response(response: 422, description: 'Datos inválidos'),
        ]
    )]
    public function create()
    {
        $rules = [
            'name'  => 'required|min_length[2]',
            'email' => 'permit_empty|valid_email',
        ];
        if (!$this->validate($rules)) {
            return ApiResponse::error('Datos inválidos', $this->validator->getErrors());
        }
        $gym = $this->gymService->create($this->request->getJSON(true), $this->getUserId());
        return ApiResponse::success($gym, 'Gimnasio creado', 201);
    }

    #[OA\Put(
        path: '/gyms/{id}',
        summary: 'Actualizar gimnasio',
        tags: ['Gyms'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'phone', type: 'string'),
                    new OA\Property(property: 'address', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Gimnasio actualizado'),
            new OA\Response(response: 404, description: 'Gimnasio no encontrado'),
        ]
    )]
    public function update($id = null)
    {
        $gym = model('GymModel')->find($id);
        if (!$gym) {
            return ApiResponse::error('Gimnasio no encontrado', [], 404);
        }
        $data = $this->request->getJSON(true);
        $gym  = $this->gymService->update((int) $id, $data, $this->getUserId());
        return ApiResponse::success($gym, 'Gimnasio actualizado');
    }

    #[OA\Patch(
        path: '/gyms/{id}/status',
        summary: 'Activar / desactivar gimnasio',
        tags: ['Gyms'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Estado actualizado'),
            new OA\Response(response: 404, description: 'Gimnasio no encontrado'),
        ]
    )]
    public function toggleStatus($id = null)
    {
        $gym = model('GymModel')->find($id);
        if (!$gym) {
            return ApiResponse::error('Gimnasio no encontrado', [], 404);
        }
        $gym = $this->gymService->toggleStatus((int) $id, $this->getUserId());
        return ApiResponse::success($gym, 'Estado actualizado');
    }

    #[OA\Get(
        path: '/gyms/{id}/settings',
        summary: 'Obtener configuración del gimnasio',
        tags: ['Gyms'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Configuración del gimnasio'),
        ]
    )]
    public function getSettings($id = null)
    {
        $settings = $this->gymService->getSettings((int) $id);
        return ApiResponse::success($settings);
    }

    #[OA\Post(
        path: '/gyms/{id}/settings',
        summary: 'Guardar configuración del gimnasio',
        tags: ['Gyms'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(type: 'object', description: 'Pares clave-valor de configuración')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Configuración guardada'),
        ]
    )]
    public function saveSettings($id = null)
    {
        $data = $this->request->getJSON(true);
        $this->gymService->saveSettings((int) $id, $data, $this->getUserId());
        return ApiResponse::success(null, 'Configuración guardada');
    }

    #[OA\Post(
        path: '/gyms/{id}/logo',
        summary: 'Subir logo del gimnasio',
        tags: ['Gyms'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['logo'],
                    properties: [
                        new OA\Property(property: 'logo', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Logo actualizado'),
            new OA\Response(response: 400, description: 'Archivo inválido'),
        ]
    )]
    public function uploadLogo($id = null)
    {
        $file = $this->request->getFile('logo');
        if (!$file || !$file->isValid()) {
            return ApiResponse::error('Archivo inválido', [], 400);
        }
        $name = $file->getRandomName();
        $file->move(ROOTPATH . 'public/uploads/logos', $name);
        model('GymModel')->update($id, ['logo' => 'uploads/logos/' . $name]);
        return ApiResponse::success(['logo' => 'uploads/logos/' . $name], 'Logo actualizado');
    }
}
