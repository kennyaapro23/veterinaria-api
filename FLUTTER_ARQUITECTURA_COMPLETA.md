# 📐 Arquitectura Flutter - VetCare App

## 🎯 **Documento Técnico sin Código**

Este documento describe la arquitectura completa, modelos, relaciones y funcionalidades que deben implementarse en Flutter.

---

## 📊 **1. MODELOS DE DATOS**

### **Tabla 1: Cliente**

| Campo | Tipo | Nullable | Descripción |
|-------|------|----------|-------------|
| `id` | int | ❌ | ID único del cliente |
| `userId` | int | ✅ | ID del usuario (null si es walk-in) |
| `nombre` | String | ❌ | Nombre completo |
| `email` | String | ✅ | Email (null si es walk-in) |
| `telefono` | String | ❌ | Teléfono (siempre requerido) |
| `direccion` | String | ✅ | Dirección física |
| `fechaNacimiento` | DateTime | ✅ | Fecha de nacimiento |
| `esWalkIn` | bool | ❌ | true = sin cuenta, false = registrado |
| `notas` | String | ✅ | Notas adicionales |
| `createdAt` | DateTime | ❌ | Fecha de creación |
| `updatedAt` | DateTime | ❌ | Última actualización |

**Relaciones:**
- ➡️ **1:N** con `Mascota` (un cliente tiene muchas mascotas)
- ➡️ **1:N** con `Factura` (un cliente tiene muchas facturas)
- ➡️ **1:1** con `User` (un cliente puede tener un usuario)

**Métodos helper:**
- `tieneUsuario` → `bool`: Retorna si tiene userId
- `puedeUsarApp` → `bool`: Retorna si puede usar la app (tiene usuario y no es walk-in)
- `tipoBadge` → `String`: Retorna "Walk-In" o "Registrado"
- `tipoBadgeColor` → `Color`: Retorna naranja o verde

---

### **Tabla 2: Mascota**

| Campo | Tipo | Nullable | Descripción |
|-------|------|----------|-------------|
| `id` | int | ❌ | ID único de la mascota |
| `clienteId` | int | ❌ | ID del dueño (cliente) |
| `nombre` | String | ❌ | Nombre de la mascota |
| `especie` | String | ❌ | Perro, Gato, Ave, Reptil, etc. |
| `raza` | String | ✅ | Raza específica |
| `sexo` | String | ❌ | "macho" o "hembra" |
| `fechaNacimiento` | DateTime | ✅ | Fecha de nacimiento |
| `color` | String | ✅ | Color del pelaje/plumas |
| `peso` | double | ✅ | Peso en kilogramos |
| `microchip` | String | ✅ | Número de microchip |
| `qrCode` | String | ❌ | Código QR único (VETCARE_PET_uuid) |
| `qrUrl` | String | ❌ | URL del QR |
| `alergias` | String | ✅ | Alergias conocidas |
| `condicionesMedicas` | String | ✅ | Condiciones médicas crónicas |
| `notas` | String | ✅ | Notas adicionales |
| `foto` | String | ✅ | URL de la foto |
| `activa` | bool | ❌ | true = activa, false = fallecida |
| `createdAt` | DateTime | ❌ | Fecha de creación |
| `updatedAt` | DateTime | ❌ | Última actualización |

**Relaciones:**
- ⬅️ **N:1** con `Cliente` (muchas mascotas de un cliente)
- ➡️ **1:N** con `Cita` (una mascota tiene muchas citas)
- ➡️ **1:N** con `HistorialMedico` (una mascota tiene muchos registros médicos)

**Métodos helper:**
- `edad` → `int`: Calcula edad en años desde fechaNacimiento
- `sexoIcono` → `IconData`: Retorna icono según sexo
- `especieIcono` → `IconData`: Retorna icono según especie
- `tieneAlergias` → `bool`: Retorna si tiene alergias
- `necesitaAtencionMedica` → `bool`: Retorna si tiene condiciones médicas

---

### **Tabla 3: Veterinario**

| Campo | Tipo | Nullable | Descripción |
|-------|------|----------|-------------|
| `id` | int | ❌ | ID único del veterinario |
| `userId` | int | ❌ | ID del usuario asociado |
| `nombre` | String | ❌ | Nombre completo |
| `matricula` | String | ❌ | Matrícula profesional |
| `especialidad` | String | ✅ | Especialidad médica |
| `telefono` | String | ❌ | Teléfono de contacto |
| `email` | String | ❌ | Email profesional |
| `foto` | String | ✅ | URL de la foto |
| `activo` | bool | ❌ | true = activo, false = inactivo |
| `createdAt` | DateTime | ❌ | Fecha de creación |
| `updatedAt` | DateTime | ❌ | Última actualización |

**Relaciones:**
- ➡️ **1:N** con `Cita` (un veterinario tiene muchas citas)
- ➡️ **1:N** con `HistorialMedico` (un veterinario crea muchos historiales)
- ➡️ **1:N** con `AgendaDisponibilidad` (un veterinario tiene muchos horarios)

**Métodos helper:**
- `nombreCorto` → `String`: Retorna "Dr. Apellido"
- `tieneEspecialidad` → `bool`: Retorna si tiene especialidad
- `estaActivo` → `bool`: Alias de activo

