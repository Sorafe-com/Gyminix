<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Home::index');

// API v1
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], function ($routes) {

    // Public auth routes (no JWT required)
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/refresh', 'AuthController::refresh');

    // API docs — solo en entorno development
    if (ENVIRONMENT === 'development') {
        $routes->get('docs', 'SwaggerController::ui');
        $routes->get('docs/json', 'SwaggerController::json');
    }

    // Protected routes
    $routes->group('', ['filter' => 'jwt'], function ($routes) {

        // Auth
        $routes->post('auth/logout',     'AuthController::logout');
        $routes->post('auth/logout-all', 'AuthController::logoutAll');
        $routes->get('auth/me',          'AuthController::me');

        // Dashboard
        $routes->get('dashboard', 'DashboardController::index');

        // Gyms (super admin only)
        $routes->get('gyms',                    'GymController::index');
        $routes->get('gyms/(:num)',             'GymController::show/$1');
        $routes->post('gyms',                   'GymController::create');
        $routes->put('gyms/(:num)',             'GymController::update/$1');
        $routes->patch('gyms/(:num)/status',    'GymController::toggleStatus/$1');
        $routes->get('gyms/(:num)/settings',    'GymController::getSettings/$1');
        $routes->post('gyms/(:num)/settings',   'GymController::saveSettings/$1');
        $routes->post('gyms/(:num)/logo',       'GymController::uploadLogo/$1');

        // Branches
        $routes->get('branches',                 'BranchController::index');
        $routes->get('branches/(:num)',          'BranchController::show/$1');
        $routes->post('branches',                'BranchController::create');
        $routes->put('branches/(:num)',          'BranchController::update/$1');
        $routes->delete('branches/(:num)',       'BranchController::delete/$1');
        $routes->patch('branches/(:num)/status', 'BranchController::toggleStatus/$1');

        // Users
        $routes->get('users',                 'UserController::index');
        $routes->get('users/(:num)',          'UserController::show/$1');
        $routes->post('users',                'UserController::create');
        $routes->put('users/(:num)',          'UserController::update/$1');
        $routes->delete('users/(:num)',       'UserController::delete/$1');
        $routes->patch('users/(:num)/status', 'UserController::toggleStatus/$1');

        // Roles & Permissions
        $routes->get('roles',                          'RoleController::index');
        $routes->post('roles',                         'RoleController::create');
        $routes->put('roles/(:num)',                   'RoleController::update/$1');
        $routes->delete('roles/(:num)',                'RoleController::delete/$1');
        $routes->get('permissions',                    'RoleController::permissions');
        $routes->get('roles/(:num)/permissions',       'RoleController::getRolePermissions/$1');
        $routes->post('roles/(:num)/permissions/sync', 'RoleController::syncPermissions/$1');

        // Menus
        $routes->get('menus',           'MenuController::index');
        $routes->get('menus/tree',      'MenuController::tree');
        $routes->get('menus/user',      'MenuController::userMenus');
        $routes->post('menus',          'MenuController::create');
        $routes->put('menus/(:num)',    'MenuController::update/$1');
        $routes->delete('menus/(:num)', 'MenuController::delete/$1');

        // Audit logs
        $routes->get('audit-logs', 'AuditController::index');
    });
});
