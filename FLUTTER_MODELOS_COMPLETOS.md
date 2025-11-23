# 📦 Modelos Completos Flutter - VetCare API

## 🎯 Modelos Faltantes

Esta guía complementa `FLUTTER_INTEGRATION_COMPLETE.md` con los modelos que faltaban.

---

## 1️⃣ Factura Model (lib/models/factura.dart)

```dart
class Factura {
  final int? id;
  final int clienteId;
  final int? citaId;
  final double total;
  final String estado;            // 'pendiente', 'pagado', 'anulado'
  final String? metodoPago;       // 'efectivo', 'tarjeta', 'transferencia', 'yape', 'plin'
  final Map<String, dynamic>? detalles;  // JSON con items de la factura
  final DateTime? createdAt;
  final DateTime? updatedAt;
  
  // Relaciones opcionales
  final Cliente? cliente;
  final Cita? cita;

  Factura({
    this.id,
    required this.clienteId,
    this.citaId,
    required this.total,
    required this.estado,
    this.metodoPago,
    this.detalles,
    this.createdAt,
    this.updatedAt,
    this.cliente,
    this.cita,
  });

  // ✅ CAMPOS REQUERIDOS EN BACKEND:
  // - cliente_id (int, obligatorio)
  // - total (decimal, obligatorio)
  // - estado (enum, default: 'pendiente')

  factory Factura.fromJson(Map<String, dynamic> json) {
    return Factura(
      id: json['id'],
      clienteId: json['cliente_id'],
      citaId: json['cita_id'],
      total: double.parse(json['total'].toString()),
      estado: json['estado'],
      metodoPago: json['metodo_pago'],
      detalles: json['detalles'] != null 
          ? Map<String, dynamic>.from(json['detalles'])
          : null,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.parse(json['updated_at'])
          : null,
      cliente: json['cliente'] != null
          ? Cliente.fromJson(json['cliente'])
          : null,
      cita: json['cita'] != null
          ? Cita.fromJson(json['cita'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'cliente_id': clienteId,
      'total': total,
      'estado': estado,
      if (citaId != null) 'cita_id': citaId,
      if (metodoPago != null) 'metodo_pago': metodoPago,
      if (detalles != null) 'detalles': detalles,
    };
  }

  // Helper: Formatear total como moneda
  String get totalFormateado {
    return 'S/. ${total.toStringAsFixed(2)}';
  }

  // Helper: Color según estado
  Color get estadoColor {
    switch (estado) {
      case 'pagado':
        return Colors.green;
      case 'pendiente':
        return Colors.orange;
      case 'anulado':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}
```

---

## 2️⃣ HistorialMedico Model (lib/models/historial_medico.dart)

