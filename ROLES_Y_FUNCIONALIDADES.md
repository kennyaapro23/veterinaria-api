# 👥 Roles, Vistas y Funcionalidades del Sistema VetCare

> **🎯 Estado Actual:** Backend 100% completo  
> **✅ Roles Implementados:** 3 (Cliente, Veterinario, Recepcionista)  
> **🔮 Futuro:** Rol Admin en desarrollo futuro

## 📊 **Resumen de Roles**

El sistema VetCare tiene **3 roles implementados**:

1. 👤 **Cliente** - Dueño de mascotas
2. 🩺 **Veterinario** - Médico veterinario
3. 📋 **Recepcionista** - Personal administrativo

> **Nota:** El rol Administrador está planeado para una versión futura

---

## 1️⃣ **ROL: CLIENTE** 👤

### **Descripción:**
Dueño de mascotas que puede gestionar sus animales, agendar citas y consultar historial médico.

### **Vistas (Pantallas en Flutter):**

#### 📱 **Dashboard Cliente**
- Resumen de mascotas registradas
- Próximas citas agendadas
- Notificaciones pendientes
- Acceso rápido a QR de mascotas

#### 🐾 **Mis Mascotas**
- **Lista de mascotas:** Ver todas sus mascotas
- **Detalles de mascota:** Info completa (nombre, especie, raza, edad, foto)
- **Código QR:** Ver/compartir QR único de cada mascota
- **Historial médico:** Consultas, vacunas, tratamientos
- **Galería de fotos:** Fotos de la mascota

#### 📅 **Mis Citas**
- **Agendar nueva cita:**
  - Ver veterinarios disponibles
  - Seleccionar fecha y hora disponible
  - Elegir mascota
  - Seleccionar servicio (consulta, vacuna, cirugía, etc.)
  - Agregar motivo/notas
- **Citas pendientes:** Ver citas próximas
- **Historial de citas:** Ver citas pasadas
- **Cancelar/reprogramar cita:** Gestionar citas existentes
- **Recordatorios:** Recibir notificaciones push antes de la cita

#### 💰 **Mis Facturas**
- Ver facturas pendientes
- Ver facturas pagadas
- Descargar/compartir facturas
- Ver detalles de cada factura

#### 🔔 **Notificaciones**
- Recordatorios de citas
- Confirmaciones de citas
- Resultados de análisis
- Promociones de la clínica

#### 👤 **Mi Perfil**
- Ver/editar información personal
- Cambiar foto de perfil
- Actualizar teléfono/email
- Cambiar contraseña
- Cerrar sesión

---

### **Funcionalidades Detalladas:**

#### ✅ **Gestión de Mascotas**
| Funcionalidad | Endpoint | Método |
|--------------|----------|---------|
| Ver lista de mis mascotas | `/api/mascotas?cliente_id={id}` | GET |
| Ver detalle de mascota | `/api/mascotas/{id}` | GET |
| Registrar nueva mascota | `/api/mascotas` | POST |
| Editar datos de mascota | `/api/mascotas/{id}` | PUT |
| Eliminar mascota | `/api/mascotas/{id}` | DELETE |
| Generar QR de mascota | `/api/mascotas/{id}/qr` | GET |

**Campos al registrar mascota:**
```json
{
  "cliente_id": 1,
  "nombre": "Max",
  "especie": "Perro",
  "raza": "Labrador",
  "sexo": "macho",
  "fecha_nacimiento": "2020-03-15",
  "color": "Dorado",
  "chip_id": "981234567890123",
  "foto_url": "https://...",
  "alergias": "Penicilina",
  "condiciones_medicas": "Displasia de cadera",
  "tipo_sangre": "DEA 1.1+",
  "microchip": "123456789"
}
```

#### ✅ **Agendar Citas (con validación de disponibilidad)**
| Funcionalidad | Endpoint | Método |
|--------------|----------|---------|
| Ver veterinarios disponibles | `/api/veterinarios` | GET |
| Ver disponibilidad de veterinario | `/api/veterinarios/{id}/disponibilidad?fecha={date}` | GET |
| Agendar nueva cita | `/api/citas` | POST |
| Ver mis citas | `/api/citas?cliente_id={id}` | GET |
| Cancelar cita | `/api/citas/{id}` | DELETE |
| Reprogramar cita | `/api/citas/{id}` | PUT |

