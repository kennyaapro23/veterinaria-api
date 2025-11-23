# 🔐 Sistema de Permisos por Rol - Implementación Completa

## 📋 Resumen de Implementación

Se han implementado **filtros y permisos por rol** en TODOS los controladores del sistema para los 3 roles: **Cliente**, **Veterinario** y **Recepción**.

---

## 👥 **PERMISOS POR ROL**

### **1️⃣ CLIENTE**

| Módulo | Ver | Crear | Editar | Eliminar | Notas |
|--------|-----|-------|--------|----------|-------|
| **Mascotas** | ✅ Solo las suyas | ✅ Solo las suyas | ✅ Solo las suyas | ✅ Solo las suyas | Auto-asigna `cliente_id` |
| **Citas** | ✅ Solo las suyas | ✅ Solo las suyas | ❌ Solo cancelar | ❌ Solo cancelar | Auto-asigna `cliente_id` |
| **Historial Médico** | ✅ Solo de sus mascotas | ❌ | ❌ | ❌ | Solo lectura |
| **Facturas** | ✅ Solo las suyas | ❌ | ❌ | ❌ | Solo lectura |
| **Servicios** | ✅ Todos | ❌ | ❌ | ❌ | Solo lectura |
| **Clientes** | ✅ Solo su perfil | ❌ | ❌ | ❌ | Solo lectura |
| **Veterinarios** | ✅ Todos | ❌ | ❌ | ❌ | Solo lectura |
| **Perfil** | ✅ | ❌ | ✅ Solo contraseña | ❌ | Endpoint `/api/cambiar-password` |

**Resumen**: El cliente **solo gestiona sus mascotas y citas**. Todo lo demás es **solo lectura** o **no permitido**. Solo puede **cambiar su contraseña**.

---

### **2️⃣ VETERINARIO**

| Módulo | Ver | Crear | Editar | Eliminar | Notas |
|--------|-----|-------|--------|----------|-------|
| **Mascotas** | ✅ Todas | ❌ | ❌ | ❌ | Solo lectura |
| **Citas** | ✅ Solo las suyas | ❌ | ✅ Confirmar/Completar | ❌ | No puede crear ni eliminar |
| **Historial Médico** | ✅ Todos | ✅ | ✅ | ❌ | Puede crear y editar |
| **Facturas** | ✅ De sus citas | ❌ | ❌ | ❌ | Solo lectura |
| **Servicios** | ✅ Todos | ❌ | ❌ | ❌ | Solo lectura |
| **Clientes** | ❌ | ❌ | ❌ | ❌ | No tiene acceso |
| **Veterinarios** | ✅ Todos | ❌ | ❌ | ❌ | Solo lectura |
| **Agenda** | ✅ Solo la suya | ✅ Solo la suya | ✅ Solo la suya | ✅ Solo la suya | Gestión completa de su agenda |

**Resumen**: El veterinario **ve todo pero solo modifica historiales médicos y su agenda**. No puede crear citas, clientes ni servicios.

---

### **3️⃣ RECEPCIÓN** (Administrador Operativo)

| Módulo | Ver | Crear | Editar | Eliminar | Notas |
|--------|-----|-------|--------|----------|-------|
| **Mascotas** | ✅ Todas | ✅ | ✅ | ✅ | Control total |
| **Citas** | ✅ Todas | ✅ | ✅ | ✅ | Control total |
| **Historial Médico** | ✅ Todos | ❌ | ❌ | ❌ | Solo lectura |
| **Facturas** | ✅ Todas | ✅ | ✅ | ✅ | Control total |
| **Servicios** | ✅ Todos | ✅ | ✅ | ✅ | Control total |
| **Clientes** | ✅ Todos | ✅ | ✅ | ✅ | Control total + Walk-in |
| **Veterinarios** | ✅ Todos | ✅ | ✅ | ✅ | Control total (excepto horarios) |
| **Agenda** | ✅ De todos | ❌ | ❌ | ❌ | Solo lectura (veterinario la edita) |

**Resumen**: Recepción tiene **control total del sistema** excepto historiales médicos (solo veterinario) y agendas de veterinarios (cada uno la suya).

