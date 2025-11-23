# 👥 Funcionalidades por Rol - VetCare App

## 📊 **Tabla Comparativa Rápida**

| Funcionalidad | Cliente | Veterinario | Recepcionista | Admin |
|--------------|---------|-------------|---------------|-------|
| **VER** mis mascotas | ✅ | ❌ | ❌ | ❌ |
| **VER** todas las mascotas | ❌ | ✅ | ✅ | ✅ |
| **REGISTRAR** mascota | ✅ | ❌ | ✅ | ✅ |
| **EDITAR** mi mascota | ✅ | ❌ | ✅ | ✅ |
| **ELIMINAR** mascota | ❌ | ❌ | ✅ | ✅ |
| **VER QR** de mi mascota | ✅ | ❌ | ❌ | ❌ |
| **ESCANEAR QR** de mascotas | ❌ | ✅ | ✅ | ✅ |
| **AGENDAR** mi cita | ✅ | ❌ | ✅ | ✅ |
| **VER** mis citas | ✅ | ✅ | ✅ | ✅ |
| **CANCELAR** mi cita | ✅ | ❌ | ✅ | ✅ |
| **GESTIONAR** todas las citas | ❌ | ❌ | ✅ | ✅ |
| **VER** historial médico | ✅ | ✅ | ✅ | ✅ |
| **CREAR** historial médico | ❌ | ✅ | ❌ | ✅ |
| **EDITAR** historial médico | ❌ | ✅ | ❌ | ✅ |
| **VER** mis facturas | ✅ | ❌ | ❌ | ❌ |
| **CREAR** facturas | ❌ | ❌ | ✅ | ✅ |
| **GESTIONAR** clientes | ❌ | ❌ | ✅ | ✅ |
| **REGISTRAR** walk-in | ❌ | ❌ | ✅ | ✅ |
| **CONFIGURAR** disponibilidad | ❌ | ✅ | ❌ | ✅ |
| **VER** notificaciones | ✅ | ✅ | ✅ | ✅ |
| **GESTIONAR** usuarios | ❌ | ❌ | ❌ | ✅ |
| **GESTIONAR** veterinarios | ❌ | ❌ | ❌ | ✅ |
| **GESTIONAR** servicios | ❌ | ❌ | ❌ | ✅ |
| **VER** reportes | ❌ | ❌ | ❌ | ✅ |

---

## 👤 **ROL: CLIENTE**

### 🏠 **Dashboard**
```
📊 Resumen Personal:
├── Mis mascotas (resumen)
├── Próximas citas (3)
├── Facturas pendientes
└── Notificaciones recientes
```

### 🐾 **Mis Mascotas**
**Funcionalidades:**
- ✅ Ver lista de mis mascotas
- ✅ Registrar nueva mascota
- ✅ Editar datos de mi mascota
- ✅ Ver detalle completo de mascota
- ✅ Ver QR de mi mascota
- ✅ Compartir QR
- ❌ Eliminar mascota (contactar recepción)

**Endpoints:**
```
GET    /api/mascotas?cliente_id={mi_id}
POST   /api/mascotas
PUT    /api/mascotas/{id}
GET    /api/mascotas/{id}
GET    /api/mascotas/{id}/qr
```

### 📅 **Mis Citas**
**Funcionalidades:**
- ✅ Ver mis citas (pasadas y futuras)
- ✅ Agendar nueva cita
- ✅ Ver disponibilidad de veterinarios
- ✅ Cancelar mi cita (solo pendientes)
- ✅ Ver detalle de cita
- ❌ Modificar cita existente (contactar recepción)

**Proceso de Agendamiento:**
```
1. Seleccionar mascota
2. Seleccionar servicio
3. Seleccionar veterinario
4. Ver disponibilidad del veterinario
5. Seleccionar fecha y hora
6. Confirmar cita
7. Recibir notificación
```

**Endpoints:**
```
GET    /api/citas?cliente_id={mi_id}
POST   /api/citas
DELETE /api/citas/{id}
GET    /api/veterinarios/{id}/disponibilidad
GET    /api/servicios
```

