# 📅 Sistema de Disponibilidad y Citas - Documentación

## 🎯 Objetivo
Sistema que permite gestionar horarios de veterinarios y visualizar slots disponibles/ocupados en tiempo real para agendar citas.

---

## 📊 Cómo Funciona el Sistema

### 1️⃣ **Horarios por Defecto Automáticos**

Cuando se crea un veterinario nuevo, automáticamente se generan horarios predeterminados:

- **Días**: Lunes a Viernes (1-5)
- **Horario**: 9:00 AM - 6:00 PM
- **Intervalos**: 30 minutos
- **Estado**: Activo

**Tabla en base de datos**: `agendas_disponibilidad`

```sql
-- Ejemplo de registros creados automáticamente
veterinario_id | dia_semana | hora_inicio | hora_fin | intervalo_minutos | activo
1             | 1 (Lunes)  | 09:00       | 18:00    | 30                | true
1             | 2 (Martes) | 09:00       | 18:00    | 30                | true
1             | 3 (Mié)    | 09:00       | 18:00    | 30                | true
1             | 4 (Jueves) | 09:00       | 18:00    | 30                | true
1             | 5 (Viernes)| 09:00       | 18:00    | 30                | true
```

**Días de la semana**:
- 0 = Domingo
- 1 = Lunes
- 2 = Martes
- 3 = Miércoles
- 4 = Jueves
- 5 = Viernes
- 6 = Sábado

---

### 2️⃣ **Sistema de Slots (Espacios de Tiempo)**

Un **slot** es un espacio de tiempo donde se puede agendar una cita.

**Ejemplo**: Si el horario es 9:00-18:00 con intervalos de 30 min:
- Slot 1: 9:00 - 9:30
- Slot 2: 9:30 - 10:00
- Slot 3: 10:00 - 10:30
- ... (18 slots en total)

Cada slot puede estar:
- ✅ **Disponible**: No hay cita agendada
- ❌ **Ocupado**: Ya existe una cita en ese horario

---

### 3️⃣ **Detección de Slots Ocupados**

El backend verifica automáticamente si un slot está ocupado:

```
Slot: 9:30 - 10:00
Cita existente: 9:15 - 10:15 (duración 60 min)

¿Se solapan? SÍ → Slot OCUPADO ❌
```

**Regla de solapamiento**:
```
slot_inicio < cita_fin  AND  slot_fin > cita_inicio
```

---

## 🔌 API Endpoints

### 📍 **1. Obtener Slots Disponibles/Ocupados** (NUEVO - Recomendado)

```http
GET /api/veterinarios/{id}/slots?fecha=2025-11-10
```

**Headers**:
```
Authorization: Bearer {token}
```

**Parámetros**:
- `fecha` (opcional): Fecha en formato `YYYY-MM-DD` (por defecto: hoy)

**Respuesta exitosa (200)**:
```json
{
  "veterinario": {
    "id": 1,
    "nombre": "Dr. Juan Pérez",
    "especialidad": "Cirugía Veterinaria"
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
        "cliente": "María González",
        "mascota": "Firulais",
        "motivo": "Vacunación antirrábica",
        "estado": "confirmada"
      }
    },
    {
      "hora_inicio": "10:00",
      "hora_fin": "10:30",
      "disponible": true,
      "cita": null
    }
    // ... más slots hasta las 18:00
  ]
}
```

**Caso: No hay horarios configurados**:
```json
{
  "veterinario": {
    "id": 1,
    "nombre": "Dr. Juan Pérez",
    "especialidad": "Cirugía Veterinaria"
  },
  "fecha": "2025-11-10",
  "mensaje": "No hay horarios configurados para este día",
  "slots": []
}
```

---

### 📍 **2. Obtener Disponibilidad Cruda** (Datos sin procesar)

```http
GET /api/veterinarios/{id}/disponibilidad?fecha=2025-11-10
```

**Respuesta**:
```json
{
  "veterinario": {
    "id": 1,
    "nombre": "Dr. Juan Pérez",
    "especialidad": "Cirugía"
  },
  "fecha": "2025-11-10",
  "dia_semana": 0,
  "horarios_configurados": [
    {
      "id": 1,
      "veterinario_id": 1,
      "dia_semana": 0,
      "hora_inicio": "09:00:00",
      "hora_fin": "18:00:00",
      "intervalo_minutos": 30,
      "activo": true
    }
  ],
  "citas_agendadas": [
    {
      "fecha": "2025-11-10 09:30:00",
      "duracion_minutos": 60
    }
  ]
}
```

