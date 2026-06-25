# Gyminix — Frontend (Vue 3 + Vite)

SPA que consume la API REST del backend. Construida con Vue 3 Composition API, Pinia, Vue Router 4 y Bootstrap 4.

## Stack y versiones

| Herramienta           | Versión  |
|-----------------------|----------|
| Vue                   | 3.x      |
| Vite                  | 8.x      |
| Pinia                 | 2.x      |
| Vue Router            | 4.x      |
| Axios                 | 1.x      |
| Bootstrap             | 4.6      |
| vue-toastification    | next (v2)|

## Comandos

```bash
# Ejecutar desde: c:\xampp\htdocs\Gyminix\frontend\

npm run dev      # servidor de desarrollo → http://localhost:5173
npm run build    # compilar para producción → dist/
npm run preview  # previsualizar build
```

El proxy de Vite (`vite.config.js`) redirige `/api/*` al backend en `http://localhost/Gyminix/backend/public`.

---

## Estructura de directorios

```
src/
├── main.js                 ← bootstrap: Pinia + Router + Toast + Bootstrap CSS
├── App.vue                 ← raíz mínima: solo <RouterView />
│
├── assets/
│   └── main.css            ← estilos globales (sidebar, topbar, cards, tabla, modal, login, badges)
│
├── api/                    ← una función por recurso, todas usan axios.js
│   ├── axios.js            ← instancia Axios + interceptor de request (Bearer) + interceptor de respuesta (refresh silencioso en 401)
│   ├── auth.js             ← login, refresh, logout, logoutAll, me
│   ├── gyms.js             ← CRUD gyms + settings + uploadLogo
│   ├── branches.js         ← CRUD branches
│   ├── users.js            ← CRUD users
│   ├── roles.js            ← CRUD roles + permissions + syncPermissions
│   ├── menus.js            ← list, tree, userMenus, CRUD
│   └── audit.js            ← list con filtros
│
├── stores/                 ← Pinia; estado global persistido en localStorage
│   ├── auth.js             ← user, accessToken, refreshToken, isAuthenticated, isSuperAdmin, gymId; login(), logout(), fetchMe(), setSession(), clearSession()
│   ├── gym.js              ← current (gimnasio activo), settings; loadGym(), loadSettings()
│   └── menu.js             ← items (árbol de menús del usuario); fetchUserMenus()
│
├── router/
│   └── index.js            ← rutas, guard beforeEach (requiresAuth / public)
│
├── layouts/
│   └── AppLayout.vue       ← sidebar + topbar + <RouterView>; carga menús y gimnasio al montar
│
├── components/
│   └── common/
│       └── SidebarMenu.vue ← recursivo; renderiza árbol de menús con RouterLink y colapsables
│
└── modules/                ← un directorio por módulo funcional
    ├── auth/views/
    │   └── LoginView.vue
    ├── dashboard/views/
    │   └── DashboardView.vue
    ├── gyms/views/
    │   └── GymsView.vue
    ├── branches/views/
    │   └── BranchesView.vue
    ├── users/views/
    │   └── UsersView.vue
    ├── roles/views/
    │   ├── RolesView.vue        ← incluye modal de asignación de permisos por rol
    │   └── PermissionsView.vue
    ├── menus/views/
    │   └── MenusView.vue
    └── audit/views/
        └── AuditView.vue
```

---

## Autenticación y sesión

### Flujo de login
1. `LoginView` llama `authStore.login({ email, password, remember })`.
2. `AuthStore.login()` → `authApi.login()` → backend devuelve `{ access_token, refresh_token, expires_in, user }`.
3. `setSession()` persiste los tres valores en `localStorage`.
4. Router navega a `/dashboard`; `AppLayout` carga menús y gimnasio.

### Renovación automática de token (silent refresh)
`src/api/axios.js` intercepta **todos** los errores 401:
- Si hay `refresh_token` en localStorage → llama `POST /auth/refresh` una sola vez.
- Reintenta automáticamente la petición original con el nuevo token.
- Si el refresh también falla → limpia la sesión y redirige a `/login`.
- Las peticiones concurrentes se encolan (cola `failedQueue`) para no hacer múltiples refresh simultáneos.

### Guard de rutas
```js
// router/index.js
router.beforeEach((to) => {
  const auth = useAuthStore()
  if (to.meta.requiresAuth && !auth.isAuthenticated) return '/login'
  if (to.meta.public && auth.isAuthenticated)        return '/dashboard'
})
```

---

## Rutas del router

