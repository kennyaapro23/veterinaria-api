# 📅 API de Horarios para Flutter - Guía Completa

## 🎯 Resumen

Esta guía documenta cómo el frontend Flutter debe interactuar con los endpoints de gestión de horarios de veterinarios.

---

## 🔐 Autenticación

Todos los endpoints requieren token de autenticación Sanctum:

```dart
headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
}
```

---

## 📋 Endpoints Disponibles

### 1️⃣ **Ver Horarios de un Veterinario**

**Endpoint:** `GET /api/veterinarios/{id}/disponibilidad`

**Parámetros Query (opcionales):**
- `fecha` - Formato: `YYYY-MM-DD` (default: hoy)

**Ejemplo Request:**
```dart
final response = await http.get(
  Uri.parse('$baseUrl/api/veterinarios/$veterinarioId/disponibilidad?fecha=2025-11-10'),
  headers: {
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
  },
);
```

**Ejemplo Response (200):**
```json
{
  "veterinario": {
    "id": 1,
    "nombre": "Dra. María García",
    "especialidad": "Medicina General"
  },
  "fecha": "2025-11-10",
  "dia_semana": 0,
  "horarios_configurados": [
    {
      "id": 1,
      "veterinario_id": 1,
      "dia_semana": 1,
      "hora_inicio": "09:00",
      "hora_fin": "13:00",
      "intervalo_minutos": 30,
      "activo": true,
      "created_at": "2025-11-08T00:00:00.000000Z",
      "updated_at": "2025-11-08T00:00:00.000000Z"
    }
  ],
  "citas_agendadas": []
}
```

---

### 2️⃣ **Ver Slots Disponibles para Agendar**

**Endpoint:** `GET /api/veterinarios/{id}/slots`

**Parámetros Query (opcionales):**
- `fecha` - Formato: `YYYY-MM-DD` (default: hoy)

**Ejemplo Request:**
```dart
final response = await http.get(
  Uri.parse('$baseUrl/api/veterinarios/$veterinarioId/slots?fecha=2025-11-10'),
  headers: {
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
  },
);
```

**Ejemplo Response (200):**
```json
{
  "veterinario": {
    "id": 1,
    "nombre": "Dra. María García",
    "especialidad": "Medicina General"
  },
  "fecha": "2025-11-10",
  "dia_semana": 0,
  "slots": [
    {
      "hora_inicio": "09:00",
      "hora_fin": "09:30",
      "disponible": true,
      "cita": null
    },
    {
      "hora_inicio": "09:30",
      "hora_fin": "10:00",
      "disponible": false,
      "cita": {
        "id": 5,
        "cliente": "Juan Pérez",
        "mascota": "Max",
        "motivo": "Vacunación",
        "estado": "programada"
      }
    }
  ]
}
```

---

### 3️⃣ **Reemplazar TODOS los Horarios**

⚠️ **CUIDADO:** Este endpoint **elimina todos los horarios anteriores** y crea los nuevos.

**Endpoint:** `POST /api/veterinarios/{id}/disponibilidad`

**Permisos:** Solo el veterinario dueño o recepción

**Body JSON:**
```json
{
  "horarios": [
    {
      "dia_semana": 1,
      "hora_inicio": "09:00",
      "hora_fin": "13:00",
      "intervalo_minutos": 30,
      "activo": true
    },
    {
      "dia_semana": 1,
      "hora_inicio": "16:00",
      "hora_fin": "20:00",
      "intervalo_minutos": 30,
      "activo": true
    },
    {
      "dia_semana": 2,
      "hora_inicio": "09:00",
      "hora_fin": "18:00",
      "intervalo_minutos": 45,
      "activo": true
    }
  ]
}
```

**Ejemplo Request:**
```dart
final response = await http.post(
  Uri.parse('$baseUrl/api/veterinarios/$veterinarioId/disponibilidad'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: jsonEncode({
    'horarios': [
      {
        'dia_semana': 1, // int, no string
        'hora_inicio': '09:00',
        'hora_fin': '13:00',
        'intervalo_minutos': 30, // int
        'activo': true, // bool
      },
      // ... más horarios
    ]
  }),
);
```

**Response (200):**
```json
{
  "message": "Horarios de disponibilidad configurados exitosamente",
  "horarios": [...]
}
```

---

### 4️⃣ **Agregar UN Horario Individual**

**Endpoint:** `POST /api/veterinarios/{id}/horarios`

**Permisos:** Solo el veterinario dueño o recepción