```dart
class HistorialMedico {
  final int? id;
  final int mascotaId;
  final int? citaId;
  final DateTime fecha;
  final String tipo;              // 'consulta', 'vacuna', 'procedimiento', 'control', 'otro'
  final String? diagnostico;
  final String? tratamiento;
  final String? observaciones;
  final int? realizadoPor;        // veterinario_id
  final Map<String, dynamic>? archivosMeta;
  final DateTime? createdAt;
  final DateTime? updatedAt;
  
  // Relaciones opcionales
  final Mascota? mascota;
  final Cita? cita;
  final Veterinario? veterinario;
  final List<Archivo>? archivos;

  HistorialMedico({
    this.id,
    required this.mascotaId,
    this.citaId,
    required this.fecha,
    required this.tipo,
    this.diagnostico,
    this.tratamiento,
    this.observaciones,
    this.realizadoPor,
    this.archivosMeta,
    this.createdAt,
    this.updatedAt,
    this.mascota,
    this.cita,
    this.veterinario,
    this.archivos,
  });

  // ✅ CAMPOS REQUERIDOS EN BACKEND:
  // - mascota_id (int, obligatorio)
  // - fecha (datetime, obligatorio, default: now())
  // - tipo (enum, obligatorio, default: 'consulta')

  factory HistorialMedico.fromJson(Map<String, dynamic> json) {
    return HistorialMedico(
      id: json['id'],
      mascotaId: json['mascota_id'],
      citaId: json['cita_id'],
      fecha: DateTime.parse(json['fecha']),
      tipo: json['tipo'],
      diagnostico: json['diagnostico'],
      tratamiento: json['tratamiento'],
      observaciones: json['observaciones'],
      realizadoPor: json['realizado_por'],
      archivosMeta: json['archivos_meta'] != null
          ? Map<String, dynamic>.from(json['archivos_meta'])
          : null,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.parse(json['updated_at'])
          : null,
      mascota: json['mascota'] != null
          ? Mascota.fromJson(json['mascota'])
          : null,
      cita: json['cita'] != null
          ? Cita.fromJson(json['cita'])
          : null,
      veterinario: json['veterinario'] != null
          ? Veterinario.fromJson(json['veterinario'])
          : null,
      archivos: json['archivos'] != null
          ? (json['archivos'] as List)
              .map((a) => Archivo.fromJson(a))
              .toList()
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'mascota_id': mascotaId,
      'fecha': fecha.toIso8601String(),
      'tipo': tipo,
      if (citaId != null) 'cita_id': citaId,
      if (diagnostico != null) 'diagnostico': diagnostico,
      if (tratamiento != null) 'tratamiento': tratamiento,
      if (observaciones != null) 'observaciones': observaciones,
      if (realizadoPor != null) 'realizado_por': realizadoPor,
      if (archivosMeta != null) 'archivos_meta': archivosMeta,
    };
  }

  // Helper: Icono según tipo
  IconData get tipoIcon {
    switch (tipo) {
      case 'consulta':
        return Icons.medical_services;
      case 'vacuna':
        return Icons.vaccines;
      case 'procedimiento':
        return Icons.healing;
      case 'control':
        return Icons.health_and_safety;
      default:
        return Icons.folder;
    }
  }
}
```

---

## 3️⃣ AgendaDisponibilidad Model (lib/models/agenda_disponibilidad.dart)

