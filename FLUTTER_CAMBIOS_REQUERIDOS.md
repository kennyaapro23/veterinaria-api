# 📱 Guía de Cambios Requeridos - Flutter Frontend

## 🎯 **Resumen Ejecutivo**

El backend fue actualizado con:
1. ✅ **FIX Error 500**: Todos los endpoints ahora funcionan correctamente
2. ✅ **Sistema Walk-In**: Clientes sin cuenta para atención rápida
3. ✅ **Email opcional**: Clientes walk-in solo necesitan nombre + teléfono

---

## 📋 **CAMBIOS REQUERIDOS**

### **1️⃣ Actualizar Modelo Cliente**

**Archivo:** `lib/models/cliente.dart`

**Agregar estos campos:**

```dart
class Cliente {
  // ... campos existentes ...
  
  final bool esWalkIn;          // ✅ NUEVO: Identifica si es walk-in
  final int? userId;            // ✅ CAMBIAR: de 'required' a nullable
  final String? email;          // ✅ CAMBIAR: de 'required' a nullable
  
  // ✅ NUEVO: Helpers útiles
  bool get tieneUsuario => userId != null;
  bool get puedeUsarApp => tieneUsuario && !esWalkIn;
  String get tipoBadge => esWalkIn ? 'Walk-In' : 'Registrado';
  Color get tipoBadgeColor => esWalkIn ? Colors.orange : Colors.green;
}
```

**Actualizar constructor:**
- Agregar `this.esWalkIn = false`
- Cambiar `required this.userId` → `this.userId`
- Cambiar `required this.email` → `this.email`

**Actualizar `fromJson`:**
- Agregar: `esWalkIn: json['es_walk_in'] ?? false`

**Actualizar `toJson`:**
- Agregar: `'es_walk_in': esWalkIn`

---

### **2️⃣ Actualizar ClienteService**

**Archivo:** `lib/services/cliente_service.dart`

**Agregar 3 nuevos métodos:**

#### **A) Método: `registroRapido()`**
```dart
// Endpoint: POST /api/clientes/registro-rapido
// Recibe: datos de cliente + datos de mascota
// Retorna: Map con cliente, mascota, qr_code, qr_url
```

**Parámetros requeridos:**
- `nombreCliente` (String)
- `telefonoCliente` (String)
- `nombreMascota` (String)
- `especieMascota` (String)
- `sexoMascota` (String: 'macho' o 'hembra')

**Parámetros opcionales:**
- `emailCliente`, `direccionCliente`, `notasCliente`
- `razaMascota`, `colorMascota`, `pesoMascota`, etc.

#### **B) Método: `getClientesWalkIn()`**
```dart
// Endpoint: GET /api/clientes?es_walk_in=true
// Retorna: List<Cliente> solo walk-ins
```

#### **C) Método: `getClientesConCuenta()`**
```dart
// Endpoint: GET /api/clientes?es_walk_in=false
// Retorna: List<Cliente> solo registrados
```

---

### **3️⃣ CREAR Pantalla de Registro Rápido**

**Archivo NUEVO:** `lib/screens/recepcion/clientes/registro_rapido_screen.dart`

**Estructura:**

```
RegistroRapidoScreen
├── Formulario con 2 secciones:
│   ├── 📋 SECCIÓN 1: Datos del Cliente
│   │   ├── Nombre * (required)
│   │   ├── Teléfono * (required)
│   │   ├── Email (opcional)
│   │   ├── Dirección (opcional)
│   │   └── Notas (opcional)
│   │
│   └── 🐾 SECCIÓN 2: Datos de la Mascota
│       ├── Nombre * (required)
│       ├── Especie * (required)
│       ├── Sexo * (required: macho/hembra)
│       ├── Raza (opcional)
│       ├── Color (opcional)
│       └── Peso (opcional)
│
├── Botón: "REGISTRAR CLIENTE Y MASCOTA"
│
└── Al éxito → Mostrar Dialog con:
    ├── ✅ Datos del cliente registrado
    ├── ✅ Datos de la mascota registrada
    ├── 📱 QR Code de la mascota
    └── Botones: [Cerrar] [Imprimir QR]
```

**Validaciones:**
- Solo 3 campos obligatorios: nombre cliente, teléfono cliente, nombre mascota, especie, sexo
- Resto es opcional
- Teléfono debe ser formato válido

**Al registrar exitosamente:**
1. Llamar `ClienteService().registroRapido()`
2. Mostrar dialog con QR usando `QrImageView()`
3. Cerrar y regresar con `Navigator.pop(context, true)`

---

### **4️⃣ Actualizar Dashboard Recepcionista**

**Archivo:** `lib/screens/recepcion/recepcion_home_screen.dart`

**Agregar botón destacado:**