**Body JSON:**
```json
{
  "dia_semana": 3,
  "hora_inicio": "14:00",
  "hora_fin": "18:00",
  "intervalo_minutos": 30,
  "activo": true
}
```

**Ejemplo Request:**
```dart
final response = await http.post(
  Uri.parse('$baseUrl/api/veterinarios/$veterinarioId/horarios'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: jsonEncode({
    'dia_semana': 3,
    'hora_inicio': '14:00',
    'hora_fin': '18:00',
    'intervalo_minutos': 30,
    'activo': true,
  }),
);
```

**Response (201):**
```json
{
  "message": "Horario agregado exitosamente",
  "horario": {
    "id": 15,
    "veterinario_id": 1,
    "dia_semana": 3,
    "hora_inicio": "14:00",
    "hora_fin": "18:00",
    "intervalo_minutos": 30,
    "activo": true,
    "created_at": "2025-11-08T21:00:00.000000Z",
    "updated_at": "2025-11-08T21:00:00.000000Z"
  }
}
```

---

### 5️⃣ **Editar un Horario Existente**

**Endpoint:** `PUT /api/veterinarios/{veterinarioId}/horarios/{horarioId}`

**Permisos:** Solo el veterinario dueño o recepción

**Body JSON (todos los campos son opcionales):**
```json
{
  "dia_semana": 4,
  "hora_inicio": "10:00",
  "hora_fin": "14:00",
  "intervalo_minutos": 45,
  "activo": false
}
```

**Ejemplo Request:**
```dart
final response = await http.put(
  Uri.parse('$baseUrl/api/veterinarios/$veterinarioId/horarios/$horarioId'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: jsonEncode({
    'hora_inicio': '10:00',
    'hora_fin': '14:00',
  }),
);
```

**Response (200):**
```json
{
  "message": "Horario actualizado exitosamente",
  "horario": {
    "id": 15,
    "veterinario_id": 1,
    "dia_semana": 4,
    "hora_inicio": "10:00",
    "hora_fin": "14:00",
    "intervalo_minutos": 30,
    "activo": true,
    "created_at": "2025-11-08T21:00:00.000000Z",
    "updated_at": "2025-11-08T21:10:00.000000Z"
  }
}
```

---

### 6️⃣ **Eliminar un Horario**

**Endpoint:** `DELETE /api/veterinarios/{veterinarioId}/horarios/{horarioId}`

**Permisos:** Solo el veterinario dueño o recepción

**Body:** (vacío)

**Ejemplo Request:**
```dart
final response = await http.delete(
  Uri.parse('$baseUrl/api/veterinarios/$veterinarioId/horarios/$horarioId'),
  headers: {
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
  },
);
```

**Response (200):**
```json
{
  "message": "Horario eliminado exitosamente"
}
```

---

### 7️⃣ **Activar/Desactivar un Horario (Toggle)**

**Endpoint:** `PATCH /api/veterinarios/{veterinarioId}/horarios/{horarioId}/toggle`

**Permisos:** Solo el veterinario dueño o recepción

**Body:** (vacío)

**Ejemplo Request:**
```dart
final response = await http.patch(
  Uri.parse('$baseUrl/api/veterinarios/$veterinarioId/horarios/$horarioId/toggle'),
  headers: {
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
  },
);
```

**Response (200):**
```json
{
  "message": "Horario activado", // o "Horario desactivado"
  "horario": {
    "id": 15,
    "activo": true, // cambió de false a true
    ...
  }
}
```

---

## ✅ Reglas de Validación

| Campo | Tipo | Requerido | Validación |
|-------|------|-----------|------------|
| `dia_semana` | `int` | ✅ Sí | Entre 0-6 (0=Domingo, 1=Lunes, ... 6=Sábado) |
| `hora_inicio` | `string` | ✅ Sí | Formato `"HH:mm"` (ej: `"09:00"`) con ceros a la izquierda |
| `hora_fin` | `string` | ✅ Sí | Formato `"HH:mm"`, debe ser después de `hora_inicio` |
| `intervalo_minutos` | `int` | ✅ Sí | Entre 10-120 minutos |
| `activo` | `bool` | ⚠️ Depende | Requerido en `setDisponibilidad`, opcional en `addHorario` (default: `true`) |

---

## 🚨 Errores Comunes

### Error 422: Validation Error

**Causa más común:** Tipos de datos incorrectos