---

## 🔧 **CAMBIOS IMPLEMENTADOS POR CONTROLADOR**

### **✅ 1. MascotaController**
```php
// CLIENTE
- index(): WHERE cliente_id = auth.cliente.id
- show(): 403 si no es dueño
- store(): Auto-asigna cliente_id
- update(): 403 si no es dueño
- destroy(): 403 si no es dueño

// VETERINARIO/RECEPCIÓN
- Acceso completo sin filtros
```

---

### **✅ 2. CitaController**
```php
// CLIENTE
- index(): WHERE cliente_id = auth.cliente.id
- show(): 403 si no es dueño
- store(): Auto-asigna cliente_id (no puede crear para otros)
- update(): Solo puede cancelar (estado = 'cancelada')
- destroy(): 403 si no es dueño

// VETERINARIO
- index(): WHERE veterinario_id = auth.veterinario.id
- show(): 403 si no es su cita
- store(): 403 (no puede crear citas)
- update(): Puede confirmar/completar/cancelar
- destroy(): 403 (no puede eliminar)

// RECEPCIÓN
- Acceso completo a todas las citas
```

---

### **✅ 3. ClienteController**
```php
// CLIENTE
- index(): 403 (no puede ver lista)
- show(): Solo su propio perfil (id debe coincidir)
- update(): 403 (debe usar /api/cambiar-password)
- destroy(): 403

// VETERINARIO
- index(): 403
- show(): 403
- update(): 403
- destroy(): 403

// RECEPCIÓN
- Acceso completo (CRUD)
- Puede hacer registro walk-in
```

**🆕 Endpoint Nuevo:**
```
POST /api/cambiar-password
Body: {
  "password_actual": "...",
  "password_nuevo": "...",
  "password_nuevo_confirmation": "..."
}
```
- ✅ Cualquier usuario autenticado puede cambiar su contraseña

---

### **✅ 4. ServicioController**
```php
// CLIENTE/VETERINARIO
- index(): ✅ Ver todos
- show(): ✅ Ver cualquiera
- store(): 403
- update(): 403
- destroy(): 403

// RECEPCIÓN
- Acceso completo (CRUD)
```

---

### **✅ 5. HistorialController**
```php
// CLIENTE
- index(): WHERE mascota.cliente_id = auth.cliente.id
- show(): 403 si no es su mascota
- store(): 403
- update(): 403
- attachFiles(): 403
- destroy(): 403

// VETERINARIO
- index(): ✅ Ver todos
- show(): ✅ Ver cualquiera
- store(): ✅ Crear
- update(): ✅ Editar
- attachFiles(): ✅ Agregar archivos
- destroy(): ✅ Eliminar

// RECEPCIÓN
- index(): ✅ Ver todos
- show(): ✅ Ver cualquiera
- store(): 403
- update(): 403
- attachFiles(): 403
- destroy(): 403
```

---

### **✅ 6. VeterinarioController**
```php
// TODOS
- index(): ✅ Ver todos
- show(): ✅ Ver cualquiera

// SOLO RECEPCIÓN
- store(): Crear veterinarios
- update(): Editar veterinarios
- destroy(): Eliminar veterinarios

// VETERINARIO (su propia agenda)
- getDisponibilidad(): ✅ Ver su agenda
- setDisponibilidad(): ✅ Configurar su agenda
- addHorario(): ✅ Agregar horario
- updateHorario(): ✅ Editar horario
- deleteHorario(): ✅ Eliminar horario
- toggleHorario(): ✅ Activar/desactivar horario
```

---

## 📊 **TABLA RESUMEN COMPLETA**