**Uso**: Cuando necesitas procesar manualmente los datos en el frontend.

---

### 📍 **3. Configurar/Modificar Horarios** (REEMPLAZA TODOS)

```http
POST /api/veterinarios/{id}/disponibilidad
```

**Body**:
```json
{
  "horarios": [
    {
      "dia_semana": 1,
      "hora_inicio": "08:00",
      "hora_fin": "14:00",
      "intervalo_minutos": 20,
      "activo": true
    },
    {
      "dia_semana": 2,
      "hora_inicio": "10:00",
      "hora_fin": "19:00",
      "intervalo_minutos": 30,
      "activo": true
    }
  ]
}
```

**Respuesta (200)**:
```json
{
  "message": "Horarios de disponibilidad configurados exitosamente",
  "horarios": [...]
}
```

**⚠️ Nota**: Esto **elimina TODOS** los horarios anteriores y crea los nuevos.

---

### 📍 **4. Agregar un Horario Individual** (sin borrar los demás)

```http
POST /api/veterinarios/{id}/horarios
```

**Body**:
```json
{
  "dia_semana": 6,
  "hora_inicio": "10:00",
  "hora_fin": "14:00",
  "intervalo_minutos": 30,
  "activo": true
}
```

**Respuesta (201)**:
```json
{
  "message": "Horario agregado exitosamente",
  "horario": {
    "id": 15,
    "veterinario_id": 1,
    "dia_semana": 6,
    "hora_inicio": "10:00",
    "hora_fin": "14:00",
    "intervalo_minutos": 30,
    "activo": true,
    "created_at": "2025-11-08T10:30:00.000000Z",
    "updated_at": "2025-11-08T10:30:00.000000Z"
  }
}
```

**Uso**: Agregar horarios de Sábado/Domingo sin afectar los de Lun-Vie.

---

### 📍 **5. Actualizar un Horario Específico**

```http
PUT /api/veterinarios/{veterinarioId}/horarios/{horarioId}
```

**Body** (enviar solo los campos a modificar):
```json
{
  "hora_inicio": "08:30",
  "hora_fin": "17:30",
  "intervalo_minutos": 45
}
```

**Respuesta (200)**:
```json
{
  "message": "Horario actualizado exitosamente",
  "horario": {
    "id": 15,
    "veterinario_id": 1,
    "dia_semana": 1,
    "hora_inicio": "08:30",
    "hora_fin": "17:30",
    "intervalo_minutos": 45,
    "activo": true
  }
}
```

**Uso**: Cambiar el horario del Lunes sin afectar los demás días.

---

### 📍 **6. Eliminar un Horario Específico**

```http
DELETE /api/veterinarios/{veterinarioId}/horarios/{horarioId}
```

**Respuesta (200)**:
```json
{
  "message": "Horario eliminado exitosamente"
}
```

**Uso**: Eliminar solo el horario del Viernes, mantener Lun-Jue.

---

### 📍 **7. Activar/Desactivar un Horario** (sin eliminarlo)

```http
PATCH /api/veterinarios/{veterinarioId}/horarios/{horarioId}/toggle
```

**Respuesta (200)**:
```json
{
  "message": "Horario desactivado",
  "horario": {
    "id": 15,
    "veterinario_id": 1,
    "dia_semana": 1,
    "hora_inicio": "09:00",
    "hora_fin": "18:00",
    "intervalo_minutos": 30,
    "activo": false
  }
}
```

**Uso**: Desactivar temporalmente el horario del Martes (vacaciones) sin borrarlo.

---

## 💻 Integración en Flutter

### **Ejemplo 1: Mostrar Calendario de Slots**

```dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class CalendarioCitas extends StatefulWidget {
  final int veterinarioId;
  
  CalendarioCitas({required this.veterinarioId});
  
  @override
  _CalendarioCitasState createState() => _CalendarioCitasState();
}

class _CalendarioCitasState extends State<CalendarioCitas> {
  List<dynamic> slots = [];
  bool loading = true;
  String fechaSeleccionada = DateTime.now().toString().split(' ')[0];
  
  @override
  void initState() {
    super.initState();
    cargarSlots();
  }
  
  Future<void> cargarSlots() async {
    setState(() => loading = true);
    
    final response = await http.get(
      Uri.parse('http://localhost:8000/api/veterinarios/${widget.veterinarioId}/slots?fecha=$fechaSeleccionada'),
      headers: {
        'Authorization': 'Bearer ${tu_token_aqui}',
        'Accept': 'application/json',
      },
    );
    
    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      setState(() {
        slots = data['slots'];
        loading = false;
      });
    }
  }
  
  @override
  Widget build(BuildContext context) {
    if (loading) {
      return Center(child: CircularProgressIndicator());
    }
    
    return Column(
      children: [
        // Selector de fecha
        ListTile(
          title: Text('Fecha: $fechaSeleccionada'),
          trailing: Icon(Icons.calendar_today),
          onTap: () async {
            final fecha = await showDatePicker(
              context: context,
              initialDate: DateTime.now(),
              firstDate: DateTime.now(),
              lastDate: DateTime.now().add(Duration(days: 90)),
            );
            if (fecha != null) {
              setState(() {
                fechaSeleccionada = fecha.toString().split(' ')[0];
              });
              cargarSlots();
            }
          },
        ),
        
        Divider(),
        
        // Lista de slots
        Expanded(
          child: ListView.builder(
            itemCount: slots.length,
            itemBuilder: (context, index) {
              final slot = slots[index];
              final disponible = slot['disponible'];
              final cita = slot['cita'];
              
              return Card(
                color: disponible ? Colors.green.shade50 : Colors.red.shade50,
                child: ListTile(
                  leading: Icon(
                    disponible ? Icons.check_circle : Icons.event_busy,
                    color: disponible ? Colors.green : Colors.red,
                  ),
                  title: Text(
                    '${slot['hora_inicio']} - ${slot['hora_fin']}',
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                  subtitle: disponible
                    ? Text('Disponible')
                    : Text('${cita['cliente']} - ${cita['mascota']}'),
                  trailing: disponible
                    ? ElevatedButton(
                        child: Text('Agendar'),
                        onPressed: () {
                          // Navegar a formulario de crear cita
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => FormularioCita(
                                veterinarioId: widget.veterinarioId,
                                fechaHora: '$fechaSeleccionada ${slot['hora_inicio']}',
                              ),
                            ),
                          );
                        },
                      )
                    : null,
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}
```

---

### **Ejemplo 2: Vista de Horario Visual (Grid)**

```dart
class VistaHorarioGrid extends StatelessWidget {
  final List<dynamic> slots;
  
  VistaHorarioGrid({required this.slots});
  
  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 3,
        childAspectRatio: 2,
        crossAxisSpacing: 8,
        mainAxisSpacing: 8,
      ),
      itemCount: slots.length,
      itemBuilder: (context, index) {
        final slot = slots[index];
        final disponible = slot['disponible'];
        
        return InkWell(
          onTap: disponible ? () {
            // Agendar cita en este slot
          } : null,
          child: Container(
            decoration: BoxDecoration(
              color: disponible ? Colors.green : Colors.grey.shade300,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(
                color: disponible ? Colors.green.shade700 : Colors.grey,
              ),
            ),
            child: Center(
              child: Text(
                slot['hora_inicio'],
                style: TextStyle(
                  color: disponible ? Colors.white : Colors.black54,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}
```

---

### **Ejemplo 3: Gestión de Horarios en Flutter (Veterinario)**

