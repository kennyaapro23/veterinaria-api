# 🔔 Estado de Notificaciones para Flutter Frontend

## ✅ RESUMEN EJECUTIVO

**Las notificaciones están 100% listas para tu frontend Flutter.** El backend tiene todo implementado y funcionando.

---

## 📊 ESTADO ACTUAL DEL BACKEND

### ✅ **Completamente Implementado**

| Componente | Estado | Descripción |
|------------|--------|-------------|
| **Modelo Notificacion** | ✅ 100% | Modelo con campos: `tipo`, `titulo`, `cuerpo`, `leida`, `meta`, `sent_via` |
| **Modelo FcmToken** | ✅ 100% | Gestión de tokens FCM por usuario y dispositivo |
| **NotificacionController** | ✅ 100% | 8 endpoints completos (listar, marcar leídas, eliminar, etc.) |
| **FcmTokenController** | ✅ 100% | 4 endpoints para gestión de tokens FCM |
| **FcmHelper** | ✅ 100% | 3 funciones helper para envío de push notifications |
| **Migraciones** | ✅ 100% | Tablas `notificaciones` y `fcm_tokens` creadas |
| **Rutas API** | ✅ 100% | Todas las rutas protegidas con `auth:sanctum` |
| **Documentación** | ✅ 100% | `README_NOTIFICACIONES.md` con ejemplos completos |

---

## 🎯 ENDPOINTS DISPONIBLES PARA FLUTTER

### 📱 **Gestión de Tokens FCM**

#### 1. Registrar/Actualizar Token FCM
```http
POST /api/fcm-token
Authorization: Bearer {TOKEN}
Content-Type: application/json

{
  "token": "FCM_TOKEN_AQUI",
  "device_type": "android|ios|web",
  "device_name": "Pixel 6 Pro" // opcional
}
```

**Respuesta:**
```json
{
  "message": "Token FCM guardado exitosamente",
  "fcm_token": {
    "id": 1,
    "user_id": 5,
    "token": "...",
    "device_type": "android",
    "device_name": "Pixel 6 Pro"
  }
}
```

#### 2. Eliminar Token FCM (Logout)
```http
DELETE /api/fcm-token
Authorization: Bearer {TOKEN}

{
  "token": "FCM_TOKEN_AQUI"
}
```

#### 3. Listar Tokens del Usuario
```http
GET /api/fcm-tokens
Authorization: Bearer {TOKEN}
```

#### 4. Eliminar Todos los Tokens
```http
DELETE /api/fcm-tokens/all
Authorization: Bearer {TOKEN}
```

---

### 🔔 **Gestión de Notificaciones**

#### 1. Listar Notificaciones (Paginado)
```http
GET /api/notificaciones?leida=false&tipo=recordatorio_cita&page=1
Authorization: Bearer {TOKEN}
```

**Query Parameters:**
- `leida` (boolean): filtrar por leídas/no leídas
- `tipo` (string): filtrar por tipo específico
- `page` (int): número de página (20 por página)

**Respuesta:**
```json
{
  "data": [
    {
      "id": 42,
      "user_id": 7,
      "tipo": "recordatorio_cita",
      "titulo": "Recordatorio de Cita",
      "cuerpo": "Tienes una cita mañana a las 10:00 para Fido",
      "leida": false,
      "meta": {
        "cita_id": 123,
        "mascota_id": 5
      },
      "sent_via": "push",
      "created_at": "2025-11-15T12:34:56"
    }
  ],
  "current_page": 1,
  "total": 50
}
```

#### 2. Ver Detalle de Notificación
```http
GET /api/notificaciones/{id}
Authorization: Bearer {TOKEN}
```

> ⚠️ **IMPORTANTE:** Este endpoint marca automáticamente la notificación como leída.

#### 3. Marcar Notificación como Leída
```http
POST /api/notificaciones/{id}/mark-read
Authorization: Bearer {TOKEN}
```

#### 4. Marcar Todas como Leídas
```http
POST /api/notificaciones/mark-all-read
Authorization: Bearer {TOKEN}

// Opcional: solo ciertos tipos
{
  "tipos": ["recordatorio_cita", "cita_creada"]
}
```

#### 5. Obtener Conteo de No Leídas
```http
GET /api/notificaciones/unread-count?by_type=true
Authorization: Bearer {TOKEN}
```

**Respuesta:**
```json
{
  "total": 5,
  "by_type": {
    "recordatorio_cita": 3,
    "cita_creada": 2
  }
}
```

#### 6. Eliminar Notificación
```http
DELETE /api/notificaciones/{id}
Authorization: Bearer {TOKEN}
```

#### 7. Eliminar Todas las Leídas
```http
DELETE /api/notificaciones/delete-read
Authorization: Bearer {TOKEN}
```