---

### **Tabla 4: Cita**

| Campo | Tipo | Nullable | Descripción |
|-------|------|----------|-------------|
| `id` | int | ❌ | ID único de la cita |
| `clienteId` | int | ❌ | ID del cliente |
| `mascotaId` | int | ❌ | ID de la mascota |
| `veterinarioId` | int | ❌ | ID del veterinario |
| `fecha` | DateTime | ❌ | Fecha y hora de la cita |
| `duracionMinutos` | int | ❌ | Duración en minutos |
| `estado` | String | ❌ | "pendiente", "confirmada", "atendida", "cancelada", "no_asistio" |
| `motivoConsulta` | String | ✅ | Motivo de la consulta |
| `notas` | String | ✅ | Notas adicionales |
| `createdAt` | DateTime | ❌ | Fecha de creación |
| `updatedAt` | DateTime | ❌ | Última actualización |

**Relaciones:**
- ⬅️ **N:1** con `Cliente` (muchas citas de un cliente)
- ⬅️ **N:1** con `Mascota` (muchas citas de una mascota)
- ⬅️ **N:1** con `Veterinario` (muchas citas de un veterinario)
- ➡️ **1:N** con `HistorialMedico` (una cita puede generar historial)
- ➡️ **N:N** con `Servicio` (una cita puede tener varios servicios)

**Métodos helper:**
- `esPendiente` → `bool`: estado == "pendiente"
- `esAtendida` → `bool`: estado == "atendida"
- `puedeSerCancelada` → `bool`: estado == "pendiente" && fecha > ahora
- `estadoColor` → `Color`: Color según estado
- `estadoIcono` → `IconData`: Icono según estado
- `fechaFormateada` → `String`: Fecha legible
- `horaFormateada` → `String`: Hora legible

---

### **Tabla 5: Servicio**

| Campo | Tipo | Nullable | Descripción |
|-------|------|----------|-------------|
| `id` | int | ❌ | ID único del servicio |
| `codigo` | String | ❌ | Código único (ej: "VAC001") |
| `nombre` | String | ❌ | Nombre del servicio |
| `descripcion` | String | ✅ | Descripción detallada |
| `tipo` | String | ❌ | "vacuna", "tratamiento", "baño", "consulta", "cirugía", "otro" |
| `duracionMinutos` | int | ❌ | Duración estimada |
| `precio` | double | ❌ | Precio base |
| `requiereVacunaInfo` | bool | ❌ | Si requiere info de vacuna |
| `createdAt` | DateTime | ❌ | Fecha de creación |
| `updatedAt` | DateTime | ❌ | Última actualización |

**Relaciones:**
- ➡️ **N:N** con `Cita` (un servicio puede estar en muchas citas)
- ➡️ **N:N** con `HistorialMedico` (un servicio puede estar en muchos historiales) ⭐

**Métodos helper:**
- `esVacuna` → `bool`: tipo == "vacuna"
- `precioFormateado` → `String`: Precio con formato de moneda
- `tipoIcono` → `IconData`: Icono según tipo
- `tipoColor` → `Color`: Color según tipo

---

### **Tabla 6: HistorialMedico**

| Campo | Tipo | Nullable | Descripción |
|-------|------|----------|-------------|
| `id` | int | ❌ | ID único del historial |
| `mascotaId` | int | ❌ | ID de la mascota |
| `citaId` | int | ✅ | ID de la cita (si viene de una cita) |
| `fecha` | DateTime | ❌ | Fecha del registro |
| `tipo` | String | ❌ | "consulta", "vacuna", "procedimiento", "control", "otro" |
| `diagnostico` | String | ✅ | Diagnóstico del veterinario |
| `tratamiento` | String | ✅ | Tratamiento prescrito |
| `observaciones` | String | ✅ | Observaciones adicionales |
| `realizadoPor` | int | ❌ | ID del veterinario |
| `archivosMeta` | Map | ✅ | Metadata de archivos adjuntos |
| `createdAt` | DateTime | ❌ | Fecha de creación |
| `updatedAt` | DateTime | ❌ | Última actualización |

**Relaciones:**
- ⬅️ **N:1** con `Mascota` (muchos historiales de una mascota)
- ⬅️ **N:1** con `Cita` (un historial puede venir de una cita)
- ⬅️ **N:1** con `Veterinario` (muchos historiales de un veterinario)
- ➡️ **1:N** con `Archivo` (un historial tiene muchos archivos)
- ➡️ **N:N** con `Servicio` (un historial puede tener varios servicios) ⭐ **NUEVO**

**Campos calculados:**
- `totalServicios` → `double`: Suma de (cantidad × precio_unitario) de todos los servicios

**Métodos helper:**
- `tipoIcono` → `IconData`: Icono según tipo
- `tipoColor` → `Color`: Color según tipo
- `tieneArchivos` → `bool`: Retorna si tiene archivos
- `tieneServicios` → `bool`: Retorna si tiene servicios aplicados ⭐
- `fechaFormateada` → `String`: Fecha legible

---

### **Tabla 7: Factura**