```dart
class GestionHorariosVeterinario extends StatefulWidget {
  final int veterinarioId;
  
  GestionHorariosVeterinario({required this.veterinarioId});
  
  @override
  _GestionHorariosState createState() => _GestionHorariosState();
}

class _GestionHorariosState extends State<GestionHorariosVeterinario> {
  List<dynamic> horarios = [];
  
  final Map<int, String> diasSemana = {
    0: 'Domingo',
    1: 'Lunes',
    2: 'Martes',
    3: 'Miércoles',
    4: 'Jueves',
    5: 'Viernes',
    6: 'Sábado',
  };
  
  @override
  void initState() {
    super.initState();
    cargarHorarios();
  }
  
  Future<void> cargarHorarios() async {
    final response = await http.get(
      Uri.parse('http://localhost:8000/api/veterinarios/${widget.veterinarioId}/disponibilidad'),
      headers: {
        'Authorization': 'Bearer ${tu_token}',
        'Accept': 'application/json',
      },
    );
    
    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      setState(() {
        horarios = data['horarios_configurados'];
      });
    }
  }
  
  Future<void> toggleHorario(int horarioId, bool activo) async {
    final response = await http.patch(
      Uri.parse('http://localhost:8000/api/veterinarios/${widget.veterinarioId}/horarios/$horarioId/toggle'),
      headers: {
        'Authorization': 'Bearer ${tu_token}',
        'Accept': 'application/json',
      },
    );
    
    if (response.statusCode == 200) {
      cargarHorarios(); // Recargar lista
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(activo ? 'Horario desactivado' : 'Horario activado')),
      );
    }
  }
  
  Future<void> eliminarHorario(int horarioId) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('¿Eliminar horario?'),
        content: Text('Esta acción no se puede deshacer'),
        actions: [
          TextButton(
            child: Text('Cancelar'),
            onPressed: () => Navigator.pop(context, false),
          ),
          TextButton(
            child: Text('Eliminar', style: TextStyle(color: Colors.red)),
            onPressed: () => Navigator.pop(context, true),
          ),
        ],
      ),
    );
    
    if (confirmar == true) {
      final response = await http.delete(
        Uri.parse('http://localhost:8000/api/veterinarios/${widget.veterinarioId}/horarios/$horarioId'),
        headers: {
          'Authorization': 'Bearer ${tu_token}',
          'Accept': 'application/json',
        },
      );
      
      if (response.statusCode == 200) {
        cargarHorarios();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Horario eliminado')),
        );
      }
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Mis Horarios'),
        actions: [
          IconButton(
            icon: Icon(Icons.add),
            onPressed: () {
              // Navegar a formulario para agregar horario
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => AgregarHorarioScreen(
                    veterinarioId: widget.veterinarioId,
                    onGuardado: cargarHorarios,
                  ),
                ),
              );
            },
          ),
        ],
      ),
      body: ListView.builder(
        itemCount: horarios.length,
        itemBuilder: (context, index) {
          final horario = horarios[index];
          final activo = horario['activo'];
          
          return Card(
            margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: ListTile(
              leading: Icon(
                activo ? Icons.check_circle : Icons.cancel,
                color: activo ? Colors.green : Colors.grey,
              ),
              title: Text(
                diasSemana[horario['dia_semana']] ?? 'Día ${horario['dia_semana']}',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              subtitle: Text(
                '${horario['hora_inicio']} - ${horario['hora_fin']} (${horario['intervalo_minutos']} min)',
              ),
              trailing: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  IconButton(
                    icon: Icon(activo ? Icons.pause_circle : Icons.play_circle),
                    onPressed: () => toggleHorario(horario['id'], activo),
                    tooltip: activo ? 'Desactivar' : 'Activar',
                  ),
                  IconButton(
                    icon: Icon(Icons.edit, color: Colors.blue),
                    onPressed: () {
                      // Navegar a formulario de edición
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => EditarHorarioScreen(
                            veterinarioId: widget.veterinarioId,
                            horarioId: horario['id'],
                            horarioActual: horario,
                            onGuardado: cargarHorarios,
                          ),
                        ),
                      );
                    },
                  ),
                  IconButton(
                    icon: Icon(Icons.delete, color: Colors.red),
                    onPressed: () => eliminarHorario(horario['id']),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
```

---

## 🔄 Flujo Completo del Sistema