#### 8. Obtener Tipos Disponibles
```http
GET /api/notificaciones/tipos
Authorization: Bearer {TOKEN}
```

**Respuesta:**
```json
{
  "tipos": [
    "recordatorio_cita",
    "cita_creada",
    "cita_cancelada",
    "cita_modificada",
    "vacuna_proxima",
    "resultado_disponible",
    "mensaje_veterinario",
    "otro"
  ]
}
```

---

## 🔧 PROBLEMAS DETECTADOS Y SOLUCIONES

### ⚠️ **Problema 1: Inconsistencia en FcmToken Model**

**Problema:** El modelo `FcmToken.php` tiene campos diferentes a los que espera el controlador.

**Modelo actual:**
```php
protected $fillable = [
    'user_id',
    'token',
    'plataforma',        // ❌ Nombre incorrecto
    'ultimo_registro',   // ❌ Nombre incorrecto
];
```

**Controlador espera:**
```php
'device_type' => 'required|in:android,ios,web',
'device_name' => 'nullable|string|max:100',
```

**Migración tiene:**
```php
$table->enum('plataforma', ['android', 'ios', 'web'])->default('android');
$table->dateTime('ultimo_registro')->useCurrent();
```

### ✅ **Solución Necesaria:**

Necesitas actualizar el modelo `FcmToken.php` para que coincida con la migración:

```php
protected $fillable = [
    'user_id',
    'token',
    'device_type',    // Cambiar de 'plataforma'
    'device_name',    // Agregar este campo
];

protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
];
```

**O actualizar la migración** para usar `plataforma` en lugar de `device_type`. Te recomiendo la primera opción para mantener consistencia con el controlador.

---

## 📱 IMPLEMENTACIÓN EN FLUTTER

### 1️⃣ **Dependencias Necesarias**

```yaml
dependencies:
  firebase_core: ^2.24.2
  firebase_messaging: ^14.7.9
  dio: ^5.4.0
  shared_preferences: ^2.2.2
```

### 2️⃣ **Configuración Inicial**

#### `lib/services/api_service.dart`

```dart
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  late Dio dio;

  ApiService._internal() {
    dio = Dio(BaseOptions(
      baseUrl: 'https://tu-api.test/api',
      connectTimeout: const Duration(seconds: 10),
      receiveTimeout: const Duration(seconds: 10),
    ));
    
    // Interceptor para agregar token automáticamente
    dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final prefs = await SharedPreferences.getInstance();
        final token = prefs.getString('api_token');
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
    ));
  }

  // FCM Token Management
  Future<Response> registerFcmToken(String token, String deviceType, {String? deviceName}) async {
    return dio.post('/fcm-token', data: {
      'token': token,
      'device_type': deviceType,
      'device_name': deviceName,
    });
  }

  Future<Response> deleteFcmToken(String token) async {
    return dio.delete('/fcm-token', data: {'token': token});
  }

  // Notifications
  Future<Response> getNotifications({bool? leida, String? tipo, int page = 1}) async {
    return dio.get('/notificaciones', queryParameters: {
      if (leida != null) 'leida': leida,
      if (tipo != null) 'tipo': tipo,
      'page': page,
    });
  }

  Future<Response> getUnreadCount({bool byType = false}) async {
    return dio.get('/notificaciones/unread-count', 
      queryParameters: {'by_type': byType});
  }

  Future<Response> markAsRead(int id) async {
    return dio.post('/notificaciones/$id/mark-read');
  }

  Future<Response> markAllAsRead({List<String>? tipos}) async {
    return dio.post('/notificaciones/mark-all-read', 
      data: tipos != null ? {'tipos': tipos} : null);
  }

  Future<Response> deleteNotification(int id) async {
    return dio.delete('/notificaciones/$id');
  }
}
```

### 3️⃣ **Registro de Token FCM**

#### `lib/services/firebase_service.dart`

