README — Asegurar que el token funcione en toda la app (Flutter)

Este README explica, paso a paso, cómo guardar/propagar el token emitido por tu backend (Laravel Sanctum / token) y cómo garantizar que todas las peticiones lo envíen correctamente.

Resumen corto
- Guardar el token que devuelve el backend tras login (ej: `sanctum_token`).
- Usar un solo `ApiService` (singleton) que incluya el header `Authorization: Bearer <token>` en cada petición.
- Actualizar el `ApiService` justo después del login (o auto-cargar el token al iniciar la app).
- Probar localmente con curl y revisar logs del backend si hay 401.

Contenido
- Requisitos / dependencias
- Implementación recomendada (Dio + SharedPreferences + Provider)
- Uso en `main.dart` y en login/logout
- Debugging rápido y pruebas
- Notas sobre emulador / dispositivo y HTTP

---

1) Requisitos / dependencias

En tu `pubspec.yaml` añade (si no los tienes):

```yaml
dependencies:
  dio: ^5.0.0
  shared_preferences: ^2.0.0
  provider: ^6.0.0
```

Ejecuta:

```bash
flutter pub get
```

---

2) Implementación recomendada (ApiService con Dio)

Crea `lib/services/api_service.dart`:

```dart
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;

  ApiService._internal() {
    _dio = Dio(BaseOptions(
      baseUrl: _baseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 15),
      headers: {'Accept': 'application/json'},
    ));
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = _token ?? await _loadTokenFromPrefs();
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
    ));
  }

  static const String _baseUrl = 'http://192.168.1.42:8000'; // AJUSTA a tu red
  static const String _storageKey = 'auth_token';

  late Dio _dio;
  String? _token;

  Dio get client => _dio;

  Future<void> setToken(String token) async {
    _token = token;
    _dio.options.headers['Authorization'] = 'Bearer $token';
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_storageKey, token);
  }

  Future<void> clearToken() async {
    _token = null;
    _dio.options.headers.remove('Authorization');
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_storageKey);
  }

  Future<String?> _loadTokenFromPrefs() async {
    final prefs = await SharedPreferences.getInstance();
    final t = prefs.getString(_storageKey);
    _token = t;
    return t;
  }

  Future<void> init() async {
    await _loadTokenFromPrefs();
  }

  // helpers
  Future<Response> get(String path, {Map<String, dynamic>? query}) =>
    _dio.get(path, queryParameters: query);

  Future<Response> post(String path, {dynamic data}) =>
    _dio.post(path, data: data);
}
```

Notas:
- `setToken()` guarda en memoria, aplica el header y persiste en SharedPreferences.
- El interceptor lee token en cada petición (si _token es null intenta cargar de prefs). Esto evita usar token viejo si el servicio ya estaba inicializado.

---

3) AuthProvider (ejemplo con Provider)

`lib/providers/auth_provider.dart`:

```dart
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class AuthProvider with ChangeNotifier {
  bool _logged = false;
  Map<String, dynamic>? _user;

  bool get isLogged => _logged;
  Map<String, dynamic>? get user => _user;

  Future<void> init() async {
    // Cargar token persisted y actualizar estado si quieres auto-login
    await ApiService().init();
    // Opcional: pedir perfil al backend para confirmar validez del token
    try {
      final res = await ApiService().get('/api/user');
      if (res.statusCode == 200) {
        _user = res.data;
        _logged = true;
        notifyListeners();
      }
    } catch (_) {
      _logged = false;
    }
  }

  Future<void> login(String email, String password) async {
    final res = await ApiService().post('/api/auth/login', data: {
      'email': email,
      'password': password,
    });
    if (res.statusCode == 200) {
      final data = res.data;
      final token = data['sanctum_token'] ?? data['token'] ?? data['access_token'];
      if (token != null) {
        await ApiService().setToken(token);
      }
      _user = data['user'];
      _logged = true;
      notifyListeners();
    } else {
      throw Exception('Login failed');
    }
  }

  Future<void> logout() async {
    // opcional: notificar al backend
    await ApiService().clearToken();
    _logged = false;
    _user = null;
    notifyListeners();
  }
}
```

Registrar provider en `main.dart`:

```dart
void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final api = ApiService();
  await api.init(); // carga token desde SharedPreferences
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()..init()),
      ],
      child: MyApp(),
    ),
  );
}
```

---

4) Uso desde UI / pantallas

- En pantalla de login: tras recibir respuesta del backend, llama a `authProvider.login(email, password)` o guarda token directamente con `ApiService().setToken(token)`.
- En cualquier petición protegida: usa `ApiService().get('/api/..')` o `ApiService().post()`; el header Authorization se añadirá automáticamente.

---

5) Debugging rápido y comprobaciones

1. Verifica que el login devuelve token (ejemplo con Postman o print en app).
2. Tras login imprime:
```dart
print('TOKEN: ${await SharedPreferences.getInstance().then((p) => p.getString("auth_token"))}');
```
3. Antes de una petición protegida imprime el header (dentro del interceptor puedes hacer `print('Sending Authorization: ' + (options.headers['Authorization'] ?? 'null'))`).
4. Prueba con curl desde tu PC usando ese token:
```powershell
curl -H "Authorization: Bearer TU_TOKEN" -H "Accept: application/json" http://192.168.1.42:8000/api/mascotas/1
```
- Si curl responde OK y la app no → problema en app (no envía header).
- Si curl falla → token inválido / backend.

5. Revisa logs en Laravel (temporal):
```php
\Log::info('Auth header: ' . request()->header('authorization'));
```
`storage/logs/laravel.log` mostrará si Authorization llega.

---

6) Emulador / dispositivo y host

- Android emulator (default AVD): usar `10.0.2.2` para apuntar a `localhost` del PC.
  Ej: `http://10.0.2.2:8000`
- Genymotion: IP distinta (ej. `10.0.3.2`)
- iOS Simulator: `http://localhost:8000` normalmente funciona.
- Dispositivo físico: usa la IP local de tu PC (ej `http://192.168.1.42:8000`) y asegúrate de que el PC y el móvil estén en la misma red; abre el firewall si hace falta.

---

7) Consideraciones / errores comunes

- Token guardado pero no usado porque el cliente fue inicializado antes. Solución: interceptor lee token por petición o normalmente `setToken` actualiza el client.
- Token renovado en backend al relogin: forzar logout local y `setToken` con el nuevo token.
- Uso de cookie-based Sanctum (SPA) vs tokens: para apps móviles usa tokens (Bearer) — evita cookie-based.
- HTTP en Android: si usas HTTP (no HTTPS) en Android 9+, activa cleartext temporalmente o usa HTTPS.

---

8) Cambio incremental sugerido (si tienes código existente)
- Pega aquí tu `ApiService` o `AuthProvider` actual y te doy el diff exacto para adaptarlo.
- Si quieres que implemente un interceptor de refresco automático (refresh token), lo añadimos después.

---

9) Lista de verificación antes de probar en dispositivo real
- [ ] Backend corriendo y accesible desde la red (prueba curl desde móvil/PC).
- [ ] Base URL correcto en `ApiService`.
- [ ] `flutter pub get` actualizado.
- [ ] `ApiService().init()` invocado en `main()` antes de `runApp`.
- [ ] Login guarda token y `ApiService().setToken(token)` es llamado.
- [ ] Antes de peticiones protegidas, comprueba logs/prints de header.

---

Si quieres, puedo ahora:
- Revisar tu `AuthProvider`/`ApiService` actual (pégalo aquí) y darte el parche exacto.
- O generarte un pequeño ejemplo de proyecto Flutter con estos archivos listos para probar en un emulador.
