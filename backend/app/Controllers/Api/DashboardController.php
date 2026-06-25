<?php

namespace App\Controllers\Api;

use App\Libraries\ApiResponse;
use OpenApi\Attributes as OA;

class DashboardController extends BaseApiController
{
    #[OA\Get(
        path: '/dashboard',
        summary: 'Estadísticas del dashboard',
        tags: ['Dashboard'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estadísticas generales y actividad reciente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'stats', type: 'object', properties: [
                                new OA\Property(property: 'gyms', type: 'integer'),
                                new OA\Property(property: 'branches', type: 'integer'),
                                new OA\Property(property: 'users', type: 'integer'),
                                new OA\Property(property: 'roles', type: 'integer'),
                            ]),
                            new OA\Property(property: 'recent_logs', type: 'array', items: new OA\Items(type: 'object')),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autorizado'),
        ]
    )]
    public function index()
    {
        $db    = db_connect();
        $gymId = $this->getGymId();

        $stats = [
            'gyms'     => 0,
            'branches' => 0,
            'users'    => 0,
            'roles'    => 0,
        ];

        if ($this->isSuperAdmin()) {
            $stats['gyms']     = $db->table('gyms')->where('deleted_at IS NULL')->countAllResults();
            $stats['branches'] = $db->table('branches')->where('deleted_at IS NULL')->countAllResults();
            $stats['users']    = $db->table('users')->where('deleted_at IS NULL')->countAllResults();
            $stats['roles']    = $db->table('roles')->where('deleted_at IS NULL')->countAllResults();
        } else {
            $stats['branches'] = $db->table('branches')->where('gym_id', $gymId)->where('deleted_at IS NULL')->countAllResults();
            $stats['users']    = $db->table('users')->where('gym_id', $gymId)->where('deleted_at IS NULL')->countAllResults();
            $stats['roles']    = $db->table('roles')->where('gym_id', $gymId)->where('deleted_at IS NULL')->countAllResults();
        }

        $recentLogs = $db->table('audit_logs al')
            ->select('al.module, al.action, al.description, al.created_at, u.first_name, u.last_name')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->where($this->isSuperAdmin() ? '1=1' : 'al.gym_id = ' . $gymId)
            ->orderBy('al.created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return ApiResponse::success([
            'stats'       => $stats,
            'recent_logs' => $recentLogs,
        ]);
    }
}