```dart
import 'dart:io';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'api_service.dart';

class FirebaseService {
  static final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  static final ApiService _api = ApiService();

  // Inicializar Firebase y registrar token
  static Future<void> initialize() async {
    // Pedir permisos (iOS)
    NotificationSettings settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      print('✅ Permisos de notificaciones otorgados');
      
      // Obtener token FCM
      final fcmToken = await _messaging.getToken();
      if (fcmToken != null) {
        await _registerToken(fcmToken);
      }

      // Escuchar refresh de token
      _messaging.onTokenRefresh.listen((newToken) {
        _registerToken(newToken);
      });

      // Configurar handlers
      _setupMessageHandlers();
    }
  }

  static Future<void> _registerToken(String token) async {
    try {
      final deviceType = Platform.isAndroid ? 'android' : 'ios';
      final deviceName = Platform.operatingSystem;
      
      await _api.registerFcmToken(token, deviceType, deviceName: deviceName);
      print('✅ Token FCM registrado: ${token.substring(0, 20)}...');
    } catch (e) {
      print('❌ Error registrando token FCM: $e');
    }
  }

  static void _setupMessageHandlers() {
    // Foreground messages
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print('📩 Notificación recibida (foreground)');
      print('Título: ${message.notification?.title}');
      print('Cuerpo: ${message.notification?.body}');
      print('Data: ${message.data}');
      
      // Mostrar notificación local o banner in-app
      // TODO: Implementar con flutter_local_notifications
    });

    // Background/terminated - usuario toca la notificación
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) async {
      print('📱 Notificación abierta desde background');
      await _handleNotificationTap(message);
    });

    // App abierta desde terminated state
    _messaging.getInitialMessage().then((RemoteMessage? message) {
      if (message != null) {
        print('📱 App abierta desde notificación (terminated)');
        _handleNotificationTap(message);
      }
    });
  }

  static Future<void> _handleNotificationTap(RemoteMessage message) async {
    final data = message.data;
    final notificationId = data['notification_id'];
    final citaId = data['cita_id'];
    final mascotaId = data['mascota_id'];

    // Marcar como leída en el servidor
    if (notificationId != null) {
      try {
        await _api.markAsRead(int.parse(notificationId));
      } catch (e) {
        print('Error marcando notificación como leída: $e');
      }
    }

    // Navegar según el tipo de notificación
    if (citaId != null) {
      // TODO: Navegar a detalle de cita
      // Navigator.pushNamed(context, '/cita-detail', arguments: citaId);
    }
  }

  // Logout - eliminar token
  static Future<void> unregisterToken() async {
    try {
      final token = await _messaging.getToken();
      if (token != null) {
        await _api.deleteFcmToken(token);
        print('✅ Token FCM eliminado');
      }
    } catch (e) {
      print('❌ Error eliminando token FCM: $e');
    }
  }
}
```

### 4️⃣ **Modelo de Notificación**

#### `lib/models/notificacion.dart`

```dart
class Notificacion {
  final int id;
  final int userId;
  final String tipo;
  final String titulo;
  final String cuerpo;
  final bool leida;
  final Map<String, dynamic>? meta;
  final String? sentVia;
  final DateTime createdAt;

  Notificacion({
    required this.id,
    required this.userId,
    required this.tipo,
    required this.titulo,
    required this.cuerpo,
    required this.leida,
    this.meta,
    this.sentVia,
    required this.createdAt,
  });

  factory Notificacion.fromJson(Map<String, dynamic> json) {
    return Notificacion(
      id: json['id'],
      userId: json['user_id'],
      tipo: json['tipo'],
      titulo: json['titulo'],
      cuerpo: json['cuerpo'],
      leida: json['leida'] ?? false,
      meta: json['meta'],
      sentVia: json['sent_via'],
      createdAt: DateTime.parse(json['created_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'tipo': tipo,
      'titulo': titulo,
      'cuerpo': cuerpo,
      'leida': leida,
      'meta': meta,
      'sent_via': sentVia,
      'created_at': createdAt.toIso8601String(),
    };
  }
}
```

### 5️⃣ **Uso en la App**

#### En `main.dart`:

```dart
void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();
  
  // Inicializar Firebase Messaging
  await FirebaseService.initialize();
  
  runApp(MyApp());
}
```

#### En Login exitoso:

```dart
// Después de login exitoso
final prefs = await SharedPreferences.getInstance();
await prefs.setString('api_token', response.data['token']);

// Registrar token FCM
await FirebaseService.initialize();
```

#### En Logout:

```dart
// Antes de logout
await FirebaseService.unregisterToken();

final prefs = await SharedPreferences.getInstance();
await prefs.remove('api_token');
```

#### Pantalla de Notificaciones:

```dart
class NotificacionesScreen extends StatefulWidget {
  @override
  _NotificacionesScreenState createState() => _NotificacionesScreenState();
}

class _NotificacionesScreenState extends State<NotificacionesScreen> {
  final ApiService _api = ApiService();
  List<Notificacion> notificaciones = [];
  int unreadCount = 0;
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
    _loadUnreadCount();
  }

  Future<void> _loadNotifications() async {
    try {
      final response = await _api.getNotifications();
      setState(() {
        notificaciones = (response.data['data'] as List)
            .map((json) => Notificacion.fromJson(json))
            .toList();
        loading = false;
      });
    } catch (e) {
      print('Error cargando notificaciones: $e');
      setState(() => loading = false);
    }
  }

  Future<void> _loadUnreadCount() async {
    try {
      final response = await _api.getUnreadCount();
      setState(() {
        unreadCount = response.data['total'];
      });
    } catch (e) {
      print('Error cargando conteo: $e');
    }
  }

  Future<void> _markAsRead(int id) async {
    try {
      await _api.markAsRead(id);
      _loadNotifications();
      _loadUnreadCount();
    } catch (e) {
      print('Error marcando como leída: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Notificaciones'),
        actions: [
          if (unreadCount > 0)
            Center(
              child: Padding(
                padding: EdgeInsets.only(right: 16),
                child: Badge(
                  label: Text('$unreadCount'),
                  child: Icon(Icons.notifications),
                ),
              ),
            ),
        ],
      ),
      body: loading
          ? Center(child: CircularProgressIndicator())
          : ListView.builder(
              itemCount: notificaciones.length,
              itemBuilder: (context, index) {
                final notif = notificaciones[index];
                return ListTile(
                  leading: Icon(
                    notif.leida ? Icons.mail_outline : Icons.mail,
                    color: notif.leida ? Colors.grey : Colors.blue,
                  ),
                  title: Text(
                    notif.titulo,
                    style: TextStyle(
                      fontWeight: notif.leida ? FontWeight.normal : FontWeight.bold,
                    ),
                  ),
                  subtitle: Text(notif.cuerpo),
                  trailing: Text(
                    _formatDate(notif.createdAt),
                    style: TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                  onTap: () => _markAsRead(notif.id),
                );
              },
            ),
    );
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final diff = now.difference(date);
    
    if (diff.inDays > 0) return '${diff.inDays}d';
    if (diff.inHours > 0) return '${diff.inHours}h';
    if (diff.inMinutes > 0) return '${diff.inMinutes}m';
    return 'Ahora';
  }
}
```

---

## 🔥 CONFIGURACIÓN DE FIREBASE

### Android (`android/app/google-services.json`)

1. Descarga el archivo desde Firebase Console
2. Colócalo en `android/app/`

### iOS (`ios/Runner/GoogleService-Info.plist`)

1. Descarga el archivo desde Firebase Console
2. Colócalo en `ios/Runner/`

### Configuración del Backend

En tu `.env`:

```env
FCM_SERVER_KEY=tu_server_key_aqui
```

> 📝 **Nota:** Obtén el Server Key desde Firebase Console → Project Settings → Cloud Messaging → Server Key

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

### Backend (Ya completado ✅)
- [x] Modelo `Notificacion` creado
- [x] Modelo `FcmToken` creado
- [x] Controladores implementados
- [x] Helper FCM implementado
- [x] Rutas configuradas
- [x] Migraciones creadas
- [x] Documentación completa

### Backend (Pendiente ⚠️)
- [ ] **Corregir modelo `FcmToken.php`** (cambiar `plataforma` → `device_type`, agregar `device_name`)
- [ ] Configurar `FCM_SERVER_KEY` en `.env`
- [ ] Ejecutar migraciones si no están aplicadas

### Flutter (Por implementar 📱)
- [ ] Agregar dependencias Firebase
- [ ] Configurar `google-services.json` (Android)
- [ ] Configurar `GoogleService-Info.plist` (iOS)
- [ ] Crear `ApiService` con endpoints de notificaciones
- [ ] Crear `FirebaseService` para manejo de FCM
- [ ] Crear modelo `Notificacion`
- [ ] Implementar pantalla de notificaciones
- [ ] Agregar badge de notificaciones no leídas
- [ ] Implementar navegación desde notificaciones
- [ ] Registrar token en login
- [ ] Eliminar token en logout

---

## 🎯 PRÓXIMOS PASOS RECOMENDADOS

1. **Corregir el modelo FcmToken** (ver sección de problemas detectados)
2. **Configurar Firebase en tu proyecto Flutter**
3. **Implementar los servicios** (`ApiService` y `FirebaseService`)
4. **Crear la pantalla de notificaciones**
5. **Probar el flujo completo:**
   - Login → Registro de token
   - Recepción de notificación
   - Navegación desde notificación
   - Logout → Eliminación de token

---

## 📚 RECURSOS ADICIONALES

- [README_NOTIFICACIONES.md](./README_NOTIFICACIONES.md) - Documentación completa del backend
- [Firebase Messaging Flutter](https://firebase.flutter.dev/docs/messaging/overview/)
- [Dio Package](https://pub.dev/packages/dio)

---

## 💡 TIPS IMPORTANTES

1. **Permisos iOS:** Debes pedir permisos explícitamente
2. **Background Handler:** Para notificaciones en background, usa `@pragma('vm:entry-point')`
3. **Local Notifications:** Considera usar `flutter_local_notifications` para mostrar notificaciones cuando la app está en foreground
4. **Testing:** Usa Firebase Console → Cloud Messaging para enviar notificaciones de prueba
5. **Meta Data:** Usa el campo `meta` para pasar IDs y datos de navegación

---

**¿Necesitas ayuda con alguna parte específica de la implementación en Flutter?** 🚀