| Campo | Tipo | Nullable | Descripción |
|-------|------|----------|-------------|
| `id` | int | ❌ | ID único de la factura |
| `clienteId` | int | ❌ | ID del cliente |
| `numeroFactura` | String | ❌ | Número único de factura |
| `fecha` | DateTime | ❌ | Fecha de emisión |
| `subtotal` | double | ❌ | Subtotal antes de impuestos |
| `impuesto` | double | ❌ | Monto de impuestos |
| `total` | double | ❌ | Total a pagar |
| `estado` | String | ❌ | "pendiente", "pagada", "cancelada" |
| `metodoPago` | String | ✅ | "efectivo", "tarjeta", "transferencia" |
| `notas` | String | ✅ | Notas adicionales |
| `createdAt` | DateTime | ❌ | Fecha de creación |
| `updatedAt` | DateTime | ❌ | Última actualización |

**Relaciones:**
- ⬅️ **N:1** con `Cliente` (muchas facturas de un cliente)

**Métodos helper:**
- `esPendiente` → `bool`: estado == "pendiente"
- `esPagada` → `bool`: estado == "pagada"
- `estadoColor` → `Color`: Color según estado
- `totalFormateado` → `String`: Total con formato de moneda
- `fechaFormateada` → `String`: Fecha legible

---

### **Tabla 8: Notificacion**

| Campo | Tipo | Nullable | Descripción |
|-------|------|----------|-------------|
| `id` | int | ❌ | ID único de la notificación |
| `userId` | int | ❌ | ID del usuario receptor |
| `titulo` | String | ❌ | Título de la notificación |
| `mensaje` | String | ❌ | Mensaje completo |
| `tipo` | String | ❌ | "recordatorio", "resultado", "sistema", "promocion" |
| `leida` | bool | ❌ | true = leída, false = no leída |
| `metadatos` | Map | ✅ | Datos adicionales en JSON |
| `createdAt` | DateTime | ❌ | Fecha de creación |

**Relaciones:**
- ⬅️ **N:1** con `User` (muchas notificaciones de un usuario)

**Métodos helper:**
- `esLeida` → `bool`: Alias de leida
- `tipoIcono` → `IconData`: Icono según tipo
- `tipoColor` → `Color`: Color según tipo
- `fechaRelativa` → `String`: "Hace 5 min", "Hace 2 horas"

---

### **Tabla 9: AgendaDisponibilidad**

| Campo | Tipo | Nullable | Descripción |
|-------|------|----------|-------------|
| `id` | int | ❌ | ID único |
| `veterinarioId` | int | ❌ | ID del veterinario |
| `diaSemana` | String | ❌ | "lunes", "martes", etc. |
| `horaInicio` | String | ❌ | Hora de inicio (HH:mm) |
| `horaFin` | String | ❌ | Hora de fin (HH:mm) |
| `intervaloMinutos` | int | ❌ | Intervalo entre citas (15, 30, 60) |
| `activa` | bool | ❌ | Si la disponibilidad está activa |
| `createdAt` | DateTime | ❌ | Fecha de creación |

**Relaciones:**
- ⬅️ **N:1** con `Veterinario` (muchas disponibilidades de un veterinario)

---

## 🔗 **2. TABLA PIVOT (Relaciones N:N)**

### **Tabla Pivot 1: cita_servicio**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `citaId` | int | ID de la cita |
| `servicioId` | int | ID del servicio |
| `cantidad` | int | Cantidad de veces aplicado |
| `precioUnitario` | double | Precio al momento de la cita |
| `notas` | String | Notas específicas |

**Uso:**
- Vincular qué servicios se aplicaron en cada cita
- Calcular el costo total de una cita

---

### **Tabla Pivot 2: historial_servicio** ⭐ **NUEVO**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `historialMedicoId` | int | ID del historial médico |
| `servicioId` | int | ID del servicio |
| `cantidad` | int | Cantidad de veces aplicado |
| `precioUnitario` | double | Precio al momento de aplicar |
| `notas` | String | Notas específicas |

**Uso:**
- Vincular qué servicios se aplicaron en cada consulta
- Calcular el costo total de un historial médico
- **Vincular servicios a facturas** (las facturas se generan desde historiales)

---

## 📐 **3. DIAGRAMA DE RELACIONES**

```
┌─────────────┐
│    User     │
└──────┬──────┘
       │ 1:1
       ▼
┌─────────────┐          ┌─────────────┐
│   Cliente   │──────────│   Mascota   │
│             │    1:N   │             │
│ esWalkIn ✅ │◄─────────┤ qrCode 📱   │
└──────┬──────┘          └──────┬──────┘
       │                        │
       │ 1:N                    │ 1:N
       ▼                        ▼
┌─────────────┐          ┌─────────────┐
│   Factura   │          │    Cita     │
└─────────────┘          └──────┬──────┘
                                │
                                │ N:1
                                ▼
                         ┌─────────────┐
                         │ Veterinario │
                         └──────┬──────┘
                                │ 1:N
                                ▼
                         ┌─────────────┐
                         │   Agenda    │
                         │Disponibilidad│
                         └─────────────┘

┌─────────────┐          ┌─────────────┐
│   Mascota   │──────────│  Historial  │
│             │    1:N   │   Médico    │
└─────────────┘          └──────┬──────┘
                                │
                                │ N:N ⭐
                                ▼
                         ┌─────────────┐
                         │  Servicio   │
                         │             │
                         │ tipo        │
                         │ precio      │
                         └─────────────┘
                                ▲
                                │ N:N
                                │
                         ┌──────┴──────┐
                         │    Cita     │
                         └─────────────┘
```

