# Notificaciones — API y guía para Flutter

Resumen
- Este documento describe cómo consumir y enviar notificaciones desde el frontend (Flutter) hacia el backend Laravel de VetCareApp.
- Cubre endpoints disponibles, payloads, ejemplo curl y snippets recomendados en Flutter (Dio + SharedPreferences + firebase_messaging).

Requisitos del backend
- Endpoints protegidos con `auth:sanctum` — usar header `Authorization: Bearer <TOKEN>` en todas las llamadas.
- Variable de entorno `FCM_SERVER_KEY` debe estar configurada para que el servidor pueda enviar push reales.
- Modelos/Controladores relevantes:
  - `Notificacion` (`notificaciones`)
  - `FcmToken` y `FcmTokenController` (`POST /api/fcm-token`, `DELETE /api/fcm-token`, `GET /api/fcm-tokens`, `DELETE /api/fcm-tokens/all`)
  - Helper `app/Helpers/FcmHelper.php` para envíos push (usa HTTP a FCM si `FCM_SERVER_KEY` está definido).

Rutas principales (Resumen)
- GET `/api/notificaciones` — Listar notificaciones (paginado). Query: `leida`, `tipo`, `page`.
- GET `/api/notificaciones/tipos` — Devuelve tipos válidos.
- GET `/api/notificaciones/unread-count` — Conteo de no leídas. Query opcional: `by_type=true`.
- GET `/api/notificaciones/{id}` — Ver detalle (marca como leída automáticamente si no lo estaba).
- POST `/api/notificaciones/{id}/mark-read` — Marcar una notificación como leída.
- POST `/api/notificaciones/mark-all-read` — Marcar todas (opcional body `{ "tipos": ["recordatorio_cita"] }`).
- DELETE `/api/notificaciones/{id}` — Eliminar una notificación.
- DELETE `/api/notificaciones/delete-read` — Eliminar todas las leídas.

Endpoints FCM tokens
- POST `/api/fcm-token` — Registrar/actualizar token FCM
  - Body JSON: `{ "token": "<FCM_TOKEN>", "device_type": "android|ios|web", "device_name": "optional" }`
- DELETE `/api/fcm-token` — Eliminar token (body `{ "token": "<FCM_TOKEN>" }`).
- GET `/api/fcm-tokens` — Listar tokens del usuario.
- DELETE `/api/fcm-tokens/all` — Eliminar todos los tokens del usuario.

Formato de una notificación (respuesta típica)
```
{
  "id": 42,
  "user_id": 7,
  "tipo": "recordatorio_cita",
  "titulo": "Recordatorio de Cita",
  "cuerpo": "Tienes una cita mañana a las 10:00 para Fido",
  "leida": false,
  "meta": { "cita_id": 123, "mascota_id": 5 },
  "sent_via": "push",
  "created_at": "2025-11-15T12:34:56"
}
```
- `meta` es un objeto JSON libre usado para pasar IDs o contexto (p. ej. `cita_id`). El frontend debe leer `meta` para navegación o acciones.

Ejemplos curl
- Listar notificaciones
```bash
curl -H "Authorization: Bearer <TOKEN>" "https://tu-api.test/api/notificaciones"
```
- Obtener conteo no leídas
```bash
curl -H "Authorization: Bearer <TOKEN>" "https://tu-api.test/api/notificaciones/unread-count"
```
- Registrar FCM token
```bash
curl -X POST -H "Authorization: Bearer <TOKEN>" -H "Content-Type: application/json" \
  -d '{"token":"<FCM_TOKEN>","device_type":"android","device_name":"KennyPhone"}' \
  "https://tu-api.test/api/fcm-token"
```
- Marcar como leída
```bash
curl -X POST -H "Authorization: Bearer <TOKEN>" "https://tu-api.test/api/notificaciones/42/mark-read"
```

Flutter: dependencias recomendadas
- `firebase_core`
- `firebase_messaging`
- `dio`
- `shared_preferences`

Registro de token FCM en Flutter (recomendado)
- Lógica general:
  1. En el `login` exitoso: guardar `Bearer token` en `SharedPreferences` y configurar `Dio` con interceptor `Authorization`.
  2. Conseguir `fcmToken = await FirebaseMessaging.instance.getToken()` y enviarlo a `POST /api/fcm-token`.
  3. Escuchar `FirebaseMessaging.instance.onTokenRefresh` y volver a enviar si cambia.

