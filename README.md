# 🐾 VetCare API - Backend Laravel

API REST completa para sistema de gestión veterinaria con autenticación Firebase y notificaciones push.

---

## 🚀 Stack Tecnológico

- **Framework:** Laravel 12.x
- **Base de Datos:** MySQL
- **Autenticación:** Laravel Sanctum + Firebase Auth (Google Sign-In)
- **Notificaciones:** Firebase Cloud Messaging (FCM)
- **Permisos:** Spatie Laravel Permission

---

## 📦 Instalación Rápida

```bash
# 1. Instalar dependencias
composer install

# 2. Configurar entorno
cp .env.example .env

# 3. Generar key
php artisan key:generate

# 4. Configurar base de datos en .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=veterinaria
DB_USERNAME=root
DB_PASSWORD=

# 5. Ejecutar migraciones
php artisan migrate

# 6. Ejecutar seeders (3 usuarios + 2 mascotas)
php artisan db:seed --class=UsersWithDataSeeder
```

---

## 🔥 Configuración Firebase

### 1. Copiar service-account.json
Descarga desde Firebase Console → Project Settings → Service accounts
```bash
# Colocar en:
storage/firebase/service-account.json
```

### 2. Configurar .env
```env
FIREBASE_CREDENTIALS=storage/firebase/service-account.json
FIREBASE_DATABASE_URL=https://tu-proyecto.firebaseio.com
FCM_SERVER_KEY=tu_fcm_server_key_aqui
```

---

## 🏃 Ejecutar Servidor

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**URL:** http://127.0.0.1:8000

---

## 📱 Integración Flutter

**📖 Guía completa:** `FLUTTER_INTEGRATION_COMPLETE.md`

### Configuración Emulador
```bash
# Ejecutar ANTES de iniciar Flutter app
adb reverse tcp:8000 tcp:8000
```

### BaseURL en Flutter
```dart
static const String baseUrl = 'http://127.0.0.1:8000/api/';
```

---

## 🔐 Autenticación

### Métodos Soportados:

#### 1. Email/Password (Tradicional)
```bash
POST /api/auth/login
Body: {"email": "...", "password": "..."}
```

#### 2. Google Sign-In (Firebase)
```bash
POST /api/firebase/verify
Body: {"firebase_token": "..."}
```

### Usuarios de Prueba:
- **Cliente:** cliente@test.com / password
- **Veterinario:** vet@test.com / password
- **Recepción:** recepcion@test.com / password

---

## 📡 Endpoints API (64 totales)

### Autenticación
- `POST /api/auth/register` - Registro tradicional
- `POST /api/auth/login` - Login tradicional
- `POST /api/auth/logout` - Cerrar sesión
- `POST /api/firebase/verify` - Login con Google

### Recursos Principales
- `/api/clientes` - CRUD clientes
- `/api/mascotas` - CRUD mascotas
- `/api/citas` - CRUD citas
- `/api/veterinarios` - CRUD veterinarios
- `/api/servicios` - CRUD servicios
- `/api/facturas` - CRUD facturas
- `/api/historial-medico` - CRUD historial médico

### Notificaciones
- `GET /api/notificaciones` - Listar notificaciones
- `POST /api/notificaciones/{id}/mark-read` - Marcar leída
- `GET /api/notificaciones/unread-count` - Contador no leídas

### FCM Tokens
- `POST /api/fcm-token` - Registrar token
- `DELETE /api/fcm-token` - Eliminar token
- `GET /api/fcm-tokens` - Listar tokens

### Utilidades
- `GET /api/health` - Health check
- `GET /api/qr/lookup/{token}` - Buscar por QR

Ver lista completa:
```bash
php artisan route:list --path=api
```

---

## 🧪 Testing

```bash
# Ejecutar tests
php artisan test

# Health check
curl http://127.0.0.1:8000/api/health
```

---

## 🛠️ Comandos Útiles

```bash
# Ver migraciones
php artisan migrate:status

# Rollback
php artisan migrate:rollback

# Limpiar cache
php artisan cache:clear
php artisan config:clear

# Ver rutas
php artisan route:list
```

---

## 📚 Documentación

- **Integración Flutter:** `FLUTTER_INTEGRATION_COMPLETE.md`
- **API Routes:** `php artisan route:list --path=api`
- **Modelos:** `app/Models/`

---

## 🔒 Seguridad

- ✅ CORS configurado para Flutter
- ✅ Sanctum tokens con expiración
- ✅ Firebase token verification
- ✅ Rate limiting en endpoints críticos
- ✅ Validación de roles (Spatie Permission)

---

## 📝 Licencia

MIT License

---

**🐕 Desarrollado para VetCare - Sistema de Gestión Veterinaria**