---

## 🎯 **4. FUNCIONALIDADES POR PANTALLA**

### **4.1 Dashboard Cliente**

**Datos a mostrar:**
- Total de mascotas (count)
- Próximas citas (3 últimas where fecha >= hoy)
- Facturas pendientes (where estado == "pendiente")
- Notificaciones no leídas (count where leida == false)

**Consultas necesarias:**
```
GET /api/mascotas?cliente_id={id}
GET /api/citas?cliente_id={id}&estado=pendiente&limit=3
GET /api/facturas?cliente_id={id}&estado=pendiente
GET /api/notificaciones/unread-count
```

---

### **4.2 Lista de Mascotas (Cliente)**

**Funcionalidades:**
- Mostrar todas las mascotas del cliente
- Filtrar por especie
- Buscar por nombre
- Ver tarjeta con: foto, nombre, especie, edad, botón QR

**Consultas necesarias:**
```
GET /api/mascotas?cliente_id={id}
GET /api/mascotas?cliente_id={id}&especie={especie}
GET /api/mascotas?cliente_id={id}&search={nombre}
```

---

### **4.3 Detalle Mascota (Cliente)**

**Tabs a implementar:**

**Tab 1: Información**
- Datos básicos
- Mostrar QR (usar `qr_flutter` package)
- Botón: Compartir QR
- Botón: Imprimir QR

**Tab 2: Historial Médico**
- Lista de historiales ordenados por fecha desc
- Cada item muestra:
  - Fecha
  - Tipo (con icono)
  - Veterinario
  - **Servicios aplicados** ⭐
  - **Total de servicios** ⭐
- Tap item → Ver detalle completo

**Tab 3: Citas**
- Lista de citas (pendientes y pasadas)
- Cada item muestra:
  - Fecha y hora
  - Estado (con badge)
  - Veterinario
  - Servicios (si tiene)
- Botón: Agendar nueva cita

**Consultas necesarias:**
```
GET /api/mascotas/{id}
GET /api/historial-medico?mascota_id={id}
GET /api/citas?mascota_id={id}
```

---

### **4.4 Agendar Cita (Cliente)**

**Flujo en 5 pasos:**

**Paso 1: Seleccionar Mascota**
- Mostrar lista de mascotas del cliente
- Solo mascotas activas

**Paso 2: Seleccionar Servicio(s)**
- Mostrar lista de servicios
- Filtrar por tipo
- Permitir seleccionar múltiples servicios ⭐
- Calcular duración total
- Mostrar precio estimado ⭐

**Paso 3: Seleccionar Veterinario**
- Mostrar lista de veterinarios activos
- Mostrar foto, nombre, especialidad
- Filtrar por especialidad

**Paso 4: Seleccionar Fecha**
- Calendario interactivo
- Deshabilitar días sin disponibilidad
- Consultar disponibilidad del veterinario

**Paso 5: Seleccionar Hora**
- Mostrar slots disponibles según:
  - Disponibilidad del veterinario (AgendaDisponibilidad)
  - Citas ya agendadas (ocupadas)
  - Duración de servicios seleccionados
- Deshabilitar horas ocupadas

**Paso 6: Confirmar**
- Resumen de la cita
- Botón: Confirmar

**Consultas necesarias:**
```
GET /api/mascotas?cliente_id={id}&activa=true
GET /api/servicios
GET /api/veterinarios?activo=true
GET /api/veterinarios/{id}/disponibilidad?fecha={fecha}
POST /api/citas (con array de servicios)
```

---

### **4.5 Dashboard Veterinario**

**Datos a mostrar:**
- Citas del día (count y lista)
- Próxima cita (más cercana en el tiempo)
- Pacientes atendidos hoy (count)
- Notificaciones

**Consultas necesarias:**
```
GET /api/citas?veterinario_id={id}&fecha={hoy}
GET /api/notificaciones/unread-count
```

---

### **4.6 Mi Agenda (Veterinario)**

**Funcionalidades:**
- Vista de calendario (día/semana/mes)
- Lista de citas con:
  - Hora
  - Cliente
  - Mascota (con foto)
  - Servicio(s)
  - Estado
- Tap cita → Ver detalle y opciones:
  - Ver historial de la mascota
  - Iniciar consulta (crear historial)
  - Marcar como atendida
  - Marcar como no asistió
  - Cancelar

**Consultas necesarias:**
```
GET /api/citas?veterinario_id={id}&fecha={fecha}
GET /api/citas/{id}
GET /api/historial-medico?mascota_id={id}
```

---

### **4.7 Registrar Consulta (Veterinario)**

**Formulario en secciones:**

**Sección 1: Información Básica**
- Mascota (pre-seleccionada desde cita)
- Fecha (pre-llenada)
- Tipo de consulta (dropdown)

**Sección 2: Diagnóstico**
- Campo de texto largo
- Plantillas predefinidas (opcional)

**Sección 3: Tratamiento**
- Campo de texto largo
- Plantillas predefinidas (opcional)