### 📋 **Historial Médico**
**Funcionalidades:**
- ✅ Ver historial de mis mascotas
- ✅ Ver detalle de consultas
- ✅ Ver diagnósticos
- ✅ Ver tratamientos
- ✅ Ver vacunas aplicadas
- ❌ Crear/editar registros (solo veterinario)

**Endpoints:**
```
GET    /api/historial-medico?mascota_id={id}
GET    /api/historial-medico/{id}
```

### 💰 **Mis Facturas**
**Funcionalidades:**
- ✅ Ver mis facturas
- ✅ Ver detalle de factura
- ✅ Filtrar por estado (pagada/pendiente)
- ❌ Crear facturas (solo recepción)
- ❌ Modificar facturas

**Endpoints:**
```
GET    /api/facturas?cliente_id={mi_id}
GET    /api/facturas/{id}
```

### 🔔 **Notificaciones**
**Funcionalidades:**
- ✅ Ver notificaciones
- ✅ Marcar como leída
- ✅ Ver recordatorios de citas
- ✅ Ver resultados de consultas
- ✅ Eliminar notificaciones

**Endpoints:**
```
GET    /api/notificaciones
POST   /api/notificaciones/{id}/mark-read
DELETE /api/notificaciones/{id}
```

### 👤 **Mi Perfil**
**Funcionalidades:**
- ✅ Ver mis datos
- ✅ Editar mi información
- ✅ Cambiar contraseña
- ✅ Actualizar foto de perfil
- ✅ Cerrar sesión

---

## 👨‍⚕️ **ROL: VETERINARIO**

### 🏥 **Dashboard**
```
📊 Resumen Profesional:
├── Citas del día (agenda)
├── Pacientes atendidos hoy
├── Consultas pendientes
└── Notificaciones urgentes
```

### 📅 **Mi Agenda**
**Funcionalidades:**
- ✅ Ver mis citas del día/semana/mes
- ✅ Ver detalle de cita
- ✅ Marcar cita como: atendida/cancelada/no asistió
- ✅ Ver información del paciente (mascota)
- ✅ Acceder rápido al historial
- ❌ Agendar citas (lo hace recepción o cliente)

**Endpoints:**
```
GET    /api/citas?veterinario_id={mi_id}
PUT    /api/citas/{id}
GET    /api/citas/{id}
```

### 🔬 **Pacientes (Todas las Mascotas)**
**Funcionalidades:**
- ✅ Ver todas las mascotas del sistema
- ✅ Buscar por nombre/especie/dueño
- ✅ Ver detalle de mascota
- ✅ Escanear QR de mascota
- ✅ Ver historial completo
- ❌ Editar datos de mascota (solo recepción)

**Endpoints:**
```
GET    /api/mascotas
GET    /api/mascotas/{id}
GET    /api/qr/lookup/{qr_code}
```

### 📝 **Historial Médico**
**Funcionalidades:**
- ✅ **CREAR** nuevo registro médico
- ✅ **EDITAR** registro médico
- ✅ Ver historial de cualquier mascota
- ✅ Registrar diagnóstico
- ✅ Registrar tratamiento
- ✅ Registrar vacunas
- ✅ Adjuntar archivos (radiografías, etc.)
- ✅ Agregar notas del veterinario

**Tipos de registro:**
- Consulta general
- Vacunación
- Procedimiento/Cirugía
- Control
- Emergencia

**Endpoints:**
```
GET    /api/historial-medico
POST   /api/historial-medico
PUT    /api/historial-medico/{id}
GET    /api/historial-medico/{id}
POST   /api/historial-medico/{id}/archivos
```

### ⏰ **Mi Disponibilidad**
**Funcionalidades:**
- ✅ **CONFIGURAR** mi horario de atención
- ✅ Definir días laborables
- ✅ Definir horarios por día
- ✅ Definir intervalos de citas (15, 30, 60 min)
- ✅ Bloquear fechas específicas (vacaciones)
- ✅ Ver mi agenda ocupada