**Flujo para agendar cita:**
1. Cliente selecciona mascota
2. Cliente selecciona servicio (consulta, vacuna, cirugía, etc.)
3. Sistema muestra veterinarios disponibles
4. Cliente selecciona veterinario
5. Sistema muestra horarios disponibles del veterinario
6. Cliente selecciona fecha y hora
7. Sistema valida disponibilidad:
   - ✅ Horario dentro del rango de disponibilidad del veterinario
   - ✅ No hay otra cita en ese horario
   - ✅ Respeta el intervalo de minutos configurado (default 30 min)
8. Sistema agenda cita y envía notificación

**Ejemplo de request para agendar cita:**
```json
{
  "cliente_id": 1,
  "mascota_id": 2,
  "veterinario_id": 3,
  "fecha": "2025-11-10",
  "hora": "10:00",
  "motivo": "Consulta de rutina",
  "servicio_ids": [1, 2],
  "notas": "Max tiene tos desde hace 3 días"
}
```

#### ✅ **Ver Historial Médico**
| Funcionalidad | Endpoint | Método |
|--------------|----------|---------|
| Ver historial de mascota | `/api/historial-medico?mascota_id={id}` | GET |
| Ver detalle de consulta | `/api/historial-medico/{id}` | GET |

**Info que ve el cliente:**
- Fecha de consulta
- Veterinario que atendió
- Diagnóstico
- Tratamiento aplicado
- Medicamentos recetados
- Observaciones
- Archivos adjuntos (rayos X, análisis, etc.)

#### ✅ **Ver Facturas**
| Funcionalidad | Endpoint | Método |
|--------------|----------|---------|
| Ver mis facturas | `/api/facturas?cliente_id={id}` | GET |
| Ver detalle de factura | `/api/facturas/{id}` | GET |

---

## 2️⃣ **ROL: VETERINARIO** 🩺

### **Descripción:**
Médico veterinario que atiende mascotas, gestiona historial médico y configura su disponibilidad.

### **Vistas (Pantallas en Flutter):**

#### 📱 **Dashboard Veterinario**
- Citas del día (calendario)
- Próximas citas
- Estadísticas (pacientes atendidos, citas pendientes)
- Acceso rápido a historial médico

#### 📅 **Mis Citas**
- **Calendario de citas:** Ver todas las citas asignadas
- **Citas del día:** Ver agenda del día actual
- **Detalles de cita:** Ver info completa de la cita
- **Atender cita:** Marcar cita como "en progreso" o "completada"
- **Cancelar/reprogramar cita:** Gestionar citas

#### 🐾 **Pacientes**
- **Lista de pacientes:** Ver mascotas atendidas
- **Buscar mascota:** Por nombre, chip, QR
- **Escanear QR:** Acceso rápido con QR scanner
- **Ver historial médico completo**
- **Ver datos del dueño**

#### 📋 **Historial Médico**
- **Registrar consulta:**
  - Fecha y hora
  - Tipo (consulta, vacuna, procedimiento, control, otro)
  - Diagnóstico
  - Tratamiento
  - Observaciones
  - Adjuntar archivos (rayos X, análisis, recetas)
- **Ver historial de mascota**
- **Editar registro médico**

#### ⏰ **Mi Disponibilidad**
- **Configurar horarios:**
  - Día de la semana (Lunes-Domingo)
  - Hora inicio (ej: 08:00)
  - Hora fin (ej: 18:00)
  - Intervalo de citas (15, 30, 45, 60 minutos)
  - Activar/desactivar día
- **Ver horarios configurados**
- **Bloquear horarios específicos** (vacaciones, reuniones)

#### 👤 **Mi Perfil**
- Ver/editar información personal
- Ver especialidades
- Cambiar foto de perfil
- Cerrar sesión

---

### **Funcionalidades Detalladas:**

#### ✅ **Gestión de Citas**
| Funcionalidad | Endpoint | Método |
|--------------|----------|---------|
| Ver mis citas | `/api/citas?veterinario_id={id}` | GET |
| Ver citas del día | `/api/citas?veterinario_id={id}&fecha={date}` | GET |
| Ver detalle de cita | `/api/citas/{id}` | GET |
| Actualizar estado de cita | `/api/citas/{id}` | PUT |

