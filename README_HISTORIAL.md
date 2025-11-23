# Historial Médico — API spec y guía para Flutter

Este documento describe cómo el frontend (Flutter) debe construir y enviar el payload para crear/actualizar un `HistorialMedico` en el backend de VetCareApp. Incluye:
- Campos esperados, tipos y validaciones
- Normalización (cuando el frontend envía strings vs arrays)
- Ejemplos de payloads (simple y completo)
- Snippets Flutter (modelos Dart, normalizadores y llamada con Dio)
- Notas de migración y verificación

---

**Resumen rápido (backend actual)**
- `diagnostico` y `tratamiento` se guardan como JSON array en la BD (cast a `array` en `HistorialMedico`).
- `archivos_meta` está disponible como array para metadatos de archivos.
- `servicios` se guarda en pivot `historial_servicio` con `cantidad` y `precio_unitario`.
- `cita_id` es opcional; si se envía, backend valida que la cita pertenezca a la `mascota_id`.

---

## Campos (payload para POST /api/historial-medico)
- `mascota_id` (int) — required
- `cita_id` (int) — optional, must exist and belong to `mascota_id` if provided
- `fecha` (ISO8601 string) — optional, default now()
- `tipo` (string) — optional (ej. "consulta", "vacunacion")
- `diagnostico` (array | string) — optional. Prefer array. See normalization.
- `tratamiento` (array | string) — optional. Prefer array of objects.
- `observaciones` (string) — optional
- `realizado_por` (int) — optional (veterinario id)
- `servicios` (array) — optional. Prefer array of objects `{id, cantidad}`. Backend acepta also array of ids.
- `archivos_meta` (array) — optional, metadatos de archivos (filename/url/tipo)
- `facturado` (boolean) — optional
- `factura_id` (int) — optional

### Validaciones recomendadas (backend)
- `mascota_id` => required|exists:mascotas,id
- `cita_id` => nullable|exists:citas,id (and validate belongsTo mascota)
- `fecha` => nullable|date
- `diagnostico` => nullable|array
- `diagnostico.*.descripcion` => required_with:diagnostico|string
- `tratamiento` => nullable|array
- `tratamiento.*.tipo` => nullable|in:medicamento,procedimiento,indicacion
- `tratamiento.*.nombre` => required_with:tratamiento|string
- `servicios` => nullable|array
- `servicios.*.id` => exists:servicios,id
- `archivos_meta` => nullable|array

---

## Estructuras recomendadas

### Diagnóstico (recomendado)
- Array de objetos para mayor información:
```json
[
  { "codigo": "D10", "descripcion": "Otitis externa", "observaciones": "crónica" }
]
```
- Backend acepta también `diagnostico` como string o array de strings; frontend debe normalizar.

### Tratamiento (recomendado)
- Cada ítem describe una prescripción o indicación:
```json
[
  {
    "tipo": "medicamento",
    "nombre": "Amoxicilina",
    "dosis": "20 mg/kg",
    "frecuencia": "cada 12h",
    "duracion": "7 días",
    "via": "oral",
    "notas": "administrar con comida"
  },
  {
    "tipo": "indicacion",
    "nombre": "Limpieza de oído",
    "notas": "limpiar con solución salina 2 veces al día"
  }
]
```
- Si el frontend envía `tratamiento` como string, el controller lo debe normalizar a un array con un item `{ tipo: 'indicacion', nombre: '<texto>' }`.

### Servicios
- Forma simple: array de ids
```json
[5, 8]
```
- Forma recomendada (detallada):
```json
[{ "id": 5, "cantidad": 1 }]
```
Backend registra precio actual en pivot; si quieres enviar precio histórico, enviar `precio_unitario` en cada objeto.

### Archivos meta
- Solo metadatos (cuando upload esté separado):
```json
[{ "filename":"foto1.jpg", "url":"/storage/historial/123/foto1.jpg", "tipo":"imagen" }]
```

---