```
1. BACKEND: Crear veterinario
   └─> Se generan automáticamente horarios Lun-Vie 9am-6pm

2. FLUTTER: Usuario selecciona veterinario y fecha
   └─> GET /api/veterinarios/{id}/slots?fecha=2025-11-10

3. BACKEND: Procesa y calcula
   ├─> Lee horarios configurados en agendas_disponibilidad
   ├─> Lee citas agendadas para esa fecha
   ├─> Genera slots (9:00-9:30, 9:30-10:00, ...)
   └─> Marca cada slot como disponible/ocupado

4. BACKEND: Responde con slots listos
   └─> JSON con array de slots y su estado

5. FLUTTER: Pinta calendario
   ├─> Verde = Disponible → Permite agendar
   └─> Rojo = Ocupado → Muestra info de la cita

6. USUARIO: Selecciona slot verde
   └─> Navega a formulario de crear cita

7. FLUTTER: Crea cita
   └─> POST /api/citas con fecha/hora seleccionada

8. BACKEND: Valida y crea cita
   ├─> Verifica que no haya solapamiento
   └─> Guarda en base de datos

9. FLUTTER: Recarga slots
   └─> El slot ahora aparece ocupado (rojo)
```

---

## 🎨 Diseño Visual Sugerido

### **Opción 1: Lista con Tarjetas**
```
┌─────────────────────────────────┐
│  📅 Fecha: 10/11/2025          │
├─────────────────────────────────┤
│ ✅ 09:00 - 09:30  [AGENDAR]    │ ← Verde (disponible)
├─────────────────────────────────┤
│ ❌ 09:30 - 10:00                │ ← Rojo (ocupado)
│    María G. - Firulais          │
├─────────────────────────────────┤
│ ✅ 10:00 - 10:30  [AGENDAR]    │ ← Verde (disponible)
├─────────────────────────────────┤
│ ✅ 10:30 - 11:00  [AGENDAR]    │
└─────────────────────────────────┘
```

### **Opción 2: Grid (Calendario)**
```
┌─────┬─────┬─────┐
│09:00│09:30│10:00│ ← Fila 1
│  ✅ │  ❌ │  ✅ │
├─────┼─────┼─────┤
│10:30│11:00│11:30│ ← Fila 2
│  ✅ │  ✅ │  ❌ │
└─────┴─────┴─────┘
```

---

## ⚙️ Configuración Avanzada

### **Escenario 1: Reemplazar Todos los Horarios**

```http
POST /api/veterinarios/1/disponibilidad
```

**Ejemplo: Trabajar solo Martes y Jueves, 14:00-20:00**
```json
{
  "horarios": [
    {
      "dia_semana": 2,
      "hora_inicio": "14:00",
      "hora_fin": "20:00",
      "intervalo_minutos": 45,
      "activo": true
    },
    {
      "dia_semana": 4,
      "hora_inicio": "14:00",
      "hora_fin": "20:00",
      "intervalo_minutos": 45,
      "activo": true
    }
  ]
}
```
⚠️ Esto **elimina** los horarios anteriores (Lun-Vie 9am-6pm).

---

### **Escenario 2: Agregar Sábado sin Borrar Lun-Vie**

```http
POST /api/veterinarios/1/horarios
```

**Body**:
```json
{
  "dia_semana": 6,
  "hora_inicio": "10:00",
  "hora_fin": "14:00",
  "intervalo_minutos": 30,
  "activo": true
}
```
✅ Mantiene los horarios Lun-Vie y agrega Sábado.

---

### **Escenario 3: Modificar Solo el Lunes**

1. **Obtener los horarios actuales:**
```http
GET /api/veterinarios/1/disponibilidad
```

2. **Identificar el ID del horario del Lunes** (ej: `horario_id = 5`)

3. **Actualizar solo ese horario:**
```http
PUT /api/veterinarios/1/horarios/5
```

**Body**:
```json
{
  "hora_inicio": "10:00",
  "hora_fin": "15:00"
}
```
✅ Solo modifica el Lunes, mantiene Martes-Viernes intactos.

---

### **Escenario 4: Desactivar Temporalmente (Vacaciones)**

```http
PATCH /api/veterinarios/1/horarios/5/toggle
```

✅ Desactiva el horario sin eliminarlo. Puedes reactivarlo después con el mismo endpoint.

---

### **Escenario 5: Eliminar Solo un Día**

```http
DELETE /api/veterinarios/1/horarios/5
```

✅ Elimina solo ese horario específico.

---

## 🚨 Casos Especiales

