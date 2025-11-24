# CRUD Servicios — Flutter

Guía mínima y directa para consumir el CRUD de `servicios` desde Flutter.

## Resumen
- Base URL: `BASE_URL` (ej. `http://10.0.2.2:8000` en emulador Android).
- Autenticación: header `Authorization: Bearer <token>` (guardar token con `flutter_secure_storage`).

## Endpoints

- Listar servicios
  - GET `/api/servicios`
  - Query params: `page`, `search`, `tipo`, `precio_min`, `precio_max`.
  - Respuesta: paginación Laravel `{ data: [...], meta: {...}, links: {...} }`.

- Detalle
  - GET `/api/servicios/{id}`
  - Respuesta: objeto servicio (+ `es_vacuna` boolean).

- Crear (solo `recepcion`)
  - POST `/api/servicios`
  - Body JSON:
    - `codigo` (string, required, unique)
    - `nombre` (string, required)
    - `descripcion` (string, optional)
    - `tipo` (enum: `vacuna,tratamiento,baño,consulta,cirugía,otro`)
    - `duracion_minutos` (int, 5..480)
    - `precio` (decimal)
    - `requiere_vacuna_info` (boolean)
  - Respuesta: `201 { message, servicio }`.

- Actualizar (solo `recepcion`)
  - PUT `/api/servicios/{id}`
  - Campos `sometimes` — responde `{ message, servicio }`.

- Eliminar (solo `recepcion`)
  - DELETE `/api/servicios/{id}`
  - Retorna error `422` si está asociado a citas.

- Obtener tipos
  - GET `/api/servicios-tipos` → `{ tipos: [...] }`

## Errores importantes
- `401/403` — autenticación/permiso.
- `422` — validación o eliminaciones bloqueadas (servicio en uso).
- `500` — error servidor.

## Snippets Flutter (Dio)

1) `pubspec.yaml` deps (sugerido):
```
dio: ^5.0.0
flutter_secure_storage: ^8.0.0
```

2) Cliente HTTP básico:
```dart
import 'package:dio/dio.dart';

final dio = Dio(BaseOptions(baseUrl: 'https://api.tu-dominio.com'));
dio.options.headers['Authorization'] = 'Bearer <token>';

// Listar servicios
Future<Map<String,dynamic>> listarServicios({int page = 1, String? search, String? tipo}) async {
  final resp = await dio.get('/api/servicios', queryParameters: {
    'page': page,
    if (search!=null) 'search': search,
    if (tipo!=null) 'tipo': tipo,
  });
  return resp.data; // data, meta, links
}

// Obtener detalle
Future<Map<String,dynamic>> getServicio(int id) async {
  final resp = await dio.get('/api/servicios/$id');
  return resp.data;
}

// Crear servicio
Future<Map<String,dynamic>> crearServicio(Map<String,dynamic> body) async {
  final resp = await dio.post('/api/servicios', data: body);
  return resp.data; // { message, servicio }
}

// Actualizar
Future<Map<String,dynamic>> actualizarServicio(int id, Map<String,dynamic> changes) async {
  final resp = await dio.put('/api/servicios/$id', data: changes);
  return resp.data;
}

// Eliminar
Future<void> eliminarServicio(int id) async {
  await dio.delete('/api/servicios/$id');
}
```

## UI / Permisos
- Oculta acciones de crear/editar/eliminar si `user.tipo_usuario != 'recepcion'`.
- Maneja 403 del backend mostrando mensaje breve.

## Buenas prácticas
- Validar formularios antes de enviar.
- Mapear errores 422 (`resp.data['errors']`) y mostrarlos en el formulario.
- Para dropdowns pequeños solicita `?per_page=1000` o usa `/api/servicios-tipos`.

---

Si quieres que lo guarde en otro archivo (`README_SERVICIOS_FLUTTER.md`) o que genere ejemplos Dart más completos, dime y lo agrego.