**Sección 4: Servicios Aplicados** ⭐ **NUEVO**
- Lista de servicios disponibles
- Permitir agregar múltiples servicios
- Por cada servicio:
  - Servicio (dropdown)
  - Cantidad (número)
  - Precio unitario (pre-llenado, editable)
  - Notas (texto corto)
- Botón: + Agregar otro servicio
- Mostrar total calculado

**Sección 5: Observaciones**
- Campo de texto largo
- Opcional

**Sección 6: Archivos**
- Botón: Adjuntar archivos
- Mostrar preview de archivos
- Permitir múltiples archivos

**Botón: Guardar Consulta**

**Estructura del POST:**
```json
{
  "mascota_id": 1,
  "cita_id": 5,
  "fecha": "2025-11-08 10:30:00",
  "tipo": "consulta",
  "diagnostico": "...",
  "tratamiento": "...",
  "observaciones": "...",
  "servicios": [
    {
      "servicio_id": 3,
      "cantidad": 1,
      "precio_unitario": 50.00,
      "notas": "Vacuna aplicada en pata trasera"
    },
    {
      "servicio_id": 7,
      "cantidad": 2,
      "precio_unitario": 25.00,
      "notas": "Tratamiento de 2 dosis"
    }
  ],
  "archivos": [...]
}
```

**Consultas necesarias:**
```
GET /api/servicios (para dropdown)
POST /api/historial-medico (con servicios)
```

---

### **4.8 Dashboard Recepcionista**

**Datos a mostrar:**
- Citas del día (calendario)
- Clientes walk-in atendidos hoy (count)
- Facturas generadas hoy (count)
- Próximas citas (3 horas)

**Botón destacado:**
- 🟧 REGISTRO RÁPIDO WALK-IN

**Consultas necesarias:**
```
GET /api/citas?fecha={hoy}
GET /api/clientes?es_walk_in=true&created_at={hoy}
GET /api/facturas?created_at={hoy}
```

---

### **4.9 Registro Rápido Walk-In (Recepcionista)**

**Formulario en 2 secciones:**

**Sección 1: Datos del Cliente**
- Nombre * (requerido)
- Teléfono * (requerido)
- Email (opcional)
- Dirección (opcional)
- Notas (opcional)

**Sección 2: Datos de la Mascota**
- Nombre * (requerido)
- Especie * (requerido)
- Sexo * (requerido: macho/hembra)
- Raza (opcional)
- Color (opcional)
- Peso (opcional)

**Botón: REGISTRAR CLIENTE Y MASCOTA**

**Al éxito:**
- Mostrar Dialog con:
  - ✅ Datos del cliente
  - ✅ Datos de la mascota
  - 📱 QR Code de la mascota
  - Botón: Cerrar
  - Botón: Imprimir QR

**Consultas necesarias:**
```
POST /api/clientes/registro-rapido
```

---

### **4.10 Gestión de Clientes (Recepcionista)**

**Funcionalidades:**
- Ver todos los clientes
- Filtros:
  - Todos
  - Solo Walk-In
  - Solo Registrados
- Buscar por nombre/teléfono/email
- Cada item muestra:
  - Nombre
  - Badge: "Walk-In" (naranja) o "Registrado" (verde)
  - Teléfono
  - Email (si existe)
- Botones:
  - Ver detalle
  - Editar
  - Ver mascotas
  - Ver citas
  - Ver facturas

**FAB (Floating Action Button):**
- Icon: `person_add`
- Label: "Walk-In"
- Color: naranja
- Acción: Navegar a registro rápido

**Consultas necesarias:**
```
GET /api/clientes
GET /api/clientes?es_walk_in=true
GET /api/clientes?es_walk_in=false
GET /api/clientes?search={texto}
```

---

### **4.11 Gestión de Servicios (Admin)**

**Funcionalidades:**
- Listar todos los servicios
- Filtrar por tipo
- Buscar por nombre/código
- Cada item muestra:
  - Código
  - Nombre
  - Tipo (con badge)
  - Precio
  - Duración
- Botones:
  - Crear nuevo servicio
  - Editar servicio
  - Eliminar servicio

**Formulario de servicio:**
- Código * (único)
- Nombre *
- Descripción
- Tipo * (dropdown)
- Duración en minutos *
- Precio *
- Requiere info de vacuna (checkbox)

**Consultas necesarias:**
```
GET /api/servicios
GET /api/servicios?tipo={tipo}
POST /api/servicios
PUT /api/servicios/{id}
DELETE /api/servicios/{id}
```

---

## 📦 **5. ESTRUCTURA DE CARPETAS FLUTTER**