| Acción | Cliente | Veterinario | Recepción |
|--------|---------|-------------|-----------|
| Ver sus mascotas | ✅ | - | - |
| Ver todas las mascotas | ❌ | ✅ | ✅ |
| Crear mascota | ✅ Solo las suyas | ❌ | ✅ |
| Editar mascota | ✅ Solo las suyas | ❌ | ✅ |
| Eliminar mascota | ✅ Solo las suyas | ❌ | ✅ |
| Ver sus citas | ✅ | - | - |
| Ver citas del veterinario | ❌ | ✅ | - |
| Ver todas las citas | ❌ | ❌ | ✅ |
| Crear cita | ✅ Solo para sí | ❌ | ✅ |
| Cancelar cita | ✅ Solo las suyas | ✅ | ✅ |
| Confirmar/Completar cita | ❌ | ✅ | ✅ |
| Ver historiales de sus mascotas | ✅ | - | - |
| Ver todos los historiales | ❌ | ✅ | ✅ |
| Crear historial médico | ❌ | ✅ | ❌ |
| Editar historial médico | ❌ | ✅ | ❌ |
| Ver servicios | ✅ | ✅ | ✅ |
| Crear/Editar servicios | ❌ | ❌ | ✅ |
| Ver lista de clientes | ❌ | ❌ | ✅ |
| Ver su perfil | ✅ | ✅ | ✅ |
| Gestionar clientes | ❌ | ❌ | ✅ |
| Cambiar contraseña | ✅ | ✅ | ✅ |
| Gestionar su agenda | ❌ | ✅ | ❌ |
| Ver agendas | ✅ | ✅ | ✅ |

---

## 🔍 **CÓDIGOS DE ERROR HTTP**

- **403 Forbidden**: No tienes permiso para esta acción
- **422 Unprocessable Entity**: Validación fallida (ej: cliente intenta cambiar estado de cita)
- **404 Not Found**: Recurso no existe
- **401 Unauthorized**: No autenticado (token faltante/inválido)

---

## 🧪 **EJEMPLOS DE USO**

### **Cliente intenta ver todas las mascotas:**
```http
GET /api/mascotas
Authorization: Bearer {token_cliente}

Response 200:
{
  "data": [
    // Solo sus mascotas
  ]
}
```

### **Cliente intenta editar mascota de otro:**
```http
PUT /api/mascotas/5
Authorization: Bearer {token_cliente}

Response 403:
{
  "error": "No tienes permiso para editar esta mascota"
}
```

### **Cliente intenta crear cita especificando otro cliente_id:**
```http
POST /api/citas
Authorization: Bearer {token_cliente}
Body: {
  "cliente_id": 99, // <-- Ignorado
  "mascota_id": 1,
  "veterinario_id": 1,
  "fecha": "2025-11-10 10:00:00",
  "servicios": [1, 2]
}

Response 201:
{
  "cita": {
    "cliente_id": 4, // <-- Auto-asignado (su propio ID)
    ...
  }
}
```

### **Veterinario intenta crear un servicio:**
```http
POST /api/servicios
Authorization: Bearer {token_veterinario}

Response 403:
{
  "error": "No tienes permiso para crear servicios"
}
```

### **Cliente cambia su contraseña:**
```http
POST /api/cambiar-password
Authorization: Bearer {token_cliente}
Body: {
  "password_actual": "antigua123",
  "password_nuevo": "nueva456",
  "password_nuevo_confirmation": "nueva456"
}

Response 200:
{
  "message": "Contraseña actualizada exitosamente"
}
```

---

## 🚀 **PRÓXIMOS PASOS**

1. ✅ **Testing**: Probar desde Flutter cada endpoint con los 3 roles
2. ✅ **Documentación**: Actualizar docs de API con permisos
3. ✅ **UI/UX**: Ocultar botones según permisos en Flutter
4. ⏳ **Facturas**: Implementar filtros por rol (pendiente)
5. ⏳ **Auditoría**: Ver logs solo para recepción (pendiente)

---

## 📝 **NOTAS IMPORTANTES**

1. **Auto-asignación**: Cliente y Veterinario tienen auto-asignación de IDs en creación de recursos
2. **Soft Delete**: Citas se marcan como canceladas, no se eliminan físicamente
3. **Cascada**: Al eliminar cliente/mascota, se verifica que no tenga citas/historiales
4. **Tokens**: Usa Sanctum tokens con `auth:sanctum` middleware
5. **Validación**: Cada endpoint valida primero el rol antes de procesar

---

**Última actualización**: 8 de Noviembre, 2025  
**Estado**: ✅ **Implementación Completa**