Ejemplo simplificado: ApiService (Dio) y registro de token
```dart
// ApiService.dart (simplified)
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  late Dio dio;

  ApiService._internal() {
    dio = Dio(BaseOptions(baseUrl: 'https://tu-api.test/api'));
    dio.interceptors.add(InterceptorsWrapper(onRequest: (options, handler) async {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('api_token');
      if (token != null) {
        options.headers['Authorization'] = 'Bearer $token';
      }
      return handler.next(options);
    }));
  }

  Future<Response> postFcmToken(String token, String deviceType, {String? deviceName}) async {
    return dio.post('/fcm-token', data: {
      'token': token,
      'device_type': deviceType,
      'device_name': deviceName
    });
  }
}
```

Registro y refresh de FCM token (snippet)
```dart
// en tu init o después del login
FirebaseMessaging messaging = FirebaseMessaging.instance;
final fcmToken = await messaging.getToken();
if (fcmToken != null) {
  await ApiService().postFcmToken(fcmToken, Platform.isAndroid ? 'android' : 'ios', deviceName: Platform.operatingSystem);
}

// escuchar refresh
FirebaseMessaging.instance.onTokenRefresh.listen((newToken) async {
  if (newToken != null) {
    await ApiService().postFcmToken(newToken, Platform.isAndroid ? 'android' : 'ios');
  }
});
```

Manejo de notificaciones recibidas
- Foreground: usar `FirebaseMessaging.onMessage.listen` para mostrar un diálogo/in-app banner.
- Background / terminated: usar `FirebaseMessaging.onMessageOpenedApp` para navegar cuando el usuario toca la notificación.
- Siempre revisar `message.data` para encontrar `cita_id` u otra clave de navegación.
- Al abrir la notificación en la app, llamar `GET /api/notificaciones/{id}` para obtener detalle y marcarla como leída en el servidor (esa ruta hace la marca automática al leer).

Ejemplo de manejo (simplified)
```dart
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  final data = message.data;
  // Mostrar in-app banner con message.notification.title / body
});

FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) async {
  final data = message.data; // p.ej. { 'cita_id': '123', 'type': 'recordatorio_cita', 'notification_id': '42' }
  final notificacionId = data['notification_id'];
  if (notificacionId != null) {
    final resp = await ApiService().dio.get('/notificaciones/$notificacionId');
    final detalle = resp.data;
    // navegar según detalle.meta.cita_id
  }
});
```

Badge y centro de notificaciones
- Para mostrar el número de notificaciones no leídas, llamar periódicamente o en resume:
```
GET /api/notificaciones/unread-count
```
- Mostrar `total` como badge en la pestaña o en el icono de la app.

Buenas prácticas
- Registrar el token siempre después del login y cuando el token FCM se refresque.
- En logout, llamar `DELETE /api/fcm-token` con el token actual para desasociarlo.
- Usar la respuesta `meta` para navegación y evitar extra llamadas si ya tienes la info en la carga inicial.
- Para entornos de prueba: si no hay `FCM_SERVER_KEY`, el backend guardará la notificación en BD pero puede solo loguear el envío.

Errores comunes y soluciones
- 401 Unauthorized: verifica que `Authorization: Bearer <TOKEN>` esté presente y válido. Revisa que el token no haya expirado.
- FCM token null: asegúrate de configurar `firebase_core` y pedir permisos en iOS. En Android, verifica que el `google-services.json` esté configurado.
- Sin pushes reales: verifica `FCM_SERVER_KEY` en `.env` y que el helper `sendPushNotification` esté siendo llamado (logs en `storage/logs/laravel.log`).

Siguientes pasos sugeridos
- ¿Quieres que agregue el archivo `lib/services/api_service.dart` completo con la lógica de registro de token y manejo de headers? Puedo generarlo y adaptarlo a tu estructura de Flutter.
- ¿Quieres que convierta `EnviarRecordatoriosCitas` para usar el helper `sendPushToUser()` (centralizado) y habilite envíos reales si `FCM_SERVER_KEY` está presente? Puedo aplicar ese cambio en backend.

---
Archivo generado: `README_NOTIFICACIONES.md` (raíz del proyecto)

Si quieres, ahora genero el archivo `lib/services/api_service.dart` con el ejemplo completo para Flutter.```