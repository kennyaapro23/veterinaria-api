# 🚶 Sistema de Clientes Walk-In - VetCare

## 📋 **¿Qué es un Cliente Walk-In?**

Un **cliente walk-in** es un cliente que llega directamente a la veterinaria **SIN tener una cuenta registrada** en la aplicación. Este es el flujo más común en veterinarias reales:

- ✅ Cliente llega sin cita
- ✅ Recepcionista registra al cliente y mascota en el momento
- ✅ Se genera historial médico completo
- ✅ Se pueden emitir facturas normalmente
- ❌ Cliente NO tiene acceso a la app móvil
- ❌ Cliente NO tiene email/contraseña

---

## 🎯 **Diferencia: Cliente Walk-In vs. Cliente con Cuenta**

| Característica | Cliente Walk-In | Cliente con Cuenta |
|---|---|---|
| **Registro** | Por recepcionista | Por el propio cliente (app) |
| **Email** | Opcional | Obligatorio |
| **Contraseña** | No tiene | Requerida |
| **user_id** | NULL | FK a users |
| **Acceso App Móvil** | ❌ No | ✅ Sí |
| **Agendar Citas** | Vía recepción | Desde la app |
| **Ver Historial** | Solo presencial | Desde la app |
| **QR de Mascota** | ✅ Sí tiene | ✅ Sí tiene |
| **Facturación** | ✅ Normal | ✅ Normal |

---

## 🗄️ **Cambios en Base de Datos**

### **Migración: `add_es_walk_in_to_clientes_table`**

```php
Schema::table('clientes', function (Blueprint $table) {
    $table->boolean('es_walk_in')->default(false)->after('user_id');
    $table->string('email')->nullable()->change(); // ✅ Email opcional
});
```

**Campos de clientes walk-in:**
- `user_id`: **NULL** (no vinculado a ningún usuario)
- `es_walk_in`: **true** (marcado explícitamente)
- `email`: **opcional** (puede ser NULL)
- `telefono`: **obligatorio** (para contactar)
- `nombre`: **obligatorio**

---

## 🚀 **Endpoint: Registro Rápido Walk-In**

### **POST `/api/clientes/registro-rapido`**

Crea un cliente walk-in + su mascota en **una sola transacción**.

**Headers:**
```http
Authorization: Bearer {token_recepcionista}
Accept: application/json
Content-Type: application/json
```

**Request Body (mínimo requerido):**
```json
{
  "cliente": {
    "nombre": "Carlos Rodríguez",
    "telefono": "+34611222333"
  },
  "mascota": {
    "nombre": "Rocky",
    "especie": "Perro",
    "sexo": "macho"
  }
}
```

**Request Body (completo con datos opcionales):**
```json
{
  "cliente": {
    "nombre": "Carlos Rodríguez",
    "telefono": "+34611222333",
    "email": "carlos@example.com",
    "direccion": "Calle Mayor 45, Madrid",
    "notas": "Cliente de confianza, prefiere citas por la tarde"
  },
  "mascota": {
    "nombre": "Rocky",
    "especie": "Perro",
    "raza": "Pitbull",
    "sexo": "macho",
    "fecha_nacimiento": "2020-05-15",
    "color": "Marrón",
    "peso": 25.5,
    "chip_id": "981234567890999",
    "alergias": "Penicilina",
    "condiciones_medicas": "Displasia de cadera leve",
    "tipo_sangre": "DEA 1.1+"
  }
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Cliente y mascota registrados exitosamente",
  "cliente": {
    "id": 2,
    "user_id": null,
    "es_walk_in": true,
    "nombre": "Carlos Rodriguez",
    "telefono": "+34611222333",
    "email": null,
    "direccion": null,
    "created_at": "2025-11-08T15:51:32.000000Z",
    "mascotas": [
      {
        "id": 4,
        "nombre": "Rocky",
        "especie": "Perro",
        "qr_code": "VETCARE_PET_40347737-ce64-4de7-8411-910a07f84c7c"
      }
    ]
  },
  "mascota": {
    "id": 4,
    "cliente_id": 2,
    "nombre": "Rocky",
    "especie": "Perro",
    "sexo": "macho",
    "qr_code": "VETCARE_PET_40347737-ce64-4de7-8411-910a07f84c7c"
  },
  "qr_code": "VETCARE_PET_40347737-ce64-4de7-8411-910a07f84c7c",
  "qr_url": "http://127.0.0.1:8000/api/qr/lookup/VETCARE_PET_40347737-ce64-4de7-8411-910a07f84c7c"
}
```