```dart
class AgendaDisponibilidad {
  final int? id;
  final int veterinarioId;
  final int diaSemana;            // 0=domingo, 1=lunes, 2=martes, ..., 6=sábado
  final String horaInicio;        // Formato: "09:00:00"
  final String horaFin;           // Formato: "18:00:00"
  final int intervaloMinutos;     // Default: 30
  final bool activo;
  final DateTime? createdAt;
  final DateTime? updatedAt;
  
  // Relación opcional
  final Veterinario? veterinario;

  AgendaDisponibilidad({
    this.id,
    required this.veterinarioId,
    required this.diaSemana,
    required this.horaInicio,
    required this.horaFin,
    this.intervaloMinutos = 30,
    this.activo = true,
    this.createdAt,
    this.updatedAt,
    this.veterinario,
  });

  // ✅ CAMPOS REQUERIDOS EN BACKEND:
  // - veterinario_id (int, obligatorio)
  // - dia_semana (tinyint, obligatorio, 0-6)
  // - hora_inicio (time, obligatorio)
  // - hora_fin (time, obligatorio)
  // - intervalo_minutos (int, default: 30)
  // - activo (bool, default: true)

  factory AgendaDisponibilidad.fromJson(Map<String, dynamic> json) {
    return AgendaDisponibilidad(
      id: json['id'],
      veterinarioId: json['veterinario_id'],
      diaSemana: json['dia_semana'],
      horaInicio: json['hora_inicio'],
      horaFin: json['hora_fin'],
      intervaloMinutos: json['intervalo_minutos'] ?? 30,
      activo: json['activo'] == 1 || json['activo'] == true,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.parse(json['updated_at'])
          : null,
      veterinario: json['veterinario'] != null
          ? Veterinario.fromJson(json['veterinario'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'veterinario_id': veterinarioId,
      'dia_semana': diaSemana,
      'hora_inicio': horaInicio,
      'hora_fin': horaFin,
      'intervalo_minutos': intervaloMinutos,
      'activo': activo,
    };
  }

  // Helper: Nombre del día
  String get nombreDia {
    const dias = [
      'Domingo',
      'Lunes',
      'Martes',
      'Miércoles',
      'Jueves',
      'Viernes',
      'Sábado'
    ];
    return dias[diaSemana];
  }

  // Helper: Formatear horario
  String get horarioFormateado {
    final inicio = horaInicio.substring(0, 5); // "09:00"
    final fin = horaFin.substring(0, 5);       // "18:00"
    return '$inicio - $fin';
  }

  // Helper: Generar slots de tiempo disponibles
  List<String> get slotsDisponibles {
    List<String> slots = [];
    
    // Parsear horas
    final inicioPartes = horaInicio.split(':');
    int horaActual = int.parse(inicioPartes[0]);
    int minutoActual = int.parse(inicioPartes[1]);
    
    final finPartes = horaFin.split(':');
    final horaFin = int.parse(finPartes[0]);
    final minutoFin = int.parse(finPartes[1]);
    
    // Generar slots
    while (horaActual < horaFin || 
           (horaActual == horaFin && minutoActual < minutoFin)) {
      slots.add('${horaActual.toString().padLeft(2, '0')}:'
                '${minutoActual.toString().padLeft(2, '0')}');
      
      minutoActual += intervaloMinutos;
      if (minutoActual >= 60) {
        horaActual++;
        minutoActual -= 60;
      }
    }
    
    return slots;
  }
}
```

---

## 4️⃣ Archivo Model (lib/models/archivo.dart)

```dart
class Archivo {
  final int? id;
  final String relacionadoTipo;   // 'App\\Models\\HistorialMedico', etc.
  final int relacionadoId;
  final String nombre;
  final String url;
  final String? tipoMime;         // 'image/jpeg', 'application/pdf', etc.
  final int? size;                // Tamaño en bytes
  final int? uploadedBy;          // user_id
  final DateTime? createdAt;
  final DateTime? updatedAt;
  
  // Relación opcional
  final User? uploader;

  Archivo({
    this.id,
    required this.relacionadoTipo,
    required this.relacionadoId,
    required this.nombre,
    required this.url,
    this.tipoMime,
    this.size,
    this.uploadedBy,
    this.createdAt,
    this.updatedAt,
    this.uploader,
  });

  // ✅ CAMPOS REQUERIDOS EN BACKEND:
  // - relacionado_tipo (string, obligatorio)
  // - relacionado_id (int, obligatorio)
  // - nombre (string, obligatorio)
  // - url (string, obligatorio)

  factory Archivo.fromJson(Map<String, dynamic> json) {
    return Archivo(
      id: json['id'],
      relacionadoTipo: json['relacionado_tipo'],
      relacionadoId: json['relacionado_id'],
      nombre: json['nombre'],
      url: json['url'],
      tipoMime: json['tipo_mime'],
      size: json['size'],
      uploadedBy: json['uploaded_by'],
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.parse(json['updated_at'])
          : null,
      uploader: json['uploader'] != null
          ? User.fromJson(json['uploader'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'relacionado_tipo': relacionadoTipo,
      'relacionado_id': relacionadoId,
      'nombre': nombre,
      'url': url,
      if (tipoMime != null) 'tipo_mime': tipoMime,
      if (size != null) 'size': size,
      if (uploadedBy != null) 'uploaded_by': uploadedBy,
    };
  }

  // Helper: Verificar si es imagen
  bool get esImagen {
    if (tipoMime == null) return false;
    return tipoMime!.startsWith('image/');
  }

  // Helper: Verificar si es PDF
  bool get esPDF {
    return tipoMime == 'application/pdf';
  }

  // Helper: Tamaño formateado
  String get sizeFormateado {
    if (size == null) return 'Desconocido';
    
    if (size! < 1024) return '$size B';
    if (size! < 1024 * 1024) return '${(size! / 1024).toStringAsFixed(1)} KB';
    return '${(size! / (1024 * 1024)).toStringAsFixed(1)} MB';
  }

  // Helper: Icono según tipo
  IconData get tipoIcon {
    if (esImagen) return Icons.image;
    if (esPDF) return Icons.picture_as_pdf;
    return Icons.insert_drive_file;
  }
}
```

