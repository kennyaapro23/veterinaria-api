**Frontend Integration Guide (Auth, Mascotas, Citas) — Instrucciones para Copilot**

Este README explica los cambios realizados en el backend y qué debes modificar en el frontend (Flutter). Incluye snippets, comandos de prueba y prompts listos para usar con GitHub Copilot o Copilot Chat para generar código Dart/Flutter.

**Resumen de cambios en backend (qué impacta al frontend)**n+
- Registro/login local: ahora se debe usar `/api/auth/register` y `/api/auth/login` para crear usuarios en la base de datos MySQL. El backend crea automáticamente el `Cliente` o `Veterinario` cuando registras con `role`.
- Flujo Firebase: `POST /api/firebase/verify` ya NO crea usuarios automáticamente. Si el usuario no existe en la BD, el endpoint devuelve 422 con un mensaje instructivo y `existing_local_by_email`.
- Auto-create Cliente: los controladores `MascotaController` y `CitaController` crearon automáticamente el perfil `Cliente` cuando un usuario con `tipo_usuario === 'cliente'` no lo tiene (evita errores 500/403 en lista/creación).
- Respuestas actualizadas: la creación/listado de `citas` incluye ahora el objeto `mascota` embebido en cada cita (ajusta parsing en el frontend).

**Qué cambiar en el frontend (lista priorizada)**
- Usar los endpoints locales para registro/login:
  - `POST /api/auth/register` (campos: `name`, `email`, `password`, `password_confirmation`, `role`)
  - `POST /api/auth/login` (campos: `email`, `password`)
- Guardar el token devuelto (Sanctum token) en almacenamiento seguro (`flutter_secure_storage`).
- En todas las requests protegidas incluir header `Authorization: Bearer <token>`.
- Mantener `POST /api/firebase/verify` solamente como verificación: si devuelve 422, guiar al usuario al registro local.
- Ajustar modelos/parsers: `Cita` debe aceptar un campo `mascota` (objeto) y mapearlo.
- UX: mostrar mensajes claros cuando `firebase/verify` devuelva `existing_local_by_email: true` (sugerir iniciar sesión localmente para vincular).

**Snippets listos para usar (Dart / Flutter)**

- Guardar token (usar `flutter_secure_storage`):
```dart
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
final storage = FlutterSecureStorage();
// guardar
await storage.write(key: 'api_token', value: token);
// leer
final token = await storage.read(key: 'api_token');
```

- Registro (usar `/api/auth/register`):
```dart
Future<void> register(String name, String email, String password, String role) async {
  final resp = await http.post(Uri.parse('$baseUrl/api/auth/register'), body: {
    'name': name,
    'email': email,
    'password': password,
    'password_confirmation': password,
    'role': role,
  });
  final data = jsonDecode(resp.body);
  if (resp.statusCode == 201) {
    final token = data['token'];
    await storage.write(key: 'api_token', value: token);
  } else {
    // manejar errores
  }
}
```

- Login (usar `/api/auth/login`):
```dart
Future<void> login(String email, String password) async {
  final resp = await http.post(Uri.parse('$baseUrl/api/auth/login'), body: {
    'email': email,
    'password': password,
  });
  final data = jsonDecode(resp.body);
  if (resp.statusCode == 200) {
    final token = data['token'] ?? data['sanctum_token'] ?? data['accessToken'];
    await storage.write(key: 'api_token', value: token);
  } else {
    // manejar error
  }
}
```

- Llamada autorizada (ej. listar mascotas):
```dart
Future<Map<String, dynamic>> fetchMascotas(int page) async {
  final token = await storage.read(key: 'api_token');
  final resp = await http.get(Uri.parse('$baseUrl/api/mascotas?page=$page'), headers: {
    'Authorization': 'Bearer $token',
  });
  if (resp.statusCode == 200) return jsonDecode(resp.body);
  throw Exception('Error: ${resp.statusCode}');
}
```

- Crear cita (asegurarse que `cita.mascota` es devuelta):
```dart
Future<Map<String, dynamic>> createCita(Map<String, dynamic> payload) async {
  final token = await storage.read(key: 'api_token');
  final resp = await http.post(Uri.parse('$baseUrl/api/citas'), headers: {
    'Authorization': 'Bearer $token',
  }, body: payload);
  final data = jsonDecode(resp.body);
  if (resp.statusCode == 201) return data['cita'];
  throw Exception(data['error'] ?? 'Error creando cita');
}
```

- Verificar con Firebase en cliente (si aún lo usas como social login):
```dart
Future<void> firebaseVerify(String firebaseIdToken) async {
  final resp = await http.post(Uri.parse('$baseUrl/api/firebase/verify'), body: {
    'firebase_token': firebaseIdToken,
    'rol': 'cliente',
  });
  final data = jsonDecode(resp.body);
  if (resp.statusCode == 200) {
    await storage.write(key: 'api_token', value: data['sanctum_token']);
  } else if (resp.statusCode == 422) {
    final existing = data['existing_local_by_email'] ?? false;
    // mostrar UI para registro o login local
  } else {
    // manejar otros errores
  }
}
```

**Prompt sugerido para Copilot / Copilot Chat**
- Prompt para generar la función de registro (Copilot):
"""
Genera una función Dart `register` que haga POST a `/api/auth/register`. La función debe:
- Recibir `name`, `email`, `password`, `role`.
- Enviar `password_confirmation` igual a `password`.
- Guardar el token recibido en `flutter_secure_storage`.
- Manejar errores y retornar un `Result` con success/error.
Usa `http` y `flutter_secure_storage`.
"""

- Prompt para mapear `Cita` del backend (Copilot):
"""
Genera una clase Dart `Cita` que incluya campos: `id`, `cliente_id`, `mascota` (objeto `Mascota`), `veterinario_id`, `fecha`, `duracion_minutos`, `estado`, `motivo`, `notas`. Agrega `fromJson` y `toJson`.
La clave `mascota` en la respuesta puede ser null o un objeto; soporta ambos casos.
"""

**Mensajes/UX recomendados cuando `firebase/verify` devuelve 422**
- Mostrar diálogo: "Tu cuenta no está registrada en nuestro sistema. Regístrate con email y contraseña para crear tu perfil local o contacta soporte." Si `existing_local_by_email: true`, sugerir "Parece que ya existe una cuenta con tu email. Inicia sesión con email/contraseña para vincular tu cuenta de Firebase desde la configuración.".

**Comandos para probar manualmente**
```powershell
# Registrar
curl -X POST https://<API_HOST>/api/auth/register -d "name=Test&email=test@example.com&password=secret&password_confirmation=secret&role=cliente"

# Login
curl -X POST https://<API_HOST>/api/auth/login -d "email=test@example.com&password=secret"

# Verificar Firebase (solo si el usuario existe localmente)
curl -X POST https://<API_HOST>/api/firebase/verify -d "firebase_token=<TOKEN>"

# Listar mascotas (con token)
curl -H "Authorization: Bearer <TOKEN>" https://<API_HOST>/api/mascotas
```

**Notas finales y recomendaciones**
- Migrar al flujo local (`/auth/register` + `/auth/login`) elimina problemas cuando el header/token de Firebase falta o expira.
- Si deseas una experiencia "social login" integrada sin pedir registro local, puedo agregar un endpoint seguro "link account" (vincula `firebase_uid` a un usuario local tras autenticación con email/password). Dime si quieres que lo implemente.

----
Archivo generado automáticamente por el asistente para ayudar al equipo frontend y Copilot.