**Ejemplo de configuración:**
```json
{
  "lunes": ["09:00-13:00", "16:00-20:00"],
  "martes": ["09:00-13:00", "16:00-20:00"],
  "miercoles": ["09:00-13:00"],
  "jueves": ["09:00-13:00", "16:00-20:00"],
  "viernes": ["09:00-13:00", "16:00-19:00"],
  "sabado": [],
  "domingo": []
}
```

**Endpoints:**
```
GET    /api/veterinarios/{mi_id}/disponibilidad
POST   /api/veterinarios/{mi_id}/disponibilidad
```

### 📱 **Escanear QR**
**Funcionalidades:**
- ✅ Escanear QR de mascota
- ✅ Ver información instantánea
- ✅ Acceder rápido al historial
- ✅ Ver alergias/condiciones médicas
- ✅ Registrar escaneo (auditoría)

**Endpoints:**
```
GET    /api/qr/lookup/{qr_code}
POST   /api/qr/scan-log
```

### 🔔 **Notificaciones**
**Funcionalidades:**
- ✅ Ver notificaciones
- ✅ Recordatorios de citas
- ✅ Alertas de emergencia
- ✅ Solicitudes de disponibilidad

---

## 👩‍💼 **ROL: RECEPCIONISTA**

### 📊 **Dashboard**
```
📊 Resumen Operacional:
├── Citas del día (calendario)
├── Clientes walk-in atendidos
├── Facturas generadas hoy
└── Próximas citas (3 horas)
```

### 🚶 **REGISTRO RÁPIDO WALK-IN** ⭐
**Funcionalidad PRINCIPAL de Recepcionista**

**Proceso:**
```
1. Cliente llega sin cuenta
2. Tap "Registro Rápido"
3. Llenar formulario mínimo:
   - Nombre cliente
   - Teléfono cliente
   - Nombre mascota
   - Especie
   - Sexo
4. Registrar → QR generado
5. Cliente se va con su QR
```

**Endpoint:**
```
POST   /api/clientes/registro-rapido
```

### 👥 **Gestión de Clientes**
**Funcionalidades:**
- ✅ **VER** todos los clientes
- ✅ **CREAR** nuevo cliente (con/sin cuenta)
- ✅ **EDITAR** datos de cliente
- ✅ **ELIMINAR** cliente
- ✅ Filtrar: Walk-in / Registrados
- ✅ Buscar por nombre/teléfono/email
- ✅ Ver historial de visitas

**Endpoints:**
```
GET    /api/clientes
POST   /api/clientes
PUT    /api/clientes/{id}
DELETE /api/clientes/{id}
GET    /api/clientes?es_walk_in=true
GET    /api/clientes?es_walk_in=false
POST   /api/clientes/registro-rapido
```

### 🐾 **Gestión de Mascotas**
**Funcionalidades:**
- ✅ **VER** todas las mascotas
- ✅ **CREAR** nueva mascota
- ✅ **EDITAR** datos de mascota
- ✅ **ELIMINAR** mascota
- ✅ Buscar por nombre/dueño
- ✅ Generar QR de mascota
- ✅ Imprimir QR

**Endpoints:**
```
GET    /api/mascotas
POST   /api/mascotas
PUT    /api/mascotas/{id}
DELETE /api/mascotas/{id}
GET    /api/mascotas/{id}/qr
```

### 📅 **Gestión de Citas (TODAS)**
**Funcionalidades:**
- ✅ **VER** todas las citas del sistema
- ✅ **CREAR** cita para cualquier cliente
- ✅ **EDITAR** cita existente
- ✅ **CANCELAR** cualquier cita
- ✅ Vista de calendario
- ✅ Filtrar por veterinario/fecha
- ✅ Ver disponibilidad de veterinarios
- ✅ Confirmación de citas

**Vistas disponibles:**
- Vista calendario (día/semana/mes)
- Vista lista
- Vista por veterinario

**Endpoints:**
```
GET    /api/citas
POST   /api/citas
PUT    /api/citas/{id}
DELETE /api/citas/{id}
GET    /api/veterinarios/{id}/disponibilidad
```