**Estados de cita:**
- `pendiente` - Agendada
- `confirmada` - Confirmada por cliente
- `en_progreso` - Atendiendo
- `completada` - Finalizada
- `cancelada` - Cancelada

#### ✅ **Gestión de Historial Médico**
| Funcionalidad | Endpoint | Método |
|--------------|----------|---------|
| Ver historial de mascota | `/api/historial-medico?mascota_id={id}` | GET |
| Registrar nueva consulta | `/api/historial-medico` | POST |
| Ver detalle de consulta | `/api/historial-medico/{id}` | GET |
| Adjuntar archivos | `/api/historial-medico/{id}/archivos` | POST |

**Ejemplo de registro médico:**
```json
{
  "mascota_id": 2,
  "cita_id": 10,
  "fecha": "2025-11-08",
  "tipo": "consulta",
  "diagnostico": "Infección respiratoria leve",
  "tratamiento": "Antibiótico Amoxicilina 250mg cada 12h por 7 días",
  "observaciones": "Revisar en 7 días. Si persiste la tos, hacer rayos X.",
  "realizado_por": 3,
  "archivos_meta": []
}
```

**Tipos de consulta:**
- `consulta` - Consulta general
- `vacuna` - Vacunación
- `procedimiento` - Cirugía/procedimiento
- `control` - Control de seguimiento
- `otro` - Otros

#### ✅ **Configurar Disponibilidad**
| Funcionalidad | Endpoint | Método |
|--------------|----------|---------|
| Ver mi disponibilidad | `/api/veterinarios/{id}/disponibilidad` | GET |
| Configurar disponibilidad | `/api/veterinarios/{id}/disponibilidad` | POST |

**Ejemplo de disponibilidad:**
```json
{
  "veterinario_id": 3,
  "horarios": [
    {
      "dia_semana": 1,
      "hora_inicio": "08:00",
      "hora_fin": "12:00",
      "intervalo_minutos": 30,
      "activo": true
    },
    {
      "dia_semana": 1,
      "hora_inicio": "14:00",
      "hora_fin": "18:00",
      "intervalo_minutos": 30,
      "activo": true
    }
  ]
}
```

**Días de la semana:**
- 0 = Domingo
- 1 = Lunes
- 2 = Martes
- 3 = Miércoles
- 4 = Jueves
- 5 = Viernes
- 6 = Sábado

#### ✅ **Escanear QR de Mascota**
| Funcionalidad | Endpoint | Método |
|--------------|----------|---------|
| Buscar mascota por QR | `/api/qr/lookup/{qrCode}` | GET |
| Registrar escaneo | `/api/qr/scan-log` | POST |

**Flujo:**
1. Veterinario escanea QR de mascota
2. Sistema muestra info completa:
   - Datos de mascota (nombre, especie, raza, edad)
   - Alergias y condiciones médicas
   - Tipo de sangre
   - Datos del dueño (nombre, teléfono, email)
   - Historial médico completo
   - Últimas citas
3. Sistema registra el escaneo (auditoría)

---

## 3️⃣ **ROL: RECEPCIONISTA** 📋

### **Descripción:**
Personal administrativo que gestiona citas, registra clientes/mascotas y maneja facturas.

### **Vistas (Pantallas en Flutter):**

#### 📱 **Dashboard Recepción**
- Citas del día (calendario)
- Clientes en espera
- Notificaciones de nuevas citas
- Acceso rápido a registro

#### 📅 **Gestión de Citas**
- **Calendario general:** Ver todas las citas de la clínica
- **Agendar cita para cliente:**
  - Buscar/registrar cliente
  - Buscar/registrar mascota
  - Seleccionar veterinario
  - Ver disponibilidad
  - Agendar cita
- **Confirmar citas**
- **Cancelar/reprogramar citas**
- **Marcar llegada de cliente**

#### 👥 **Gestión de Clientes**
- **Lista de clientes:** Ver todos los clientes
- **Buscar cliente:** Por nombre, teléfono, email
- **Registrar nuevo cliente**
- **Editar datos de cliente**
- **Ver mascotas de cliente**