```
lib/
├── config/
│   ├── app_config.dart          # Configuración general (API_URL, etc.)
│   ├── routes.dart               # Definición de rutas
│   └── theme.dart                # Tema y colores
│
├── models/
│   ├── cliente.dart
│   ├── mascota.dart
│   ├── veterinario.dart
│   ├── cita.dart
│   ├── servicio.dart             ⭐
│   ├── historial_medico.dart
│   ├── factura.dart
│   ├── notificacion.dart
│   ├── agenda_disponibilidad.dart
│   └── pivot/
│       ├── cita_servicio.dart
│       └── historial_servicio.dart  ⭐ NUEVO
│
├── services/
│   ├── api_service.dart          # Cliente HTTP base
│   ├── auth_service.dart
│   ├── cliente_service.dart
│   ├── mascota_service.dart
│   ├── cita_service.dart
│   ├── servicio_service.dart     ⭐
│   ├── historial_service.dart
│   ├── factura_service.dart
│   ├── notificacion_service.dart
│   └── qr_service.dart
│
├── providers/
│   ├── auth_provider.dart
│   ├── cliente_provider.dart
│   ├── mascota_provider.dart
│   ├── cita_provider.dart
│   └── servicio_provider.dart    ⭐
│
├── screens/
│   ├── auth/
│   │   ├── login_screen.dart
│   │   └── register_screen.dart
│   │
│   ├── cliente/
│   │   ├── dashboard_screen.dart
│   │   ├── mascotas/
│   │   │   ├── mascotas_screen.dart
│   │   │   ├── mascota_detail_screen.dart
│   │   │   └── registrar_mascota_screen.dart
│   │   ├── citas/
│   │   │   ├── mis_citas_screen.dart
│   │   │   └── agendar_cita_screen.dart
│   │   ├── facturas/
│   │   │   └── mis_facturas_screen.dart
│   │   └── perfil/
│   │       └── perfil_screen.dart
│   │
│   ├── veterinario/
│   │   ├── dashboard_screen.dart
│   │   ├── agenda/
│   │   │   └── mi_agenda_screen.dart
│   │   ├── pacientes/
│   │   │   ├── pacientes_screen.dart
│   │   │   └── paciente_detail_screen.dart
│   │   ├── consulta/
│   │   │   └── registrar_consulta_screen.dart  ⭐ Con servicios
│   │   └── disponibilidad/
│   │       └── configurar_disponibilidad_screen.dart
│   │
│   ├── recepcionista/
│   │   ├── dashboard_screen.dart
│   │   ├── clientes/
│   │   │   ├── clientes_screen.dart
│   │   │   └── registro_rapido_screen.dart  ⭐ Walk-in
│   │   ├── mascotas/
│   │   │   └── mascotas_screen.dart
│   │   ├── citas/
│   │   │   ├── calendario_citas_screen.dart
│   │   │   └── gestionar_cita_screen.dart
│   │   └── facturas/
│   │       ├── facturas_screen.dart
│   │       └── crear_factura_screen.dart  ⭐ Desde historial
│   │
│   └── admin/
│       ├── dashboard_screen.dart
│       ├── usuarios/
│       ├── veterinarios/
│       └── servicios/
│           ├── servicios_screen.dart  ⭐
│           └── crear_servicio_screen.dart  ⭐
│
├── widgets/
│   ├── common/
│   │   ├── custom_button.dart
│   │   ├── custom_textfield.dart
│   │   ├── loading_widget.dart
│   │   └── error_widget.dart
│   ├── mascota/
│   │   ├── mascota_card.dart
│   │   └── qr_display.dart
│   ├── cita/
│   │   ├── cita_card.dart
│   │   └── estado_badge.dart
│   ├── servicio/
│   │   ├── servicio_card.dart  ⭐
│   │   ├── servicio_selector.dart  ⭐
│   │   └── servicio_list_item.dart  ⭐
│   └── historial/
│       ├── historial_card.dart
│       └── servicios_aplicados_list.dart  ⭐ NUEVO
│
└── utils/
    ├── constants.dart
    ├── validators.dart
    ├── date_formatter.dart
    ├── currency_formatter.dart
    └── qr_generator.dart
```

---

## 🔄 **6. FLUJOS DE DATOS IMPORTANTES**

### **Flujo 1: Crear Historial con Servicios** ⭐

```
[Veterinario] → Pantalla: Registrar Consulta
    ↓
1. Seleccionar mascota (desde cita)
2. Llenar diagnóstico
3. Llenar tratamiento
4. AGREGAR SERVICIOS:
   - Tap "Agregar Servicio"
   - Seleccionar servicio (dropdown)
   - Cantidad (número)
   - Precio se auto-llena del servicio
   - Puede editar precio
   - Agregar notas (opcional)
   - Puede agregar más servicios
5. Ver total calculado
6. Agregar archivos (opcional)
7. Tap "Guardar Consulta"
    ↓
POST /api/historial-medico
Body: {
  mascota_id, fecha, tipo,
  diagnostico, tratamiento,
  servicios: [
    {servicio_id, cantidad, precio_unitario, notas},
    {servicio_id, cantidad, precio_unitario, notas}
  ],
  archivos: [...]
}
    ↓
Backend:
- Crea historial
- Crea registros en historial_servicio (pivot)
- Sube archivos
- Calcula total_servicios
    ↓
Response: {
  historial: {...},
  servicios: [...],
  total_servicios: 150.00
}
    ↓
[Veterinario] → Ver confirmación
[Veterinario] → Regresar a agenda
```

---

### **Flujo 2: Ver Historial con Servicios** ⭐