### 💰 **Gestión de Facturas**
**Funcionalidades:**
- ✅ **VER** todas las facturas
- ✅ **CREAR** nueva factura
- ✅ **EDITAR** factura (solo pendientes)
- ✅ Marcar como pagada
- ✅ Ver estadísticas
- ✅ Filtrar por estado/fecha
- ✅ Buscar por cliente
- ✅ Generar número de factura

**Endpoints:**
```
GET    /api/facturas
POST   /api/facturas
PUT    /api/facturas/{id}
GET    /api/facturas/{id}
GET    /api/facturas-estadisticas
GET    /api/generar-numero-factura
```

### 📋 **Ver Servicios**
**Funcionalidades:**
- ✅ Ver lista de servicios
- ✅ Ver precios
- ✅ Filtrar por tipo
- ❌ Crear/editar servicios (solo admin)

**Endpoints:**
```
GET    /api/servicios
GET    /api/servicios-tipos
```

### 📱 **Escanear QR**
**Funcionalidades:**
- ✅ Escanear QR de mascota
- ✅ Ver información del cliente
- ✅ Acceder rápido al historial
- ✅ Agendar cita directa

---

## 🔧 **ROL: ADMINISTRADOR**

### 🎛️ **Dashboard**
```
📊 Resumen Completo del Sistema:
├── Total usuarios activos
├── Citas del día
├── Ingresos del mes
├── Clientes nuevos
├── Estadísticas generales
└── Alertas del sistema
```

### 👥 **Gestión de Usuarios**
**Funcionalidades:**
- ✅ **VER** todos los usuarios
- ✅ **CREAR** nuevo usuario
- ✅ **EDITAR** usuario
- ✅ **ELIMINAR** usuario
- ✅ **ASIGNAR** roles (Cliente/Veterinario/Recepción/Admin)
- ✅ **ACTIVAR/DESACTIVAR** usuarios
- ✅ **RESETEAR** contraseñas
- ✅ Ver historial de actividad

**Roles disponibles:**
- Cliente
- Veterinario
- Recepcionista
- Administrador

**Endpoints:**
```
GET    /api/usuarios (implementar)
POST   /api/usuarios (implementar)
PUT    /api/usuarios/{id} (implementar)
DELETE /api/usuarios/{id} (implementar)
```

### 👨‍⚕️ **Gestión de Veterinarios**
**Funcionalidades:**
- ✅ **VER** todos los veterinarios
- ✅ **CREAR** nuevo veterinario
- ✅ **EDITAR** datos de veterinario
- ✅ **ELIMINAR** veterinario
- ✅ Asignar especialidades
- ✅ Configurar disponibilidad
- ✅ Ver estadísticas de atención

**Endpoints:**
```
GET    /api/veterinarios
POST   /api/veterinarios
PUT    /api/veterinarios/{id}
DELETE /api/veterinarios/{id}
```

### 🛠️ **Gestión de Servicios**
**Funcionalidades:**
- ✅ **VER** todos los servicios
- ✅ **CREAR** nuevo servicio
- ✅ **EDITAR** servicio
- ✅ **ELIMINAR** servicio
- ✅ Definir precios
- ✅ Categorizar por tipo
- ✅ Activar/desactivar servicios

**Tipos de servicios:**
- Consulta
- Vacunación
- Cirugía
- Análisis
- Grooming
- Emergencia
- Hospitalización

**Endpoints:**
```
GET    /api/servicios
POST   /api/servicios
PUT    /api/servicios/{id}
DELETE /api/servicios/{id}
```

### 📊 **Reportes y Estadísticas**
**Funcionalidades:**
- ✅ Reporte de ingresos
- ✅ Reporte de citas
- ✅ Reporte de clientes nuevos
- ✅ Reporte por veterinario
- ✅ Reporte de servicios más solicitados
- ✅ Exportar a PDF/Excel
- ✅ Filtros por fecha

**Endpoints:**
```
GET    /api/reportes/ingresos (implementar)
GET    /api/reportes/citas (implementar)
GET    /api/reportes/clientes (implementar)
```