## Ejemplo de payload completo (recomendado)
```json
{
  "mascota_id": 123,
  "cita_id": 456,
  "fecha": "2025-11-16T10:30:00",
  "tipo": "consulta",
  "diagnostico": [
    { "codigo": "D10", "descripcion": "Otitis externa", "observaciones": "crónica, revisar en 7 días" }
  ],
  "tratamiento": [
    { "tipo": "medicamento", "nombre": "Amoxicilina", "dosis": "20 mg/kg", "frecuencia": "cada 12h", "duracion": "7 días", "via": "oral", "notas": "administrar con comida" }
  ],
  "observaciones": "Paciente responde bien",
  "realizado_por": 12,
  "servicios": [ { "id": 5, "cantidad": 1 } ],
  "archivos_meta": [ { "filename": "oreja.jpg", "url": "/storage/...", "tipo": "imagen" } ],
  "facturado": false
}
```

Ejemplo simple (diagnostico string):
```json
{ "mascota_id":123, "diagnostico": "Otitis externa - prurito" }
```

---

## Normalización (frontend) — funciones sugeridas en Dart
- Normalizar `diagnostico`:
  - string -> `[ { "descripcion": "<string>" } ]`
  - list<string> -> map a `[ { "descripcion": str }, ... ]`
  - list<object> -> pasar tal cual
- Normalizar `tratamiento`:
  - string -> `[ { "tipo": "indicacion", "nombre": "<string>" } ]`
  - list<object> -> pasar tal cual

Snippet Dart para normalizar:
```dart
List<Map<String,dynamic>> normalizeDiagnostico(dynamic input) {
  if (input == null) return [];
  if (input is String) return [{'descripcion': input}];
  if (input is List) {
    return input.map<Map<String,dynamic>>((e) {
      if (e is String) return {'descripcion': e};
      if (e is Map) return Map<String,dynamic>.from(e);
      return {};
    }).where((m) => m.isNotEmpty).toList();
  }
  return [];
}

List<Map<String,dynamic>> normalizeTratamiento(dynamic input) {
  if (input == null) return [];
  if (input is String) return [{ 'tipo':'indicacion', 'nombre': input }];
  if (input is List) {
    return input.map<Map<String,dynamic>>((e) {
      if (e is String) return {'tipo':'indicacion','nombre': e};
      if (e is Map) return Map<String,dynamic>.from(e);
      return {};
    }).where((m) => m.isNotEmpty).toList();
  }
  return [];
}
```

---

## Snippet Flutter: ApiService + createHistorial (Dio)
```dart
// lib/services/api_service.dart (simplified)
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
      if (token != null) options.headers['Authorization'] = 'Bearer $token';
      return handler.next(options);
    }));
  }

  Future<Response> createHistorial(Map<String,dynamic> body) async {
    return dio.post('/historial-medico', data: body);
  }
}
```

Uso recomendado en código UI/VM:
```dart
final body = {
  'mascota_id': mascotaId,
  'cita_id': citaId,
  'fecha': fecha.toIso8601String(),
  'tipo': tipo,
  'diagnostico': normalizeDiagnostico(diagnosticoInput),
  'tratamiento': normalizeTratamiento(tratamientoInput),
  'servicios': serviciosInput, // [{id:5,cantidad:1}] o [5,8]
  'archivos_meta': archivosMeta,
};

final resp = await ApiService().createHistorial(body);
```

---

## Migración y verificación
- Ya existe migración para convertir `diagnostico` a JSON y se creó una para `tratamiento`.
- Antes de ejecutar migraciones en producción: hacer backup de la base de datos.
- Ejecutar migraciones:
```powershell
php artisan migrate
```
- Verificar datos guardados:
```sql
SELECT id, diagnostico, tratamiento FROM historial_medicos ORDER BY id DESC LIMIT 20;
```

---

## Tests sugeridos (rápidos)
1. Crear historial con `diagnostico` string → verificar `diagnostico` en respuesta es array.
2. Crear historial con `tratamiento` con 2 objetos → verificar persistencia de ambos elementos.
3. Crear historial con `servicios` como ids → verificar pivot `historial_servicio` y `precio_unitario` en respuesta.
4. Crear con `archivos_meta` y verificar estructura retornada.

---

## Buenas prácticas
- Normalizar en frontend antes de enviar.
- Envolver llamadas en try/catch y mapear errores 422 → mostrar mensajes por campo.
- Registrar logs y capturar con Sentry en producción para errores 500.

---

Si quieres, genero ahora los archivos Dart de ejemplo (`lib/services/api_service.dart`, `lib/models/historial_models.dart` y un ejemplo de formulario). Dime si prefieres que los suba al repo.