```
[Cliente] → Detalle Mascota → Tab: Historial
    ↓
GET /api/historial-medico?mascota_id={id}
    ↓
Response: [
  {
    id: 1,
    fecha: "2025-11-08",
    tipo: "consulta",
    diagnostico: "...",
    servicios: [
      {
        id: 3,
        nombre: "Vacuna Rabia",
        pivot: {
          cantidad: 1,
          precio_unitario: 50.00,
          notas: "Aplicada en pata"
        }
      },
      {
        id: 7,
        nombre: "Antiparasitario",
        pivot: {
          cantidad: 2,
          precio_unitario: 25.00,
          notas: null
        }
      }
    ],
    total_servicios: 100.00
  }
]
    ↓
[Cliente] → Ve lista de historiales con:
- Fecha
- Tipo
- Veterinario
- **Servicios aplicados: 2**
- **Total: $100.00**
    ↓
[Cliente] → Tap en historial
    ↓
[Cliente] → Ve detalle completo:
- Diagnóstico
- Tratamiento
- **Lista de servicios con desglose**
- Archivos adjuntos
```

---

### **Flujo 3: Crear Factura desde Historial** ⭐

```
[Recepcionista] → Gestión de Facturas
    ↓
Tap "Nueva Factura"
    ↓
1. Seleccionar cliente
2. Buscar historiales médicos del cliente
3. Filtrar por: "Sin facturar"
4. Mostrar lista de historiales con:
   - Fecha
   - Mascota
   - Servicios aplicados
   - Total
5. Seleccionar uno o varios historiales
6. Sistema calcula:
   - Subtotal (suma de total_servicios)
   - Impuesto (% configurable)
   - Total
7. Seleccionar método de pago
8. Agregar notas (opcional)
9. Tap "Generar Factura"
    ↓
POST /api/facturas
Body: {
  cliente_id: 1,
  historial_ids: [5, 7],
  subtotal: 250.00,
  impuesto: 37.50,
  total: 287.50,
  metodo_pago: "efectivo"
}
    ↓
Backend:
- Genera número de factura
- Crea factura
- Marca historiales como facturados
    ↓
[Recepcionista] → Ver factura generada
[Recepcionista] → Imprimir (opcional)
```

---

## 📊 **7. TABLAS DE VALIDACIONES**

### **Validaciones: Registro Rápido Walk-In**

| Campo | Requerido | Tipo | Validación |
|-------|-----------|------|------------|
| nombre_cliente | ✅ | String | Min 3, Max 150 |
| telefono_cliente | ✅ | String | Formato válido, Min 9 |
| email_cliente | ❌ | String | Email válido si se proporciona |
| direccion_cliente | ❌ | String | Max 300 |
| notas_cliente | ❌ | String | Max 1000 |
| nombre_mascota | ✅ | String | Min 2, Max 100 |
| especie_mascota | ✅ | String | Enum válido |
| sexo_mascota | ✅ | String | "macho" o "hembra" |
| raza_mascota | ❌ | String | Max 100 |
| color_mascota | ❌ | String | Max 50 |
| peso_mascota | ❌ | double | Min 0.1, Max 500 |

---

### **Validaciones: Crear Historial con Servicios**

| Campo | Requerido | Tipo | Validación |
|-------|-----------|------|------------|
| mascota_id | ✅ | int | Existe en DB |
| cita_id | ❌ | int | Existe en DB |
| fecha | ✅ | DateTime | No futuro |
| tipo | ✅ | String | Enum válido |
| diagnostico | ❌ | String | Max 5000 |
| tratamiento | ❌ | String | Max 5000 |
| observaciones | ❌ | String | Max 5000 |
| **servicios** | ❌ | Array | Min 0 items |
| **servicios[].servicio_id** | ✅ | int | Existe en DB |
| **servicios[].cantidad** | ❌ | int | Min 1, Default 1 |
| **servicios[].precio_unitario** | ❌ | double | Min 0, Default del servicio |
| **servicios[].notas** | ❌ | String | Max 500 |
| archivos | ❌ | Files | Max 10MB c/u |

---

### **Validaciones: Crear Servicio**

| Campo | Requerido | Tipo | Validación |
|-------|-----------|------|------------|
| codigo | ✅ | String | Único, Max 50 |
| nombre | ✅ | String | Max 150 |
| descripcion | ❌ | String | Max 1000 |
| tipo | ✅ | String | Enum válido |
| duracion_minutos | ✅ | int | Min 5, Max 480 |
| precio | ✅ | double | Min 0, Max 99999999.99 |
| requiere_vacuna_info | ❌ | bool | Default false |

---

## 🎨 **8. ESPECIFICACIONES DE UI/UX**

### **Colores por Tipo de Servicio**

| Tipo | Color | Icono |
|------|-------|-------|
| vacuna | 💉 Púrpura | `Icons.vaccines` |
| tratamiento | 💊 Azul | `Icons.medication` |
| baño | 🛁 Cyan | `Icons.shower` |
| consulta | 🩺 Verde | `Icons.medical_services` |
| cirugía | ⚕️ Rojo | `Icons.local_hospital` |
| otro | ⚙️ Gris | `Icons.miscellaneous_services` |

---

### **Colores por Estado de Cita**

| Estado | Color | Icono |
|--------|-------|-------|
| pendiente | 🟡 Amarillo | `Icons.schedule` |
| confirmada | 🟢 Verde | `Icons.check_circle` |
| atendida | 🔵 Azul | `Icons.done_all` |
| cancelada | 🔴 Rojo | `Icons.cancel` |
| no_asistio | ⚫ Gris | `Icons.person_off` |