### ⚙️ **Configuración del Sistema**
**Funcionalidades:**
- ✅ Configurar nombre de la clínica
- ✅ Configurar logo
- ✅ Configurar horarios generales
- ✅ Configurar notificaciones
- ✅ Configurar Firebase
- ✅ Ver logs del sistema
- ✅ Gestionar backups

### 🔔 **Notificaciones Masivas**
**Funcionalidades:**
- ✅ Enviar notificación a todos los clientes
- ✅ Enviar a un grupo específico
- ✅ Programar notificaciones
- ✅ Ver historial de envíos

### 🔍 **Auditoría**
**Funcionalidades:**
- ✅ Ver logs de actividad
- ✅ Ver cambios en registros
- ✅ Ver accesos al sistema
- ✅ Filtrar por usuario/fecha/acción

---

## 🔐 **Matriz de Permisos**

### **Mascotas:**
| Acción | Cliente | Veterinario | Recepción | Admin |
|--------|---------|-------------|-----------|-------|
| Ver propias | ✅ | - | - | - |
| Ver todas | ❌ | ✅ | ✅ | ✅ |
| Crear | ✅ | ❌ | ✅ | ✅ |
| Editar propias | ✅ | ❌ | - | - |
| Editar todas | ❌ | ❌ | ✅ | ✅ |
| Eliminar | ❌ | ❌ | ✅ | ✅ |
| Ver QR | ✅ | ✅ | ✅ | ✅ |
| Escanear QR | ❌ | ✅ | ✅ | ✅ |

### **Citas:**
| Acción | Cliente | Veterinario | Recepción | Admin |
|--------|---------|-------------|-----------|-------|
| Ver propias | ✅ | ✅ | - | - |
| Ver todas | ❌ | ❌ | ✅ | ✅ |
| Agendar propia | ✅ | ❌ | - | - |
| Agendar cualquiera | ❌ | ❌ | ✅ | ✅ |
| Cancelar propia | ✅ | ❌ | - | - |
| Cancelar cualquiera | ❌ | ❌ | ✅ | ✅ |
| Modificar | ❌ | ❌ | ✅ | ✅ |
| Cambiar estado | ❌ | ✅ | ✅ | ✅ |

### **Historial Médico:**
| Acción | Cliente | Veterinario | Recepción | Admin |
|--------|---------|-------------|-----------|-------|
| Ver propio | ✅ | - | - | - |
| Ver todos | ❌ | ✅ | ✅ | ✅ |
| Crear | ❌ | ✅ | ❌ | ✅ |
| Editar | ❌ | ✅ | ❌ | ✅ |
| Eliminar | ❌ | ❌ | ❌ | ✅ |

### **Facturas:**
| Acción | Cliente | Veterinario | Recepción | Admin |
|--------|---------|-------------|-----------|-------|
| Ver propias | ✅ | - | - | - |
| Ver todas | ❌ | ❌ | ✅ | ✅ |
| Crear | ❌ | ❌ | ✅ | ✅ |
| Editar | ❌ | ❌ | ✅ | ✅ |
| Marcar pagada | ❌ | ❌ | ✅ | ✅ |
| Eliminar | ❌ | ❌ | ❌ | ✅ |

### **Clientes:**
| Acción | Cliente | Veterinario | Recepción | Admin |
|--------|---------|-------------|-----------|-------|
| Ver propios | ✅ | - | - | - |
| Ver todos | ❌ | ❌ | ✅ | ✅ |
| Crear | ❌ | ❌ | ✅ | ✅ |
| Registrar walk-in | ❌ | ❌ | ✅ | ✅ |
| Editar | ✅ | ❌ | ✅ | ✅ |
| Eliminar | ❌ | ❌ | ✅ | ✅ |

### **Sistema:**
| Acción | Cliente | Veterinario | Recepción | Admin |
|--------|---------|-------------|-----------|-------|
| Gestionar usuarios | ❌ | ❌ | ❌ | ✅ |
| Gestionar servicios | ❌ | ❌ | ❌ | ✅ |
| Ver reportes | ❌ | ❌ | ❌ | ✅ |
| Configurar sistema | ❌ | ❌ | ❌ | ✅ |
| Ver auditoría | ❌ | ❌ | ❌ | ✅ |