#### 🐾 **Gestión de Mascotas**
- **Lista de mascotas:** Ver todas las mascotas
- **Buscar mascota:** Por nombre, chip, QR
- **Registrar nueva mascota**
- **Editar datos de mascota**
- **Generar QR de mascota**
- **Imprimir QR** (para collar/placa)

#### 💰 **Gestión de Facturas**
- **Crear factura:**
  - Seleccionar cliente y cita
  - Agregar servicios/productos
  - Calcular total
  - Generar número de factura
- **Ver facturas pendientes**
- **Marcar factura como pagada**
- **Imprimir/enviar factura**
- **Estadísticas de facturación**

#### 🩺 **Servicios**
- Ver lista de servicios disponibles
- Ver precios de servicios
- Buscar servicios

#### 👤 **Mi Perfil**
- Ver/editar información personal
- Cerrar sesión

---

### **Funcionalidades Detalladas:**

#### ✅ **Gestión de Clientes**
| Funcionalidad | Endpoint | Método |
|--------------|----------|---------|
| Ver lista de clientes | `/api/clientes` | GET |
| Buscar cliente | `/api/clientes?search={query}` | GET |
| Ver detalle de cliente | `/api/clientes/{id}` | GET |
| Registrar nuevo cliente | `/api/clientes` | POST |
| Editar cliente | `/api/clientes/{id}` | PUT |
| Eliminar cliente | `/api/clientes/{id}` | DELETE |

#### ✅ **Gestión de Mascotas**
| Funcionalidad | Endpoint | Método |
|--------------|----------|---------|
| Ver lista de mascotas | `/api/mascotas` | GET |
| Registrar nueva mascota | `/api/mascotas` | POST |
| Editar mascota | `/api/mascotas/{id}` | PUT |
| Generar QR | `/api/mascotas/{id}/qr` | GET |

#### ✅ **Gestión de Citas**
| Funcionalidad | Endpoint | Método |
|--------------|----------|---------|
| Ver todas las citas | `/api/citas` | GET |
| Ver citas del día | `/api/citas?fecha={date}` | GET |
| Agendar cita | `/api/citas` | POST |
| Actualizar cita | `/api/citas/{id}` | PUT |
| Cancelar cita | `/api/citas/{id}` | DELETE |

#### ✅ **Gestión de Facturas**
| Funcionalidad | Endpoint | Método |
|--------------|----------|---------|
| Ver facturas | `/api/facturas` | GET |
| Crear factura | `/api/facturas` | POST |
| Ver detalle de factura | `/api/facturas/{id}` | GET |
| Actualizar factura | `/api/facturas/{id}` | PUT |
| Generar número de factura | `/api/generar-numero-factura` | GET |
| Ver estadísticas | `/api/facturas-estadisticas` | GET |

**Ejemplo de crear factura:**
```json
{
  "cliente_id": 1,
  "cita_id": 10,
  "total": 150.00,
  "metodo_pago": "efectivo",
  "estado": "pagado",
  "detalles": [
    {
      "concepto": "Consulta general",
      "cantidad": 1,
      "precio_unitario": 50.00,
      "subtotal": 50.00
    },
    {
      "concepto": "Vacuna antirrábica",
      "cantidad": 1,
      "precio_unitario": 100.00,
      "subtotal": 100.00
    }
  ]
}
```

---

## 🔐 **Tabla Resumen de Permisos**

> **Nota:** Solo incluye los 3 roles implementados actualmente