---

### **Badges Cliente**

| Tipo | Color | Texto |
|------|-------|-------|
| Walk-In | 🟧 Naranja | "Walk-In" |
| Registrado | 🟩 Verde | "Registrado" |

---

## 📚 **9. DEPENDENCIAS REQUERIDAS**

```yaml
dependencies:
  # HTTP
  http: ^1.1.0
  
  # State Management
  provider: ^6.1.1
  
  # QR
  qr_flutter: ^4.1.0
  qr_code_scanner: ^1.0.1
  
  # Imágenes
  image_picker: ^1.0.4
  cached_network_image: ^3.3.0
  
  # PDF
  pdf: ^3.10.7
  printing: ^5.12.0
  
  # Fechas
  intl: ^0.18.1
  
  # Firebase
  firebase_core: ^2.24.2
  firebase_auth: ^4.15.3
  firebase_messaging: ^14.7.9
  
  # UI
  flutter_svg: ^2.0.9
  shimmer: ^3.0.0
  
  # Utilidades
  shared_preferences: ^2.2.2
  path_provider: ^2.1.1
```

---

## ✅ **10. CHECKLIST DE IMPLEMENTACIÓN**

### **Fase 1: Modelos y Servicios** (2-3 días)

- [ ] Crear modelo `Servicio` con helpers
- [ ] Actualizar modelo `HistorialMedico` con relación a servicios
- [ ] Crear clase pivot `HistorialServicio`
- [ ] Crear `ServicioService` con CRUD
- [ ] Actualizar `HistorialService` para soportar servicios
- [ ] Crear `ServicioProvider` con state management

### **Fase 2: Pantallas Admin** (1-2 días)

- [ ] Pantalla: Lista de servicios
- [ ] Pantalla: Crear/Editar servicio
- [ ] Widget: `ServicioCard`
- [ ] Implementar filtros por tipo
- [ ] Implementar búsqueda

### **Fase 3: Registro Consulta con Servicios** (2-3 días)

- [ ] Actualizar pantalla: Registrar Consulta
- [ ] Widget: `ServicioSelector` (multi-select)
- [ ] Widget: `ServicioListItem` en consulta
- [ ] Calcular total en tiempo real
- [ ] Validaciones de servicios
- [ ] Integrar con API

### **Fase 4: Ver Historial con Servicios** (1-2 días)

- [ ] Actualizar `HistorialCard` para mostrar servicios
- [ ] Widget: `ServiciosAplicadosList`
- [ ] Mostrar total de servicios
- [ ] Ver detalle de servicios en historial
- [ ] Formateo de precios

### **Fase 5: Sistema Walk-In** (2-3 días)

- [ ] Actualizar modelo `Cliente` con `esWalkIn`
- [ ] Pantalla: Registro Rápido Walk-In
- [ ] Dialog: Mostrar QR después de registro
- [ ] Actualizar lista de clientes con filtros
- [ ] Badges por tipo de cliente
- [ ] FAB para walk-in

### **Fase 6: Facturas desde Historial** (3-4 días)

- [ ] Pantalla: Seleccionar historiales
- [ ] Filtro: Historiales sin facturar
- [ ] Calcular totales desde servicios
- [ ] Generar factura
- [ ] Imprimir factura (PDF)

### **Fase 7: Testing** (2-3 días)

- [ ] Pruebas unitarias de modelos
- [ ] Pruebas de servicios
- [ ] Pruebas de flujos completos
- [ ] Pruebas de UI
- [ ] Corrección de bugs

---

## 📈 **11. ESTIMACIÓN DE TIEMPO**

| Fase | Días | Desarrolladores |
|------|------|-----------------|
| Fase 1: Modelos y Servicios | 2-3 | 1 |
| Fase 2: Pantallas Admin | 1-2 | 1 |
| Fase 3: Registro Consulta | 2-3 | 1 |
| Fase 4: Ver Historial | 1-2 | 1 |
| Fase 5: Sistema Walk-In | 2-3 | 1 |
| Fase 6: Facturas | 3-4 | 1 |
| Fase 7: Testing | 2-3 | 1 |
| **TOTAL** | **13-20 días** | **1 dev** |

Con 2 desarrolladores: **7-10 días**

---

## 🎯 **12. PRIORIDADES**

### **Prioridad ALTA (hacer primero):**
1. ✅ Sistema Walk-In (negocio crítico)
2. ✅ Servicios en historial médico (core feature)
3. ✅ Ver servicios aplicados en historial

### **Prioridad MEDIA:**
4. Gestión de servicios (admin)
5. Facturas desde historial

### **Prioridad BAJA:**
6. Exportar PDF
7. Estadísticas avanzadas

---

📅 **Fecha:** 8 de noviembre de 2025
🔧 **Backend:** Laravel 12.37.0 (100% listo)
📱 **Flutter:** Arquitectura definida
👥 **Equipo:** Frontend Team

---

**Este documento es suficiente para que el equipo de Flutter implemente todo el sistema sin necesidad de ver código del backend. Todas las tablas, relaciones y flujos están documentados.**