---

## 📱 **Flujo Completo: Atención Walk-In**

### **1. Cliente Llega a la Veterinaria**
```
Cliente sin cuenta → Recepción
```

### **2. Recepcionista Registra Cliente + Mascota**
```http
POST /api/clientes/registro-rapido
{
  "cliente": { "nombre": "...", "telefono": "..." },
  "mascota": { "nombre": "...", "especie": "...", "sexo": "..." }
}
```

### **3. Sistema Genera QR Automáticamente**
```
Mascota creada → QR: VETCARE_PET_{UUID}
```

### **4. Recepcionista Agenda Cita**
```http
POST /api/citas
{
  "cliente_id": 2,  ← Cliente walk-in
  "mascota_id": 4,
  "veterinario_id": 1,
  "fecha": "2025-11-10",
  "hora": "10:00",
  "motivo": "Vacunación"
}
```

### **5. Veterinario Atiende y Registra Historial**
```http
POST /api/historial-medico
{
  "mascota_id": 4,  ← Mascota de cliente walk-in
  "tipo": "vacuna",
  "diagnostico": "Vacuna antirrábica",
  "tratamiento": "Rabia vacuna 1ml IM"
}
```

### **6. Recepcionista Genera Factura**
```http
POST /api/facturas
{
  "cita_id": 123,
  "cliente_id": 2,  ← Cliente walk-in
  "total": 35.00
}
```

### **7. Cliente se Va con su QR**
```
Recepcionista imprime/muestra QR de la mascota
Cliente puede escanear QR en futuras visitas
```

---

## ✅ **Validaciones Implementadas**

### **Para Cliente Walk-In:**
```php
'cliente.nombre' => 'required|string|max:150',       // ✅ Obligatorio
'cliente.telefono' => 'required|string|max:20',      // ✅ Obligatorio
'cliente.email' => 'nullable|email|unique:clientes', // ✅ Opcional
'cliente.direccion' => 'nullable|string|max:255',    // ✅ Opcional
'cliente.notas' => 'nullable|string',                // ✅ Opcional
```

### **Para Mascota:**
```php
'mascota.nombre' => 'required|string|max:100',       // ✅ Obligatorio
'mascota.especie' => 'required|string|max:50',       // ✅ Obligatorio
'mascota.sexo' => 'required|in:macho,hembra',        // ✅ Obligatorio
'mascota.raza' => 'nullable|string|max:100',         // ✅ Opcional
'mascota.fecha_nacimiento' => 'nullable|date',       // ✅ Opcional
'mascota.color' => 'nullable|string|max:50',         // ✅ Opcional
'mascota.peso' => 'nullable|numeric|min:0',          // ✅ Opcional
'mascota.chip_id' => 'nullable|string|max:50',       // ✅ Opcional
'mascota.alergias' => 'nullable|string',             // ✅ Opcional
'mascota.condiciones_medicas' => 'nullable|string',  // ✅ Opcional
'mascota.tipo_sangre' => 'nullable|string|max:20',   // ✅ Opcional
```

---

## 🔍 **Consultas Útiles**

### **Listar solo clientes walk-in:**
```http
GET /api/clientes?es_walk_in=true
```

### **Listar clientes con cuenta:**
```http
GET /api/clientes?es_walk_in=false
```

### **Buscar cliente walk-in por teléfono:**
```http
GET /api/clientes?search=+34611222333
```

### **Ver todas las mascotas de un cliente walk-in:**
```http
GET /api/mascotas?cliente_id=2
```

---

## 🎨 **Implementación en Flutter (Recepcionista)**

### **Screen: Registro Rápido Walk-In**