| Funcionalidad | Cliente | Veterinario | Recepcionista |
|--------------|---------|-------------|---------------|
| **Mascotas** |
| Ver sus mascotas | ✅ | ✅ (todas) | ✅ (todas) |
| Registrar mascota | ✅ | ❌ | ✅ |
| Editar mascota | ✅ (solo suyas) | ❌ | ✅ |
| Eliminar mascota | ✅ (solo suyas) | ❌ | ✅ |
| Ver QR de mascota | ✅ | ✅ | ✅ |
| Escanear QR | ✅ | ✅ | ✅ |
| **Citas** |
| Ver sus citas | ✅ | ✅ (asignadas) | ✅ (todas) |
| Agendar cita | ✅ | ❌ | ✅ |
| Cancelar cita | ✅ (solo suyas) | ✅ (asignadas) | ✅ |
| Reprogramar cita | ✅ (solo suyas) | ✅ (asignadas) | ✅ |
| Cambiar estado cita | ❌ | ✅ | ✅ |
| Ver disponibilidad | ✅ | ✅ | ✅ |
| **Historial Médico** |
| Ver historial | ✅ (solo suyas) | ✅ (todas) | ✅ (todas) |
| Registrar consulta | ❌ | ✅ | ❌ |
| Editar consulta | ❌ | ✅ (solo suyas) | ❌ |
| Adjuntar archivos | ❌ | ✅ | ❌ |
| **Facturas** |
| Ver sus facturas | ✅ | ❌ | ✅ (todas) |
| Crear factura | ❌ | ❌ | ✅ |
| Editar factura | ❌ | ❌ | ✅ |
| **Facturación desde Historiales** ⭐ |
| Ver historiales sin facturar | ❌ | ❌ | ✅ |
| Seleccionar múltiples historiales | ❌ | ❌ | ✅ |
| Generar factura desde historiales | ❌ | ❌ | ✅ |
| **Clientes** |
| Ver clientes | ❌ | ❌ | ✅ |
| Registrar cliente (walk-in) | ❌ | ❌ | ✅ |
| Editar cliente | ❌ | ❌ | ✅ |
| **Veterinarios** |
| Ver veterinarios | ✅ | ✅ | ✅ |
| Registrar veterinario | ❌ | ❌ | ❌ |
| Editar veterinario | ❌ | ❌ | ❌ |
| Configurar disponibilidad | ❌ | ✅ (solo propia) | ❌ |
| **Servicios** |
| Ver servicios | ✅ | ✅ | ✅ |
| Crear servicio | ❌ | ❌ | ✅ |
| Editar servicio | ❌ | ❌ | ✅ |
| **Sistema** |
| Ver estadísticas | ❌ | ✅ (propias) | ✅ (básicas) |
| Enviar notificaciones | ❌ | ❌ | ✅ |

---

## 🎯 **Flujo Completo: Agendar Cita con Validación de Disponibilidad**

### **Paso a Paso:**

#### **1. Cliente selecciona mascota**
```dart
// Endpoint: GET /api/mascotas?cliente_id={id}
// Response: Lista de mascotas del cliente
```

#### **2. Cliente selecciona servicio**
```dart
// Endpoint: GET /api/servicios
// Response: Lista de servicios disponibles
```

#### **3. Sistema muestra veterinarios disponibles**
```dart
// Endpoint: GET /api/veterinarios
// Response: Lista de veterinarios activos
```

#### **4. Cliente selecciona veterinario y fecha**
```dart
// Endpoint: GET /api/veterinarios/{id}/disponibilidad?fecha=2025-11-10
// Response:
{
  "fecha": "2025-11-10",
  "dia_semana": 0,
  "nombre_dia": "Domingo",
  "horarios": [
    {
      "hora_inicio": "08:00",
      "hora_fin": "12:00",
      "intervalo_minutos": 30,
      "slots_disponibles": [
        { "hora": "08:00", "disponible": true },
        { "hora": "08:30", "disponible": true },
        { "hora": "09:00", "disponible": false },
        { "hora": "09:30", "disponible": true },
        ...
      ]
    }
  ]
}
```

#### **5. Cliente selecciona hora disponible**
- Sistema muestra solo slots con `disponible: true`
- Cliente hace click en hora deseada

#### **6. Sistema valida y agenda cita**
```dart
// Endpoint: POST /api/citas
// Request:
{
  "cliente_id": 1,
  "mascota_id": 2,
  "veterinario_id": 3,
  "fecha": "2025-11-10",
  "hora": "08:30",
  "motivo": "Consulta de rutina",
  "servicio_ids": [1]
}

// Response (éxito):
{
  "success": true,
  "message": "Cita agendada exitosamente",
  "cita": {
    "id": 100,
    "cliente_id": 1,
    "mascota_id": 2,
    "veterinario_id": 3,
    "fecha": "2025-11-10",
    "hora": "08:30",
    "estado": "pendiente",
    ...
  }
}

// Response (error - horario no disponible):
{
  "success": false,
  "message": "El horario seleccionado ya no está disponible"
}
```

