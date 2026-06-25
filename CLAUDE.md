# Gyminix — Sistema Multi-Gimnasio SaaS

Plataforma web SaaS para la gestión integral de múltiples gimnasios. Arquitectura en dos capas separadas dentro de este repositorio.

## Estructura del repositorio

```
Gyminix/
├── backend/    → API REST (CodeIgniter 4 + PHP 8.2)
├── frontend/   → SPA (Vue 3 + Vite)
└── CLAUDE.md
```

## Cómo levantar el proyecto

### Prerrequisitos
- XAMPP con **Apache** y **MySQL** corriendo
- PHP 8.2+ en PATH
- Node 18+ y npm en PATH
- Composer 2+

### Base de datos
```bash
# La BD ya existe. Para recrear desde cero:
mysql -u root -e "DROP DATABASE IF EXISTS gyminix_db; CREATE DATABASE gyminix_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
cd backend
php spark migrate --all
php spark db:seed MainSeeder
```

### Backend (API)
Servido por **Apache XAMPP** — no necesita servidor adicional.
- URL: `http://localhost/Gyminix/backend/public/`
- API: `http://localhost/Gyminix/backend/public/api/v1`

### Frontend (SPA)
```bash
cd frontend
npm run dev      # → http://localhost:5173  (desarrollo)
npm run build    # → dist/  (producción)
```

## Credenciales de prueba

| Rol            | Email                       | Contraseña |
|----------------|-----------------------------|------------|
| Super Admin    | superadmin@gyminix.com      | Admin123!  |
| Admin Gimnasio | admin@demo.com              | Admin123!  |

## Módulos implementados (v1)
- Autenticación JWT con refresh token rotation
- Multi-Gimnasio (aislamiento por `gym_id`)
- Sucursales con horarios por día
- Usuarios con roles
- Roles y permisos RBAC dinámico
- Menús dinámicos filtrados por permiso
- Configuración del gimnasio (key-value por grupo)
- Auditoría completa de acciones

## Módulos pendientes (v2 — NO implementar aún)
Membresías · Clientes · Asistencias · Pagos · Rutinas · Inventario · Ventas · Facturación

## Respuesta estándar de la API
```json
{
  "success": true,
  "message": "Mensaje legible",
  "data": {},
  "errors": []
}
```
Las rutas paginadas incluyen también `"meta": { "total", "page", "per_page", "pages" }`.