```
┌─────────────────────────────────────┐
│  🟧 BOTÓN DESTACADO (color naranja) │
│  Icono: person_add                  │
│  Título: "Registro Rápido"          │
│  Subtítulo: "Cliente Walk-In"       │
│  onTap: Navigator.pushNamed(        │
│    '/recepcion/registro-rapido'     │
│  )                                   │
└─────────────────────────────────────┘
```

**Ubicación:** Primer elemento visible después del header de bienvenida

---

### **5️⃣ Actualizar Lista de Clientes**

**Archivo:** `lib/screens/recepcion/clientes/clientes_screen.dart`

**Agregar sistema de filtros:**

```
AppBar
├── actions: [Filter Icon]
    └── PopupMenu con opciones:
        ├── Todos los clientes
        ├── Solo Walk-In
        └── Solo Registrados
```

**Agregar chips de filtro rápido:**
```
[Chip: Todos] [Chip: 🚶 Walk-In] [Chip: ✓ Registrados]
```

**Actualizar Card de cliente:**
```
ListTile
├── leading: CircleAvatar con icono según tipo
├── title: Row
│   ├── Nombre del cliente
│   └── Badge: "Walk-In" (naranja) o "Registrado" (verde)
├── subtitle:
│   ├── 📞 Teléfono
│   └── 📧 Email (si existe)
└── trailing: chevron_right
```

**Actualizar FloatingActionButton:**
- Cambiar a `FloatingActionButton.extended`
- Icon: `person_add`
- Label: "Walk-In"
- Color: `Colors.orange`
- onPressed: Navegar a `/recepcion/registro-rapido`

---

### **6️⃣ Actualizar Rutas**

**Archivo:** `lib/config/routes.dart`

**Agregar ruta:**
```dart
'/recepcion/registro-rapido': (context) => RegistroRapidoScreen(),
```

**Importar:**
```dart
import '../screens/recepcion/clientes/registro_rapido_screen.dart';
```

---

## 🎨 **Especificaciones de UI**

### **Colores para Walk-In:**
- Badge: `Colors.orange`
- Botón principal: `Colors.orange.shade600`
- Icon: `Icons.directions_walk`

### **Colores para Registrados:**
- Badge: `Colors.green`
- Icon: `Icons.verified_user`

### **Iconografía:**
```
Walk-In:
- Icon principal: Icons.directions_walk
- Icon secundario: Icons.person_add
- Color: Orange

Registrado:
- Icon principal: Icons.verified_user
- Icon secundario: Icons.account_circle
- Color: Green
```

---

## 📊 **Flujo de Usuario (UX)**

### **Flujo 1: Registro Rápido desde Dashboard**
```
Dashboard Recepción
→ Tap en "Registro Rápido Walk-In"
→ RegistroRapidoScreen
→ Llenar 3 campos mínimos
→ Tap "REGISTRAR"
→ Dialog con QR
→ [Opción: Imprimir QR]
→ Cerrar → Volver a Dashboard
```

### **Flujo 2: Registro desde Lista de Clientes**
```
Lista de Clientes
→ Tap FAB "Walk-In"
→ RegistroRapidoScreen
→ (mismo flujo anterior)
```

### **Flujo 3: Filtrar Clientes Walk-In**
```
Lista de Clientes
→ Tap Filter Icon
→ Seleccionar "Solo Walk-In"
→ Lista muestra solo clientes walk-in
→ Cada card tiene badge naranja "Walk-In"
```

---

## ✅ **Checklist de Implementación**

### **Orden recomendado:**

- [ ] **Paso 1** (5 min): Actualizar `Cliente` model
  - Agregar campo `esWalkIn`
  - Hacer `userId` y `email` nullable
  - Agregar helpers (`tipoBadge`, `tipoBadgeColor`)

- [ ] **Paso 2** (10 min): Actualizar `ClienteService`
  - Método `registroRapido()`
  - Método `getClientesWalkIn()`
  - Método `getClientesConCuenta()`

- [ ] **Paso 3** (30 min): Crear `RegistroRapidoScreen`
  - Formulario con 2 secciones
  - Validaciones básicas
  - Dialog con QR al éxito

- [ ] **Paso 4** (5 min): Actualizar rutas
  - Agregar ruta a `routes.dart`

- [ ] **Paso 5** (10 min): Actualizar dashboard recepción
  - Botón destacado naranja
  - Navegación a registro rápido

- [ ] **Paso 6** (15 min): Actualizar lista de clientes
  - Sistema de filtros
  - Badges por tipo de cliente
  - FAB para walk-in

---

## 🧪 **Testing**

### **Casos de prueba:**

1. ✅ **Registro mínimo exitoso:**
   - Solo llenar: nombre, teléfono, nombre mascota, especie, sexo
   - Debe registrar y mostrar QR