❌ **MAL:**
```dart
{
  'dia_semana': '1',  // ❌ String en vez de int
  'hora_inicio': '9:00',  // ❌ Falta cero (debe ser "09:00")
  'intervalo_minutos': '30',  // ❌ String en vez de int
  'activo': 'true',  // ❌ String en vez de bool
}
```

✅ **BIEN:**
```dart
{
  'dia_semana': 1,  // ✅ int
  'hora_inicio': '09:00',  // ✅ formato correcto
  'hora_fin': '13:00',  // ✅ formato correcto
  'intervalo_minutos': 30,  // ✅ int
  'activo': true,  // ✅ bool
}
```

### Error 403: Forbidden

**Causa:** Usuario no autorizado para editar el horario

**Solución:** 
- Solo el veterinario dueño (su propio horario) puede editarlo
- O usuarios con rol `recepcion`
- Clientes **no pueden** editar horarios

### Error 404: Not Found

**Causa 1:** Veterinario no existe
- Verifica que `veterinario_id` sea correcto

**Causa 2:** Horario no existe
- El `horarioId` no corresponde a ese veterinario

---

## 🎨 Mapeo de Días de la Semana

```dart
// Backend espera estos valores numéricos
const Map<int, String> diasSemana = {
  0: 'Domingo',
  1: 'Lunes',
  2: 'Martes',
  3: 'Miércoles',
  4: 'Jueves',
  5: 'Viernes',
  6: 'Sábado',
};
```

---

## 🔄 Flujos Recomendados

### Flujo 1: Cargar Agenda del Veterinario

1. Login → obtener `veterinario_id` del usuario
2. Llamar `GET /api/veterinarios/{veterinario_id}/disponibilidad`
3. Mostrar lista de horarios configurados
4. Permitir editar/eliminar cada horario

### Flujo 2: Ver Slots para Reservar Cita

1. Seleccionar veterinario y fecha
2. Llamar `GET /api/veterinarios/{id}/slots?fecha=YYYY-MM-DD`
3. Pintar calendario con slots verdes (disponibles) y rojos (ocupados)
4. Al tocar slot disponible → crear cita

### Flujo 3: Editar un Horario Individual

1. Cargar horarios existentes
2. Usuario selecciona horario a editar
3. Mostrar modal con campos prellenados
4. Al guardar → `PUT /api/veterinarios/{vet_id}/horarios/{horario_id}`
5. Refrescar lista

### Flujo 4: Restaurar Horario por Defecto

1. Usuario toca "Restaurar horarios por defecto"
2. Mostrar confirmación (⚠️ se borrarán todos los horarios actuales)
3. Llamar `POST /api/veterinarios/{id}/disponibilidad` con:
```json
{
  "horarios": [
    {"dia_semana": 1, "hora_inicio": "09:00", "hora_fin": "18:00", "intervalo_minutos": 30, "activo": true},
    {"dia_semana": 2, "hora_inicio": "09:00", "hora_fin": "18:00", "intervalo_minutos": 30, "activo": true},
    {"dia_semana": 3, "hora_inicio": "09:00", "hora_fin": "18:00", "intervalo_minutos": 30, "activo": true},
    {"dia_semana": 4, "hora_inicio": "09:00", "hora_fin": "18:00", "intervalo_minutos": 30, "activo": true},
    {"dia_semana": 5, "hora_inicio": "09:00", "hora_fin": "18:00", "intervalo_minutos": 30, "activo": true}
  ]
}
```

---

## 🧪 Testing con Postman/curl

### Crear horario Lunes 9-13h

```bash
curl -X POST http://127.0.0.1:8000/api/veterinarios/1/horarios \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "dia_semana": 1,
    "hora_inicio": "09:00",
    "hora_fin": "13:00",
    "intervalo_minutos": 30,
    "activo": true
  }'
```

### Ver slots de hoy

```bash
curl -X GET "http://127.0.0.1:8000/api/veterinarios/1/slots?fecha=2025-11-10" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Accept: application/json"
```

---