#### **7. Sistema envía notificaciones**
- ✅ Notificación push al cliente (confirmación)
- ✅ Notificación push al veterinario (nueva cita asignada)
- ✅ Email de confirmación al cliente

---

## 📱 **Recomendaciones de Implementación en Flutter**

### **Navegación por Rol:**

```dart
// Después del login, redirigir según rol (solo 3 roles implementados):
switch (user.tipoUsuario) {
  case 'cliente':
    Navigator.pushReplacementNamed(context, '/cliente/dashboard');
    break;
  case 'veterinario':
    Navigator.pushReplacementNamed(context, '/veterinario/dashboard');
    break;
  case 'recepcion':
    Navigator.pushReplacementNamed(context, '/recepcion/dashboard');
    break;
  default:
    // Admin no implementado aún
    Navigator.pushReplacementNamed(context, '/login');
}
```

### **Drawer/Menu Lateral por Rol:**

#### **Cliente:**
```
📱 Dashboard
🐾 Mis Mascotas
📅 Mis Citas
💰 Mis Facturas
🔔 Notificaciones
👤 Mi Perfil
🚪 Cerrar Sesión
```

#### **Veterinario:**
```
📱 Dashboard
📅 Mis Citas
🐾 Pacientes
📋 Historial Médico
⏰ Mi Disponibilidad
👤 Mi Perfil
🚪 Cerrar Sesión
```

#### **Recepcionista:**
```
📱 Dashboard
� Walk-In (Registro Rápido)
�📅 Citas
👥 Clientes
🐾 Mascotas
💰 Facturas (desde Historiales) ⭐
🩺 Servicios
� Buscar QR
� Mi Perfil
🚪 Cerrar Sesión
```

---

## ✅ **Checklist de Implementación**

### **Backend (Laravel):** ✅ 100% COMPLETO

- [x] Sistema de roles con Spatie Permission (3 roles)
- [x] Endpoints de mascotas con QR
- [x] Endpoints de citas con validación
- [x] Endpoints de disponibilidad de veterinarios
- [x] Endpoints de historial médico con servicios
- [x] Endpoints de facturas
- [x] **Endpoint facturación desde historiales** ⭐
- [x] **Sistema Walk-In (clientes sin cuenta)** ⭐
- [x] Sistema de notificaciones FCM
- [x] Auditoría completa (AuditLog)
- [x] QR System con lookup
- [x] 70 API endpoints funcionales
- [x] 25 migraciones ejecutadas

### **Frontend (Flutter):** ⏳ PENDIENTE

#### **Prioridad Alta:**
- [ ] Sistema de login con roles (3 roles)
- [ ] Dashboard por rol (3 dashboards)
- [ ] **Pantallas recepcionista:**
  - [ ] Walk-In registration (wizard 3 pasos)
  - [ ] **Facturación desde historiales** (tabla + checkboxes) ⭐
  - [ ] QR scanner integration
- [ ] Pantallas de gestión de mascotas
- [ ] Pantallas de gestión de citas
- [ ] Validación de disponibilidad al agendar

#### **Prioridad Media:**
- [ ] Pantallas de historial médico
- [ ] Pantallas de facturas
- [ ] Sistema de notificaciones push (FCM)
- [ ] State management (Provider/Riverpod)

#### **Prioridad Baja:**
- [ ] Pantallas veterinario completas
- [ ] Pantallas cliente completas
- [ ] Optimizaciones UI/UX
- [ ] Testing unitario

---

## 📝 Notas Finales

**✅ Backend Status:** 100% completo para 3 roles  
**⏳ Frontend Status:** Por implementar  
**🔮 Futuro:** Rol Admin planeado para versión 2.0

**Sistemas Críticos Implementados:**
- ✅ Walk-In System (clientes sin cuenta)
- ✅ QR System (identificación única)
- ✅ Servicios múltiples en historiales
- ✅ Facturación desde múltiples historiales
- ✅ Sistema de notificaciones (FCM + DB)
- ✅ Auditoría completa

---

**Fecha de actualización:** Enero 2025  
**Backend Version:** Laravel 12.37.0  
**Roles Implementados:** 3 (Cliente, Veterinario, Recepcionista)  
**API Endpoints:** 70 rutas funcionales  
**Sistema:** VetCare - Gestión de Clínica Veterinaria
