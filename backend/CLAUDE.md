# Gyminix — Backend (CodeIgniter 4)

API REST multi-gimnasio construida con CodeIgniter 4 + PHP 8.2. Toda la lógica de negocio reside aquí; el frontend la consume vía HTTP.

## Stack y versiones

| Herramienta      | Versión    |
|------------------|------------|
| PHP              | 8.2        |
| CodeIgniter      | 4.7        |
| MySQL            | 8.0        |
| firebase/php-jwt | ^7.1       |
| Composer         | 2.x        |

## Arrancar / comandos spark

```bash
# Ejecutar desde: c:\xampp\htdocs\Gyminix\backend\

# Migraciones
php spark migrate --all          # crear tablas
php spark migrate:rollback       # deshacer última migración

# Seeders
php spark db:seed MainSeeder     # datos iniciales completos

# Servidor de desarrollo (alternativa a XAMPP)
php spark serve --port=8080

# Lint PHP
php -l app/Controllers/Api/MiControlador.php
```

El backend se sirve normalmente con **Apache XAMPP** en:
`http://localhost/Gyminix/backend/public/`

## Variables de entorno (`.env`)

```ini
CI_ENVIRONMENT     = development
app.baseURL        = 'http://localhost/Gyminix/backend/public/'
app.CSRFProtection = false          # desactivado — la API usa JWT

database.default.hostname = localhost
database.default.database = gyminix_db
database.default.username = root
database.default.password =

jwt.secret         = Gyminix_JWT_Secret_Key_2024_XYZ_Secure
jwt.expire         = 3600      # segundos (1 h)
jwt.refresh_expire = 604800    # segundos (7 días)
```

---

## Arquitectura

```
app/
├── Config/
│   ├── Routes.php          ← todas las rutas API v1
│   └── Filters.php         ← registro de filtros JWT y CORS
│
├── Controllers/Api/
│   ├── BaseApiController   ← extiende Controller; métodos ok(), error(), paginate(), getAuthUser(), getGymId(), getUserId(), isSuperAdmin()
│   ├── AuthController      ← login, refresh, logout, logoutAll, me
│   ├── GymController       ← CRUD gimnasios + settings + logo
│   ├── BranchController    ← CRUD sucursales
│   ├── UserController      ← CRUD usuarios
│   ├── RoleController      ← CRUD roles + sync permisos
│   ├── MenuController      ← CRUD menús, árbol, menús filtrados por usuario
│   ├── AuditController     ← listado de logs con filtros
│   └── DashboardController ← estadísticas y actividad reciente
│
├── Services/               ← lógica de negocio; los controladores solo delegan aquí
│   ├── AuthService         ← login con bloqueo (5 intentos → 15 min), refresh token rotation, logout
│   ├── GymService          ← CRUD gyms, settings key-value
│   ├── BranchService
│   ├── UserService
│   └── RoleService         ← CRUD roles, sync permisos
│
├── Models/                 ← extienden Model de CI4, useSoftDeletes donde aplica
│   ├── GymModel, BranchModel, UserModel (hashPassword en before insert/update)
│   ├── RoleModel, PermissionModel, RolePermissionModel
│   ├── RefreshTokenModel   ← findValid(), revokeAllByUser()
│   ├── MenuModel           ← getTree() construye árbol recursivo
│   ├── GymSettingModel     ← getByGym(), setSetting()
│   └── AuditLogModel       ← log() agrega IP y user-agent automáticamente
│
├── Filters/
│   ├── JWTAuthFilter       ← valida Bearer token → llama UserContext::set($user)
│   └── CorsFilter          ← agrega headers CORS, responde OPTIONS con 200
│
├── Libraries/
│   ├── JWTHandler          ← generateAccessToken(), generateRefreshToken(), decode()
│   ├── ApiResponse         ← success(), error(), paginated() — respuesta estándar
│   └── UserContext         ← clase estática, almacena el usuario autenticado en el request actual
│
└── Database/
    ├── Migrations/         ← 10 migraciones en orden 000001–000010
    └── Seeds/
        ├── MainSeeder      ← orquesta todos los seeders
        ├── GymSeeder       ← 1 gimnasio demo
        ├── RoleSeeder      ← 5 roles (super_admin, admin, recepcionista, entrenador, supervisor)
        ├── PermissionSeeder← 24 permisos + asigna todos al rol admin (id=2)
        ├── UserSeeder      ← superadmin@gyminix.com y admin@demo.com
        └── MenuSeeder      ← 12 menús jerárquicos
```

---

## Esquema de base de datos

| Tabla              | Descripción                                         |
|--------------------|-----------------------------------------------------|
| `gyms`             | Gimnasios; aislamiento principal por `gym_id`       |
| `branches`         | Sucursales de cada gimnasio; `schedule` JSON por día|
| `roles`            | Roles; `is_system=1` → no eliminables               |
| `permissions`      | Permisos: `module.action` (slug único)              |
| `role_permissions` | Pivote rol ↔ permiso                                |
| `users`            | Usuarios; `is_super_admin` omite todos los checks   |
| `refresh_tokens`   | Tokens de refresco; `revoked=1` al usarse (rotation)|
| `menus`            | Menús jerárquicos; `permission_slug` filtra acceso  |
| `gym_settings`     | Configuración key-value por gimnasio y grupo        |
| `audit_logs`       | Log inmutable de todas las acciones con IP y UA     |