### **Caso 1: Veterinario sin horarios configurados**
```json
{
  "mensaje": "No hay horarios configurados para este día",
  "slots": []
}
```
**Solución**: Configurar horarios con POST /api/veterinarios/{id}/disponibilidad

---

### **Caso 2: Día sin horario (ej: Sábado/Domingo)**
```json
{
  "mensaje": "No hay horarios configurados para este día",
  "slots": []
}
```
**Solución**: Agregar horarios para esos días si se desea trabajar

---

### **Caso 3: Cita cancelada**
Las citas con estado `"cancelada"` **NO** ocupan slots. Se consideran como disponibles.

---

## 📝 Notas Importantes

1. **Horarios por defecto**: Lun-Vie, 9am-6pm, intervalos 30 min
2. **Slots disponibles**: Se calculan en tiempo real cada vez que se consulta
3. **Citas canceladas**: NO bloquean slots
4. **Solapamiento**: El sistema previene citas que se solapen
5. **Zona horaria**: Usa la configuración de Laravel (config/app.php)
6. **Intervalo mínimo**: 10 minutos
7. **Intervalo máximo**: 120 minutos (2 horas)

---

## 🔍 Debugging

### **Ver horarios configurados de un veterinario:**
```sql
SELECT * FROM agendas_disponibilidad WHERE veterinario_id = 1;
```

### **Ver citas de un veterinario en una fecha:**
```sql
SELECT * FROM citas 
WHERE veterinario_id = 1 
  AND DATE(fecha) = '2025-11-10'
  AND estado != 'cancelada';
```

### **Probar endpoint desde terminal:**
```bash
curl -X GET "http://localhost:8000/api/veterinarios/1/slots?fecha=2025-11-10" \
  -H "Authorization: Bearer tu_token_aqui" \
  -H "Accept: application/json"
```

---

## 📋 Resumen de Endpoints

| Método | Endpoint | Descripción | Elimina otros |
|--------|----------|-------------|---------------|
| GET | `/api/veterinarios/{id}/slots?fecha=...` | 🆕 Slots disponibles/ocupados | - |
| GET | `/api/veterinarios/{id}/disponibilidad?fecha=...` | Datos crudos (horarios + citas) | - |
| POST | `/api/veterinarios/{id}/disponibilidad` | ⚠️ Reemplazar TODOS los horarios | ✅ SÍ |
| POST | `/api/veterinarios/{id}/horarios` | ✅ Agregar un horario | ❌ NO |
| PUT | `/api/veterinarios/{id}/horarios/{horarioId}` | ✅ Editar un horario específico | ❌ NO |
| DELETE | `/api/veterinarios/{id}/horarios/{horarioId}` | ✅ Eliminar un horario específico | ❌ NO |
| PATCH | `/api/veterinarios/{id}/horarios/{horarioId}/toggle` | ✅ Activar/Desactivar horario | ❌ NO |

---

## ✅ Checklist de Implementación

**Backend:**
- [x] Backend genera horarios por defecto al crear veterinario
- [x] Endpoint `/slots` devuelve slots disponibles/ocupados
- [x] Sistema detecta solapamiento de citas
- [x] Endpoint para reemplazar todos los horarios
- [x] Endpoint para agregar horario individual
- [x] Endpoint para editar horario individual
- [x] Endpoint para eliminar horario individual
- [x] Endpoint para activar/desactivar horario

**Frontend (Flutter):**
- [ ] Pantalla de calendario de slots
- [ ] Selector de fecha
- [ ] Botón "Agendar" en slots disponibles
- [ ] Mostrar info de cita en slots ocupados
- [ ] Formulario de crear cita con fecha/hora pre-seleccionada
- [ ] Pantalla de gestión de horarios del veterinario
- [ ] Toggle para activar/desactivar días
- [ ] Botón para agregar horario de fin de semana

---

## 🎯 Próximos Pasos Sugeridos

1. **Implementar vista de calendario en Flutter** usando el código de ejemplo
2. **Probar crear una cita** desde la app móvil
3. **Verificar que el slot se marca como ocupado** después de crear la cita
4. **Agregar filtro por veterinario** para que clientes puedan elegir
5. **Agregar notificaciones push** cuando se agenda una cita

---

**Última actualización**: 8 de Noviembre, 2025