---

## 🎯 **Flujos Principales por Rol**

### **Cliente: Agendar Cita**
```
1. Login a la app
2. Dashboard → Ver mis mascotas
3. Seleccionar mascota → "Agendar Cita"
4. Seleccionar servicio
5. Ver veterinarios disponibles
6. Seleccionar veterinario
7. Seleccionar fecha
8. Ver horarios disponibles
9. Seleccionar hora
10. Confirmar → Recibir notificación
```

### **Veterinario: Atender Paciente**
```
1. Login a la app
2. Dashboard → Ver agenda del día
3. Seleccionar cita → Ver detalles
4. Ver historial del paciente
5. Realizar consulta
6. Registrar diagnóstico
7. Registrar tratamiento
8. Adjuntar archivos (opcional)
9. Guardar → Marcar cita como atendida
10. Cliente recibe notificación con resumen
```

### **Recepcionista: Atender Walk-In**
```
1. Login a la app
2. Dashboard → "Registro Rápido Walk-In"
3. Llenar datos mínimos:
   - Nombre cliente
   - Teléfono
   - Nombre mascota
   - Especie
   - Sexo
4. Tap "Registrar"
5. Sistema genera QR
6. Mostrar QR al cliente
7. [Opcional] Imprimir QR
8. Cliente se retira con su QR
9. Próxima visita → Escanear QR → Info instantánea
```

### **Administrador: Crear Veterinario**
```
1. Login a la app
2. Dashboard Admin → "Gestión de Veterinarios"
3. Tap "Nuevo Veterinario"
4. Llenar datos:
   - Nombre
   - Email
   - Teléfono
   - Matrícula profesional
   - Especialidad
5. Configurar disponibilidad:
   - Días laborables
   - Horarios por día
   - Intervalos de citas
6. Guardar
7. Sistema crea usuario automáticamente
8. Veterinario recibe email con credenciales
```

---

## 📱 **Resumen de Pantallas por Rol**

### **Cliente: 7 Pantallas**
1. Dashboard
2. Mis Mascotas
3. Detalle Mascota
4. Agendar Cita
5. Mis Citas
6. Mis Facturas
7. Mi Perfil

### **Veterinario: 6 Pantallas**
1. Dashboard
2. Mi Agenda
3. Pacientes (todas las mascotas)
4. Registrar Consulta
5. Configurar Disponibilidad
6. Mi Perfil

### **Recepcionista: 9 Pantallas**
1. Dashboard
2. Registro Rápido Walk-In ⭐
3. Gestión de Clientes
4. Gestión de Mascotas
5. Calendario de Citas
6. Gestión de Facturas
7. Escanear QR
8. Servicios
9. Mi Perfil

### **Admin: 10+ Pantallas**
1. Dashboard
2. Gestión de Usuarios
3. Gestión de Veterinarios
4. Gestión de Servicios
5. Gestión de Clientes
6. Gestión de Citas
7. Reportes
8. Estadísticas
9. Configuración
10. Auditoría

---

## 🚀 **Estado de Implementación**

### ✅ **Completado (Backend):**
- Sistema de autenticación
- CRUD de clientes, mascotas, citas, veterinarios
- Sistema QR por mascota
- Sistema walk-in
- Historial médico
- Facturas
- Notificaciones
- FCM tokens
- Disponibilidad de veterinarios

### 🔄 **En Desarrollo (Flutter):**
- Pantallas por rol
- Registro walk-in
- Filtros de clientes
- Sistema de badges

### 📋 **Pendiente:**
- Gestión de usuarios (admin)
- Reportes y estadísticas
- Sistema de backups
- Exportación PDF
- Notificaciones masivas

---

📅 **Fecha:** 8 de noviembre de 2025
🔧 **Backend:** Laravel 12.37.0
📱 **Frontend:** Flutter 3.x+
👥 **Roles:** 4 (Cliente, Veterinario, Recepcionista, Admin)