Todas las tablas operativas tienen `deleted_at` (soft delete) excepto `permissions`, `role_permissions`, `refresh_tokens`, `menus`, `gym_settings` y `audit_logs`.

---

## Rutas API (`/api/v1`)

### Públicas (sin JWT)
| Método | Ruta              | Acción                    |
|--------|-------------------|---------------------------|
| POST   | `/auth/login`     | Login → access + refresh token |
| POST   | `/auth/refresh`   | Renovar tokens            |

### Protegidas (requieren `Authorization: Bearer <token>`)
| Método | Ruta                                  | Controlador / Método            |
|--------|---------------------------------------|---------------------------------|
| GET    | `/auth/me`                            | AuthController::me              |
| POST   | `/auth/logout`                        | AuthController::logout          |
| POST   | `/auth/logout-all`                    | AuthController::logoutAll       |
| GET    | `/dashboard`                          | DashboardController::index      |
| GET/POST | `/gyms`                             | GymController::index / create   |
| GET/PUT | `/gyms/{id}`                         | GymController::show / update    |
| PATCH  | `/gyms/{id}/status`                   | GymController::toggleStatus     |
| GET/POST | `/gyms/{id}/settings`               | GymController::getSettings / saveSettings |
| POST   | `/gyms/{id}/logo`                     | GymController::uploadLogo (multipart) |
| GET/POST | `/branches`                         | BranchController::index / create |
| GET/PUT/DELETE | `/branches/{id}`              | BranchController CRUD           |
| PATCH  | `/branches/{id}/status`               | BranchController::toggleStatus  |
| GET/POST | `/users`                            | UserController CRUD             |
| GET/PUT/DELETE | `/users/{id}`                 | UserController CRUD             |
| PATCH  | `/users/{id}/status`                  | UserController::toggleStatus    |
| GET/POST | `/roles`                            | RoleController::index / create  |
| PUT/DELETE | `/roles/{id}`                     | RoleController CRUD             |
| GET    | `/permissions`                        | RoleController::permissions     |
| GET    | `/roles/{id}/permissions`             | RoleController::getRolePermissions |
| POST   | `/roles/{id}/permissions/sync`        | RoleController::syncPermissions |
| GET    | `/menus` / `/menus/tree` / `/menus/user` | MenuController               |
| POST/PUT/DELETE | `/menus` / `/menus/{id}`     | MenuController CRUD             |
| GET    | `/audit-logs`                         | AuditController::index          |

---

## Convenciones

### Controladores
- Siempre extienden `BaseApiController`.
- Usan `$this->ok()`, `$this->error()`, `$this->paginate()` para responder.
- No contienen lógica de negocio — delegan al Service correspondiente.
- El usuario autenticado se obtiene con `$this->getAuthUser()`, `$this->getUserId()`, `$this->getGymId()`, `$this->isSuperAdmin()`.

### Servicios
- Reciben datos ya validados del controlador.
- Siempre llaman `$this->auditModel->log(...)` al final de cada operación mutante.
- Devuelven arrays (nunca objetos Model).

### Modelos
- Usan `useSoftDeletes = true` cuando la entidad debe ocultarse sin borrarse.
- `UserModel` hashea la contraseña automáticamente via `beforeInsert` / `beforeUpdate`.
- Nunca exponer el campo `password` en respuestas — unsetear antes de devolver.

### Auditoría
Llamar siempre así desde un Service:
```php
$this->auditModel->log([
    'user_id'     => $actorId,
    'gym_id'      => $gymId,       // opcional en operaciones globales
    'module'      => 'users',
    'action'      => 'create',
    'record_id'   => $newId,
    'old_data'    => json_encode($before),  // solo en updates/deletes
    'new_data'    => json_encode($after),
    'description' => 'Texto legible',
]);
```

### Multi-Gimnasio
- Todo recurso operativo lleva `gym_id` en la tabla.
- En listados, filtrar **siempre** por `gym_id` a menos que `isSuperAdmin()` sea `true`.
- El `gym_id` del usuario autenticado se obtiene de `UserContext::get()['gym_id']`.

---

## Añadir un nuevo módulo (checklist)

1. Crear migración: `php spark make:migration CreateXxxTable`
2. Crear Model en `app/Models/`
3. Crear Service en `app/Services/`
4. Crear Controller en `app/Controllers/Api/`, extendiendo `BaseApiController`
5. Registrar rutas en `app/Config/Routes.php` dentro del grupo `jwt`
6. Agregar permisos al seeder o insertar directamente en `permissions`
7. Agregar entradas en `menus` con el `permission_slug` correcto
8. Ejecutar migración y seed en ambiente local