---

## 5️⃣ User Model (lib/models/user.dart)

```dart
class User {
  final int? id;
  final String? firebaseUid;
  final String name;
  final String email;
  final String? telefono;
  final String? tipoUsuario;      // 'cliente', 'veterinario', 'recepcion', 'admin'
  final DateTime? emailVerifiedAt;
  final DateTime? createdAt;
  final DateTime? updatedAt;
  
  // Relaciones opcionales
  final List<String>? roles;

  User({
    this.id,
    this.firebaseUid,
    required this.name,
    required this.email,
    this.telefono,
    this.tipoUsuario,
    this.emailVerifiedAt,
    this.createdAt,
    this.updatedAt,
    this.roles,
  });

  // ✅ CAMPOS REQUERIDOS EN BACKEND:
  // - name (string, obligatorio)
  // - email (string, obligatorio, único)
  // - password (string, obligatorio si no es Firebase)
  // - firebase_uid (string, opcional, único)

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      firebaseUid: json['firebase_uid'],
      name: json['name'],
      email: json['email'],
      telefono: json['telefono'],
      tipoUsuario: json['tipo_usuario'],
      emailVerifiedAt: json['email_verified_at'] != null
          ? DateTime.parse(json['email_verified_at'])
          : null,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.parse(json['updated_at'])
          : null,
      roles: json['roles'] != null
          ? List<String>.from(json['roles'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'name': name,
      'email': email,
      if (telefono != null) 'telefono': telefono,
      if (tipoUsuario != null) 'tipo_usuario': tipoUsuario,
      if (firebaseUid != null) 'firebase_uid': firebaseUid,
    };
  }

  // Helper: Verificar si tiene rol
  bool tieneRol(String rol) {
    if (roles == null) return false;
    return roles!.contains(rol);
  }

  // Helper: Verificar si es admin
  bool get esAdmin => tieneRol('admin');

  // Helper: Verificar si es cliente
  bool get esCliente => tieneRol('cliente');

  // Helper: Verificar si es veterinario
  bool get esVeterinario => tieneRol('veterinario');
}
```

---

## 📡 Servicios API para los Nuevos Modelos

### FacturaService (lib/services/factura_service.dart)

```dart
import '../models/factura.dart';
import 'api_service.dart';

class FacturaService {
  final ApiService _apiService = ApiService();

  // Listar facturas
  Future<List<Factura>> getFacturas({
    int? clienteId,
    String? estado,
  }) async {
    String endpoint = 'facturas';
    
    List<String> params = [];
    if (clienteId != null) params.add('cliente_id=$clienteId');
    if (estado != null) params.add('estado=$estado');
    
    if (params.isNotEmpty) {
      endpoint += '?${params.join('&')}';
    }

    final response = await _apiService.get(endpoint);
    
    if (response['success']) {
      return (response['data']['data'] as List)
          .map((json) => Factura.fromJson(json))
          .toList();
    }
    
    throw Exception(response['message']);
  }

  // Crear factura
  Future<Factura> crearFactura(Factura factura) async {
    final response = await _apiService.post('facturas', factura.toJson());
    
    if (response['success']) {
      return Factura.fromJson(response['data']);
    }
    
    throw Exception(response['message']);
  }

  // Obtener estadísticas
  Future<Map<String, dynamic>> getEstadisticas() async {
    final response = await _apiService.get('facturas-estadisticas');
    
    if (response['success']) {
      return response['data'];
    }
    
    throw Exception(response['message']);
  }

  // Generar número de factura
  Future<String> generarNumeroFactura() async {
    final response = await _apiService.get('generar-numero-factura');
    
    if (response['success']) {
      return response['data']['numero_factura'];
    }
    
    throw Exception(response['message']);
  }
}
```