```dart
class RegistroRapidoWalkInScreen extends StatefulWidget {
  @override
  _RegistroRapidoWalkInScreenState createState() => _RegistroRapidoWalkInScreenState();
}

class _RegistroRapidoWalkInScreenState extends State<RegistroRapidoWalkInScreen> {
  final _formKey = GlobalKey<FormState>();
  
  // Controladores Cliente
  final _nombreClienteController = TextEditingController();
  final _telefonoClienteController = TextEditingController();
  final _emailClienteController = TextEditingController();
  final _direccionClienteController = TextEditingController();
  
  // Controladores Mascota
  final _nombreMascotaController = TextEditingController();
  final _especieController = TextEditingController();
  final _razaController = TextEditingController();
  String _sexoSeleccionado = 'macho';

  Future<void> _registrarWalkIn() async {
    if (!_formKey.currentState!.validate()) return;
    
    try {
      final response = await ClienteService().registroRapido({
        'cliente': {
          'nombre': _nombreClienteController.text,
          'telefono': _telefonoClienteController.text,
          'email': _emailClienteController.text.isEmpty ? null : _emailClienteController.text,
          'direccion': _direccionClienteController.text.isEmpty ? null : _direccionClienteController.text,
        },
        'mascota': {
          'nombre': _nombreMascotaController.text,
          'especie': _especieController.text,
          'raza': _razaController.text.isEmpty ? null : _razaController.text,
          'sexo': _sexoSeleccionado,
        },
      });
      
      // Mostrar QR de la mascota
      await showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: Text('✅ Registro Exitoso'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text('Cliente: ${response['cliente']['nombre']}'),
              Text('Mascota: ${response['mascota']['nombre']}'),
              SizedBox(height: 16),
              QrImageView(
                data: response['qr_url'],
                size: 200,
              ),
              SizedBox(height: 8),
              Text('QR: ${response['qr_code']}', style: TextStyle(fontSize: 10)),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: Text('Aceptar'),
            ),
            ElevatedButton(
              onPressed: () {
                // Imprimir QR
              },
              child: Text('Imprimir QR'),
            ),
          ],
        ),
      );
      
      Navigator.pop(context, true); // Regresar con éxito
      
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: ${e.toString()}')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Registro Rápido Walk-In'),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: EdgeInsets.all(16),
          children: [
            // Sección Cliente
            Text(
              '👤 Datos del Cliente',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            
            TextFormField(
              controller: _nombreClienteController,
              decoration: InputDecoration(
                labelText: 'Nombre Completo *',
                prefixIcon: Icon(Icons.person),
              ),
              validator: (value) => value?.isEmpty == true ? 'Requerido' : null,
            ),
            SizedBox(height: 12),
            
            TextFormField(
              controller: _telefonoClienteController,
              decoration: InputDecoration(
                labelText: 'Teléfono *',
                prefixIcon: Icon(Icons.phone),
              ),
              keyboardType: TextInputType.phone,
              validator: (value) => value?.isEmpty == true ? 'Requerido' : null,
            ),
            SizedBox(height: 12),
            
            TextFormField(
              controller: _emailClienteController,
              decoration: InputDecoration(
                labelText: 'Email (opcional)',
                prefixIcon: Icon(Icons.email),
              ),
              keyboardType: TextInputType.emailAddress,
            ),
            SizedBox(height: 12),
            
            TextFormField(
              controller: _direccionClienteController,
              decoration: InputDecoration(
                labelText: 'Dirección (opcional)',
                prefixIcon: Icon(Icons.home),
              ),
            ),
            SizedBox(height: 24),
            
            Divider(),
            
            // Sección Mascota
            Text(
              '🐾 Datos de la Mascota',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            
            TextFormField(
              controller: _nombreMascotaController,
              decoration: InputDecoration(
                labelText: 'Nombre de la Mascota *',
                prefixIcon: Icon(Icons.pets),
              ),
              validator: (value) => value?.isEmpty == true ? 'Requerido' : null,
            ),
            SizedBox(height: 12),
            
            TextFormField(
              controller: _especieController,
              decoration: InputDecoration(
                labelText: 'Especie *',
                prefixIcon: Icon(Icons.category),
                hintText: 'Perro, Gato, etc.',
              ),
              validator: (value) => value?.isEmpty == true ? 'Requerido' : null,
            ),
            SizedBox(height: 12),
            
            TextFormField(
              controller: _razaController,
              decoration: InputDecoration(
                labelText: 'Raza (opcional)',
                prefixIcon: Icon(Icons.info_outline),
              ),
            ),
            SizedBox(height: 12),
            
            DropdownButtonFormField<String>(
              value: _sexoSeleccionado,
              decoration: InputDecoration(
                labelText: 'Sexo *',
                prefixIcon: Icon(Icons.wc),
              ),
              items: [
                DropdownMenuItem(value: 'macho', child: Text('Macho')),
                DropdownMenuItem(value: 'hembra', child: Text('Hembra')),
              ],
              onChanged: (value) => setState(() => _sexoSeleccionado = value!),
            ),
            SizedBox(height: 24),
            
            ElevatedButton(
              onPressed: _registrarWalkIn,
              style: ElevatedButton.styleFrom(
                padding: EdgeInsets.all(16),
              ),
              child: Text('Registrar Cliente y Mascota', style: TextStyle(fontSize: 16)),
            ),
          ],
        ),
      ),
    );
  }
}
```

