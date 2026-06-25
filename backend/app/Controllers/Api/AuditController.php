<?php

namespace App\Controllers\Api;

use App\Models\AuditLogModel;
use OpenApi\Attributes as OA;

class AuditController extends BaseApiController
{
    private AuditLogModel $auditModel;

    public function __construct()
    {
        $this->auditModel = new AuditLogModel();
    }

    #[OA\Get(
        path: '/audit-logs',
        summary: 'Listar registros de auditoría',
        tags: ['Audit'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'module', in: 'query', schema: new OA\Schema(type: 'string', example: 'users')),
            new OA\Parameter(name: 'action', in: 'query', schema: new OA\Schema(type: 'string', enum: ['create', 'update', 'delete'])),
            new OA\Parameter(name: 'user_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date_from', in: 'query', schema: new OA\Schema(type: 'string', format: 'date', example: '2024-01-01')),
            new OA\Parameter(name: 'date_to', in: 'query', schema: new OA\Schema(type: 'string', format: 'date', example: '2024-12-31')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginada de registros de auditoría'),
            new OA\Response(response: 401, description: 'No autorizado'),
        ]
    )]
    public function index()
    {
        $builder = $this->auditModel->builder('al');
        $builder->select('al.*, u.first_name, u.last_name, u.email')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->orderBy('al.created_at', 'DESC');

        if (!$this->isSuperAdmin()) {
            $builder->where('al.gym_id', $this->getGymId());
        }

        $filters = [
            'module'     => $this->request->getVar('module'),
            'action'     => $this->request->getVar('action'),
            'user_id'    => $this->request->getVar('user_id'),
            'date_from'  => $this->request->getVar('date_from'),
            'date_to'    => $this->request->getVar('date_to'),
        ];

        if ($filters['module'])    $builder->where('al.module', $filters['module']);
        if ($filters['action'])    $builder->where('al.action', $filters['action']);
        if ($filters['user_id'])   $builder->where('al.user_id', $filters['user_id']);
        if ($filters['date_from']) $builder->where('al.created_at >=', $filters['date_from'] . ' 00:00:00');
        if ($filters['date_to'])   $builder->where('al.created_at <=', $filters['date_to'] . ' 23:59:59');

        $page    = (int) ($this->request->getVar('page') ?? 1);
        $perPage = (int) ($this->request->getVar('per_page') ?? 20);
        $total   = (clone $builder)->countAllResults(false);
        $logs    = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        return response()->setJSON([
            'success' => true,
            'message' => 'OK',
            'data'    => $logs,
            'meta'    => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'pages' => (int) ceil($total / $perPage)],
            'errors'  => [],
        ]);
    }
}