| Path          | Nombre       | Componente                              | Protegida |
|---------------|--------------|-----------------------------------------|-----------|
| `/login`      | Login        | `auth/views/LoginView`                  | No (pública) |
| `/dashboard`  | Dashboard    | `dashboard/views/DashboardView`         | Sí        |
| `/gyms`       | Gyms         | `gyms/views/GymsView`                   | Sí        |
| `/branches`   | Branches     | `branches/views/BranchesView`           | Sí        |
| `/users`      | Users        | `users/views/UsersView`                 | Sí        |
| `/roles`      | Roles        | `roles/views/RolesView`                 | Sí        |
| `/permissions`| Permissions  | `roles/views/PermissionsView`           | Sí        |
| `/menus`      | Menus        | `menus/views/MenusView`                 | Sí        |
| `/audit-logs` | AuditLogs    | `audit/views/AuditView`                 | Sí        |

---

## Menús dinámicos

1. `AppLayout` llama `menuStore.fetchUserMenus()` al montarse.
2. `menuStore` llama `GET /api/v1/menus/user` — el backend ya filtra por los permisos del rol.
3. `SidebarMenu.vue` recibe el árbol y lo renderiza recursivamente:
   - Ítems con `children` → botón colapsable.
   - Ítems hoja → `<RouterLink :to="item.route">`.

---

## Stores Pinia

### `useAuthStore` (`stores/auth.js`)
```js
// Computed
isAuthenticated  // !!accessToken && !!user
isSuperAdmin     // user.is_super_admin
gymId            // user.gym_id

// Acciones
login(credentials)   // llama API, llama setSession()
fetchMe()            // refresca datos del usuario desde /auth/me
logout()             // llama API, clearSession()
setSession(payload)  // persiste en localStorage
clearSession()       // limpia localStorage y estado
```

### `useGymStore` (`stores/gym.js`)
```js
current    // gimnasio activo (persistido en localStorage)
settings   // configuración key-value del gimnasio

loadGym(id)       // GET /gyms/{id}
loadSettings(id)  // GET /gyms/{id}/settings
clear()
```

### `useMenuStore` (`stores/menu.js`)
```js
items     // árbol de menús del usuario autenticado

fetchUserMenus()  // GET /menus/user
clear()
```

---

## Patrones de componente de módulo

Todos los módulos siguen la misma estructura `<script setup>`:

```js
// 1. Refs de estado
const items   = ref([])
const meta    = ref({})
const filters = ref({ search: '', status: '', page: 1, per_page: 15 })
const loading = ref(false)

// 2. Función load() — llama API, popula refs
async function load() { ... }

// 3. Funciones CRUD — openCreate(), openEdit(), save(), delete(), toggleStatus()
// 4. onMounted → load()
```

Cada módulo usa su propio archivo en `src/api/` — no hacer llamadas directas a `axios`.

---

## Estilos y clases utilitarias

Definidos en `src/assets/main.css`. Clases principales:

| Clase            | Descripción                              |
|------------------|------------------------------------------|
| `.sidebar`       | Panel lateral fijo (240 px)              |
| `.topbar`        | Barra superior fija (56 px)              |
| `.main-content`  | Área de contenido (margin-left + margin-top) |
| `.card-stat`     | Tarjeta de estadística con icono         |
| `.data-table`    | Contenedor de tabla con bordes redondeados |
| `.modal-overlay` | Fondo oscuro del modal                   |
| `.modal-box`     | Caja del modal (520 px por defecto)      |
| `.badge-active`  | Pill verde "Activo"                      |
| `.badge-inactive`| Pill rojo "Inactivo"                     |
| `.icon-btn`      | Botón icónico sin borde                  |
| `.filter-bar`    | Fila de controles de filtro              |
| `.login-page`    | Fondo degradado para la pantalla de login|
| `.pagination-bar`| Fila total + controles de paginación     |

Bootstrap 4 está disponible para grid (`row`, `col-*`), badges, alertas, tablas y formularios.
Font Awesome 4.7 se carga desde CDN en `index.html`.

---

## Notificaciones (Toast)

```js
import { useToast } from 'vue-toastification'
const toast = useToast()

toast.success('Guardado')
toast.error('Algo salió mal')
toast.warning('Atención')
toast.info('Información')
```

Configurado en `main.js`: posición top-right, 3.5 segundos, cierra al hacer clic.

---

## Añadir un nuevo módulo (checklist)

1. Crear directorio `src/modules/<nombre>/views/`.
2. Crear `<Nombre>View.vue` siguiendo el patrón CRUD descrito arriba.
3. Agregar la función de API en `src/api/<nombre>.js`.
4. Registrar la ruta en `src/router/index.js` dentro del bloque `requiresAuth`.
5. El backend debe insertar el ítem en la tabla `menus` con el `permission_slug` correspondiente para que aparezca en el sidebar.