### **Service: ClienteService**

```dart
class ClienteService {
  final ApiService _apiService = ApiService();
  
  Future<Map<String, dynamic>> registroRapido(Map<String, dynamic> data) async {
    final response = await _apiService.post('/clientes/registro-rapido', data);
    return response.data;
  }
  
  Future<List<Cliente>> getClientesWalkIn() async {
    final response = await _apiService.get('/clientes?es_walk_in=true');
    return (response.data['data'] as List)
        .map((json) => Cliente.fromJson(json))
        .toList();
  }
}
```

---

## 🎯 **Ventajas del Sistema Walk-In**

✅ **Registro ultra-rápido**: Solo nombre + teléfono + mascota
✅ **No requiere email**: Perfecto para clientes sin tecnología
✅ **QR instantáneo**: Mascota tiene QR desde el registro
✅ **Historial completo**: Igual que clientes con cuenta
✅ **Facturación normal**: No hay diferencia
✅ **Escalable**: Cliente puede crear cuenta después si quiere

---

## 📊 **Estadísticas**

### **Obtener total de clientes walk-in:**
```php
$total = Cliente::where('es_walk_in', true)->count();
```

### **Obtener clientes walk-in del mes:**
```php
$este_mes = Cliente::where('es_walk_in', true)
    ->whereMonth('created_at', now()->month)
    ->count();
```

### **Cliente walk-in con más visitas:**
```php
$top_cliente = Cliente::where('es_walk_in', true)
    ->withCount('citas')
    ->orderBy('citas_count', 'desc')
    ->first();
```

---

## 🔐 **Permisos y Roles**

| Rol | Puede crear walk-in | Puede ver walk-ins | Puede agendar cita |
|---|---|---|---|
| **Recepcionista** | ✅ Sí | ✅ Todos | ✅ Sí |
| **Veterinario** | ❌ No | ✅ Solo sus citas | ❌ No |
| **Admin** | ✅ Sí | ✅ Todos | ✅ Sí |
| **Cliente** | ❌ No | ❌ No | ❌ No |

---

## 🚀 **Próximas Mejoras**

1. ✅ **Conversión walk-in → cuenta**: Permitir que un cliente walk-in cree una cuenta después
2. ✅ **Búsqueda por teléfono mejorada**: Evitar duplicados al registrar
3. ✅ **Historial de visitas walk-in**: Dashboard para recepción
4. ✅ **Impresión de QR**: Directamente desde la app
5. ✅ **SMS de recordatorio**: Para clientes walk-in sin email

---

## 📝 **Ejemplos de Testing**

### **Test 1: Crear walk-in exitoso**
```bash
POST /api/clientes/registro-rapido
{
  "cliente": {"nombre": "Test", "telefono": "+34600000000"},
  "mascota": {"nombre": "TestPet", "especie": "Perro", "sexo": "macho"}
}
→ Esperado: 201 Created con QR generado
```

### **Test 2: Sin teléfono (debe fallar)**
```bash
POST /api/clientes/registro-rapido
{
  "cliente": {"nombre": "Test"},
  "mascota": {"nombre": "TestPet", "especie": "Perro", "sexo": "macho"}
}
→ Esperado: 422 Validation Error
```

### **Test 3: Listar walk-ins**
```bash
GET /api/clientes?es_walk_in=true
→ Esperado: Lista de clientes con user_id = null
```

---

## ✅ **Estado Actual**

- ✅ Migración ejecutada (`es_walk_in` agregado)
- ✅ Modelo `Cliente` actualizado
- ✅ Endpoint `/api/clientes/registro-rapido` funcional
- ✅ Validaciones implementadas
- ✅ QR generado automáticamente
- ✅ Auditoría activada
- ✅ Probado y funcionando

**Fecha de implementación:** 8 de noviembre de 2025  
**Backend Version:** Laravel 12.37.0  
**Endpoint:** `POST /api/clientes/registro-rapido`
