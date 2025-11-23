# Integración Flutter — Historial y Notificaciones (FCM)

Este README explica dos cambios importantes en el backend y cómo adaptarlos desde la app Flutter:

- Los campos `diagnostico` y `tratamiento` de `HistorialMedico` ahora se almacenan como JSON arrays.
- El backend usa Firebase Cloud Messaging (FCM). La app debe registrar el token FCM y enviarlo al endpoint autenticado `POST /api/fcm-tokens`.

---

## 1) Historial: `diagnostico` y `tratamiento` como arrays

Resumen: El backend guarda `diagnostico` y `tratamiento` como arrays JSON. Siempre envía/recibe arrays. Si tienes un solo elemento, envía un array con ese elemento.

Ejemplo de payload (crear/actualizar historial):

```json
{
  "cita_id": 123,
  "diagnostico": ["Otitis", "Alergia a pulgas"],
  "tratamiento": [
    { "descripcion": "Antibiótico oral 7 días", "dosis": "2 mg/kg" },
    { "descripcion": "Champú medicado semanal", "dosis": null }
  ],
  "servicios": [1, 2],
  "archivos_meta": []
}
```

Recomendación Flutter: normaliza antes de enviar.

Snippet Dart — normalizador sencillo:

```dart
List<dynamic> ensureArray(dynamic v) {
  if (v == null) return [];
  if (v is List) return v;
  return [v];
}

final body = {
  'cita_id': citaId,
  'diagnostico': ensureArray(diagnostico),
  'tratamiento': ensureArray(tratamiento),
  'servicios': serviciosIds,
  'archivos_meta': archivosMeta ?? [],
};

// Luego enviar `body` como JSON usando Dio o http
```

Notas de compatibilidad:
- Si el backend devuelve `diagnostico` o `tratamiento` como `null`, trátalo como `[]`.
- Si recibes un String por error (por ejemplo: "Otitis"), conviértelo en `["Otitis"]` con `ensureArray`.

---

## 2) Notificaciones (Firebase Cloud Messaging)

Resumen: El backend crea notificaciones y envía pushes usando la clave `FCM_SERVER_KEY`. La app debe:

1. Inicializar `firebase_messaging`.
2. Obtener el token FCM y enviarlo al backend autenticado `POST /api/fcm-tokens`.
3. Escuchar eventos `onMessage`, `onBackgroundMessage` y `onMessageOpenedApp`.

### Registro del token FCM (pasos)

1. Inicializa Firebase en `main()` (según tu setup de Flutter).
2. Solicita permisos en iOS: `requestPermission()`.
3. Obtén token y envíalo al backend (después de login, con Bearer token de tu API).

Snippet Dart (registro con Dio):

```dart
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:dio/dio.dart';

Future<void> registerFcmToken(Dio dio) async {
  final fcm = FirebaseMessaging.instance;
  final token = await fcm.getToken();
  if (token == null) return;

  // enviar token al backend (asegúrate de tener Authorization: Bearer <token>)
  await dio.post('/api/fcm-tokens', data: {'token': token});
}
```

### Manejo de mensajes

- En primer plano: `FirebaseMessaging.onMessage.listen(...)`.
- En background: registra `FirebaseMessaging.onBackgroundMessage(handler)` en top-level.
- Al abrir desde la notificación: `FirebaseMessaging.onMessageOpenedApp.listen(...)`.

Snippet de ejemplo:

```dart
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  // mostrar notificación local o actualizar UI
  final data = message.data;
  // data['type'], data['cita_id'], etc.
});

FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
  final data = message.data;
  // navegar según data['type']
});

Future<void> firebaseBackgroundHandler(RemoteMessage message) async {
  // manejar datos en background (top-level function)
}

FirebaseMessaging.onBackgroundMessage(firebaseBackgroundHandler);
```

### Ejemplo de payload que envía el backend

```json
{
  "id": 456,
  "titulo": "Cita actualizada",
  "mensaje": "Tu cita con el Dr. López fue reprogramada",
  "data": { "type": "cita", "cita_id": 123 },
  "leida": false,
  "created_at": "2025-11-22T12:34:56Z"
}
```

En `onMessage` y `onMessageOpenedApp` comprueba `message.data['type']` y `message.data['cita_id']` para decidir la navegación.

---

## 3) Conectividad en desarrollo (Android emulator / dispositivo)

- AVD (Android emulator): usa `http://10.0.2.2:8000/api/` para apuntar al servidor local.
- Alternativa AVD: ejecutar `adb reverse tcp:8000 tcp:8000` y usar `http://localhost:8000/api/` desde la app.
- Dispositivo físico: usar la IP del PC, por ejemplo `http://192.168.100.43:8000/api/`. Asegúrate de que:
  - PC y dispositivo estén en la misma red Wi‑Fi.
  - Firewall permita conexiones al proceso `php.exe` o puerto `8000`.
  - En Android dev puede ser necesario `usesCleartextTraffic=true` en `AndroidManifest.xml` o un `network_security_config.xml` temporal.

Comando `adb` útil:

```powershell
adb reverse tcp:8000 tcp:8000
```

Snippet de `AndroidManifest.xml` (dev only):

```xml
<application
  android:usesCleartextTraffic="true"
  ...>
  ...
</application>
```

---

## 4) Checklist rápida para agregar al README del proyecto Flutter

- [ ] Normalizar `diagnostico` y `tratamiento` como arrays antes de enviar.
- [ ] Registrar token FCM tras login y enviarlo a `POST /api/fcm-tokens` con Bearer.
- [ ] Implementar `onMessage`, `onBackgroundMessage`, `onMessageOpenedApp`.
- [ ] Navegar/actualizar UI según `message.data`.
- [ ] Usar `10.0.2.2` o `adb reverse` para AVD; IP de PC y firewall para dispositivo físico.

---

Si quieres, puedo:

- Generar un ejemplo de `ApiService` en Dart con interceptor para enviar Authorization header.
- Añadir código para `flutter_local_notifications` para mostrar notificaciones locales cuando la app está en primer plano.

Fin.