## 📊 Ejemplo Completo en Dart

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class VetScheduleService {
  final String baseUrl = 'http://127.0.0.1:8000/api';
  final String token;

  VetScheduleService(this.token);

  // 1. Obtener horarios configurados
  Future<Map<String, dynamic>> getSchedule(int veterinarioId, {String? fecha}) async {
    final uri = Uri.parse('$baseUrl/veterinarios/$veterinarioId/disponibilidad')
        .replace(queryParameters: fecha != null ? {'fecha': fecha} : null);
    
    final response = await http.get(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Error: ${response.statusCode} - ${response.body}');
    }
  }

  // 2. Obtener slots disponibles
  Future<Map<String, dynamic>> getSlots(int veterinarioId, String fecha) async {
    final uri = Uri.parse('$baseUrl/veterinarios/$veterinarioId/slots')
        .replace(queryParameters: {'fecha': fecha});
    
    final response = await http.get(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Error: ${response.statusCode} - ${response.body}');
    }
  }

  // 3. Agregar un horario
  Future<Map<String, dynamic>> addSchedule(
    int veterinarioId, {
    required int diaSemana,
    required String horaInicio,
    required String horaFin,
    required int intervaloMinutos,
    bool activo = true,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/veterinarios/$veterinarioId/horarios'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'dia_semana': diaSemana,
        'hora_inicio': horaInicio,
        'hora_fin': horaFin,
        'intervalo_minutos': intervaloMinutos,
        'activo': activo,
      }),
    );

    if (response.statusCode == 201) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Error: ${response.statusCode} - ${response.body}');
    }
  }

  // 4. Editar un horario
  Future<Map<String, dynamic>> updateSchedule(
    int veterinarioId,
    int horarioId, {
    int? diaSemana,
    String? horaInicio,
    String? horaFin,
    int? intervaloMinutos,
    bool? activo,
  }) async {
    final body = <String, dynamic>{};
    if (diaSemana != null) body['dia_semana'] = diaSemana;
    if (horaInicio != null) body['hora_inicio'] = horaInicio;
    if (horaFin != null) body['hora_fin'] = horaFin;
    if (intervaloMinutos != null) body['intervalo_minutos'] = intervaloMinutos;
    if (activo != null) body['activo'] = activo;

    final response = await http.put(
      Uri.parse('$baseUrl/veterinarios/$veterinarioId/horarios/$horarioId'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode(body),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Error: ${response.statusCode} - ${response.body}');
    }
  }

  // 5. Eliminar un horario
  Future<void> deleteSchedule(int veterinarioId, int horarioId) async {
    final response = await http.delete(
      Uri.parse('$baseUrl/veterinarios/$veterinarioId/horarios/$horarioId'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode != 200) {
      throw Exception('Error: ${response.statusCode} - ${response.body}');
    }
  }

  // 6. Activar/Desactivar horario
  Future<Map<String, dynamic>> toggleSchedule(int veterinarioId, int horarioId) async {
    final response = await http.patch(
      Uri.parse('$baseUrl/veterinarios/$veterinarioId/horarios/$horarioId/toggle'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Error: ${response.statusCode} - ${response.body}');
    }
  }
}

// Uso:
void main() async {
  final service = VetScheduleService('tu_token_aqui');
  
  // Obtener slots del 10 de noviembre
  final slots = await service.getSlots(1, '2025-11-10');
  print('Slots: $slots');
  
  // Agregar horario Lunes 9-13h
  await service.addSchedule(
    1,
    diaSemana: 1,
    horaInicio: '09:00',
    horaFin: '13:00',
    intervaloMinutos: 30,
  );
}
```

---

## 🎯 Checklist de Integración

- [ ] Login devuelve `veterinario_id` en el objeto user
- [ ] Endpoint `GET /veterinarios/{id}/disponibilidad` funciona
- [ ] Endpoint `GET /veterinarios/{id}/slots` funciona
- [ ] Se pueden crear horarios con `POST /veterinarios/{id}/horarios`
- [ ] Se pueden editar horarios con `PUT /veterinarios/{id}/horarios/{horarioId}`
- [ ] Se pueden eliminar horarios con `DELETE /veterinarios/{id}/horarios/{horarioId}`
- [ ] Toggle activo/inactivo funciona con `PATCH .../toggle`
- [ ] Tipos de datos correctos: `dia_semana` (int), `intervalo_minutos` (int), `activo` (bool)
- [ ] Formato de hora correcto: `"HH:mm"` con ceros a la izquierda
- [ ] Manejo de errores 422, 403, 404
- [ ] Refresh de UI después de crear/editar/eliminar

---

## 📞 Soporte

Si encuentras errores, comparte:

1. El endpoint llamado (ej: `POST /api/veterinarios/1/horarios`)
2. El body enviado (print antes de enviar)
3. El código de error (200, 422, 403, 404, 500)
4. La respuesta del backend (response.body)

---

**Última actualización:** 8 de noviembre de 2025
**Versión API:** 1.0
**Backend:** Laravel 12.x