### HistorialMedicoService (lib/services/historial_medico_service.dart)

```dart
import '../models/historial_medico.dart';
import 'api_service.dart';

class HistorialMedicoService {
  final ApiService _apiService = ApiService();

  // Listar historial médico
  Future<List<HistorialMedico>> getHistorial({
    int? mascotaId,
    String? tipo,
  }) async {
    String endpoint = 'historial-medico';
    
    List<String> params = [];
    if (mascotaId != null) params.add('mascota_id=$mascotaId');
    if (tipo != null) params.add('tipo=$tipo');
    
    if (params.isNotEmpty) {
      endpoint += '?${params.join('&')}';
    }

    final response = await _apiService.get(endpoint);
    
    if (response['success']) {
      return (response['data']['data'] as List)
          .map((json) => HistorialMedico.fromJson(json))
          .toList();
    }
    
    throw Exception(response['message']);
  }

  // Crear registro médico
  Future<HistorialMedico> crearRegistro(HistorialMedico registro) async {
    final response = await _apiService.post(
      'historial-medico',
      registro.toJson(),
    );
    
    if (response['success']) {
      return HistorialMedico.fromJson(response['data']);
    }
    
    throw Exception(response['message']);
  }

  // Subir archivo
  Future<void> subirArchivo(int registroId, File archivo) async {
    // Implementar con multipart/form-data
    // Ver: package:http con MultipartRequest
  }
}
```

---

## ✅ Checklist de Implementación

### Modelos
- [ ] Crear `factura.dart`
- [ ] Crear `historial_medico.dart`
- [ ] Crear `agenda_disponibilidad.dart`
- [ ] Crear `archivo.dart`
- [ ] Crear `user.dart`

### Servicios
- [ ] Crear `factura_service.dart`
- [ ] Crear `historial_medico_service.dart`

### Pantallas
- [ ] Pantalla de facturas del cliente
- [ ] Pantalla de detalle de factura
- [ ] Pantalla de historial médico de mascota
- [ ] Pantalla de disponibilidad del veterinario
- [ ] Visor de archivos (imágenes/PDF)

---

## 🎯 Endpoints API Disponibles

### Facturas
- `GET /api/facturas` - Listar facturas
- `POST /api/facturas` - Crear factura
- `GET /api/facturas/{id}` - Ver factura
- `PUT /api/facturas/{id}` - Actualizar factura
- `DELETE /api/facturas/{id}` - Eliminar factura
- `GET /api/facturas-estadisticas` - Estadísticas
- `GET /api/generar-numero-factura` - Generar número

### Historial Médico
- `GET /api/historial-medico` - Listar historial
- `POST /api/historial-medico` - Crear registro
- `GET /api/historial-medico/{id}` - Ver registro
- `PUT /api/historial-medico/{id}` - Actualizar registro
- `DELETE /api/historial-medico/{id}` - Eliminar registro
- `POST /api/historial-medico/{id}/archivos` - Subir archivo

### Disponibilidad
- `GET /api/veterinarios/{id}/disponibilidad` - Ver disponibilidad
- `POST /api/veterinarios/{id}/disponibilidad` - Establecer disponibilidad

---

**📚 Complementa la guía principal:** `FLUTTER_INTEGRATION_COMPLETE.md`

**🚀 ¡Ahora tienes TODOS los modelos del backend documentados!**