2. ✅ **Registro completo exitoso:**
   - Llenar todos los campos
   - Debe registrar con toda la info

3. ✅ **Validación de campos requeridos:**
   - Dejar nombre vacío → Error
   - Dejar teléfono vacío → Error
   - Email vacío → OK (es opcional)

4. ✅ **Filtros funcionando:**
   - Filtro "Walk-In" → Solo muestra walk-ins
   - Filtro "Registrados" → Solo muestra con cuenta
   - Filtro "Todos" → Muestra ambos

5. ✅ **Badges visibles:**
   - Walk-in debe mostrar badge naranja "Walk-In"
   - Registrado debe mostrar badge verde "Registrado"

---

## 📱 **Dependencias**

**Verificar que existan en `pubspec.yaml`:**
```yaml
dependencies:
  qr_flutter: ^4.1.0  # Para mostrar QR codes
  http: ^1.1.0        # Para llamadas API
  provider: ^6.1.1    # State management
```

Si no existen, ejecutar:
```bash
flutter pub add qr_flutter
flutter pub get
```

---

## 🚀 **Endpoint del Backend**

### **Registro Rápido:**
```http
POST /api/clientes/registro-rapido
Authorization: Bearer {token}
Content-Type: application/json

{
  "cliente": {
    "nombre": "Carlos Rodriguez",
    "telefono": "+34611222333"
  },
  "mascota": {
    "nombre": "Rocky",
    "especie": "Perro",
    "sexo": "macho"
  }
}
```

**Response 201:**
```json
{
  "success": true,
  "cliente": { "id": 2, "nombre": "...", "es_walk_in": true },
  "mascota": { "id": 4, "nombre": "Rocky", "qr_code": "VETCARE_PET_..." },
  "qr_code": "VETCARE_PET_40347737-ce64-4de7-8411-910a07f84c7c",
  "qr_url": "http://127.0.0.1:8000/api/qr/lookup/VETCARE_PET_..."
}
```

---

## 💡 **Tips de Implementación**

1. **Reutiliza widgets existentes:**
   - Si tienes `CustomTextField`, úsalo
   - Si tienes `CustomButton`, úsalo
   - Si tienes `LoadingWidget`, úsalo

2. **Manejo de errores:**
   - Usa try-catch en todas las llamadas al API
   - Muestra SnackBar con el error
   - No dejes la app colgada si falla

3. **UX mejorada:**
   - Loading indicator mientras registra
   - Deshabilita botón mientras procesa
   - Vibración o sonido al éxito (opcional)

4. **Validación de teléfono:**
   - Acepta formato internacional (+34...)
   - Acepta formato local (611...)
   - Mínimo 9 dígitos

5. **QR Dialog:**
   - Tamaño QR: 200x200 pixels
   - Fondo blanco con padding
   - Border gris claro
   - Botón para cerrar
   - Botón para imprimir (implementar después)

---

## 📖 **Documentación Backend**

Para más detalles del backend, ver:
- `SISTEMA_WALK_IN_README.md` - Sistema completo walk-in
- `ROLES_Y_FUNCIONALIDADES.md` - Permisos por rol
- `SISTEMA_QR_README.md` - Sistema de QR codes

---

## ❓ **Preguntas Frecuentes**

**Q: ¿Por qué email es opcional?**
A: Muchos clientes walk-in no tienen o no quieren dar email. El teléfono es suficiente.

**Q: ¿Puedo convertir un walk-in a cliente registrado después?**
A: Sí, pero esa funcionalidad se implementará en el futuro.

**Q: ¿Los walk-ins pueden usar la app móvil?**
A: No, porque no tienen credenciales (user_id es null).

**Q: ¿Los walk-ins tienen QR de mascota?**
A: Sí, todas las mascotas tienen QR, independientemente del tipo de cliente.

**Q: ¿Puedo facturar a un cliente walk-in?**
A: Sí, la facturación funciona igual para ambos tipos.

---

## 🎯 **Resultado Final Esperado**

Después de implementar todos los cambios:

✅ Recepcionista puede registrar cliente + mascota en **menos de 1 minuto**
✅ Sistema distingue visualmente entre walk-ins y registrados
✅ Filtros permiten ver solo el tipo de cliente deseado
✅ QR se genera automáticamente al registrar
✅ Experiencia fluida y rápida para atención en mostrador

---

**Tiempo total estimado: 1-1.5 horas** ⏱️

**Archivos a modificar: 5**
**Archivos a crear: 1**
**Endpoints nuevos: 1**

---

📅 **Fecha:** 8 de noviembre de 2025
🔧 **Backend Version:** Laravel 12.37.0
📱 **Flutter Version:** 3.x+
