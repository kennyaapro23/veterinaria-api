# 🚀 Guía Completa de Integración Flutter - VetCare API

## 📋 Índice
1. [Configuración Inicial](#configuración-inicial)
2. [Modelos de Datos](#modelos-de-datos)
3. [Servicios API](#servicios-api)
4. [Notificaciones Push (Firebase)](#notificaciones-push-firebase)
5. [Autenticación](#autenticación)
6. [Gestión de Estado](#gestión-de-estado)
7. [Validaciones y Campos Requeridos](#validaciones-y-campos-requeridos)

---

## 🔧 Configuración Inicial

### 1. AppConfig (lib/config/app_config.dart)

```dart
class AppConfig {
  // ✅ CONFIGURACIÓN CORRECTA PARA EMULADOR CON ADB REVERSE
  static const String baseUrl = 'http://127.0.0.1:8000/api/';
  
  // Timeouts
  static const Duration requestTimeout = Duration(seconds: 30);
  static const Duration connectTimeout = Duration(seconds: 10);
  static const int maxRetries = 3;

  // Headers estándar
  static Map<String, String> get headers => {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  static Map<String, String> headersWithAuth(String token) => {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'Authorization': 'Bearer $token',
  };
}
```

### 2. Dependencias requeridas (pubspec.yaml)

```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # HTTP y Estado
  http: ^1.1.0
  provider: ^6.1.1
  
  # Firebase
  firebase_core: ^2.24.2
  firebase_auth: ^4.15.3
  firebase_messaging: ^14.7.9
  google_sign_in: ^6.2.1          # ✅ NUEVO: Google Sign-In
  
  # UI y Utilidades
  intl: ^0.18.1
  flutter_local_notifications: ^16.3.0
  shared_preferences: ^2.2.2
  image_picker: ^1.0.7
  qr_flutter: ^4.1.0
  mobile_scanner: ^3.5.5
  
  # Extras
  uuid: ^4.3.3
  cached_network_image: ^3.3.1
```

### 3. Configuración Google Sign-In en Android

**android/app/build.gradle:**
```gradle
dependencies {
    // Firebase
    implementation platform('com.google.firebase:firebase-bom:32.7.0')
    implementation 'com.google.firebase:firebase-auth'
    implementation 'com.google.android.gms:play-services-auth:20.7.0'
}
```

**Obtener SHA-1 y SHA-256 para Firebase Console:**
```bash
# Windows PowerShell
cd android
.\gradlew signingReport

# Buscar en la salida:
# SHA1: XX:XX:XX:...
# SHA-256: YY:YY:YY:...
```

**⚠️ IMPORTANTE:** Agregar estos SHA en Firebase Console:
1. Ir a Firebase Console → Project Settings → Your apps → Android app
2. Click "Add fingerprint"
3. Pegar SHA-1 y SHA-256
4. Descargar nuevo `google-services.json` y reemplazarlo en `android/app/`

---

## 📦 Modelos de Datos

### Cliente Model (lib/models/cliente.dart)

```dart
class Cliente {
  final int? id;
  final int userId;
  final String nombre;
  final String telefono;
  final String email;
  final String? documentoTipo;    // 'DNI', 'RUC', 'CE', 'Pasaporte'
  final String? documentoNum;
  final String? direccion;
  final String? notas;
  final String? publicId;         // UUID para QR
  final DateTime? createdAt;
  final DateTime? updatedAt;

  Cliente({
    this.id,
    required this.userId,
    required this.nombre,
    required this.telefono,
    required this.email,
    this.documentoTipo,
    this.documentoNum,
    this.direccion,
    this.notas,
    this.publicId,
    this.createdAt,
    this.updatedAt,
  });

  // ✅ CAMPOS REQUERIDOS EN BACKEND:
  // - user_id (int, obligatorio)
  // - nombre (string, obligatorio)
  // - telefono (string, obligatorio)
  // - email (string, obligatorio, único)

  factory Cliente.fromJson(Map<String, dynamic> json) {
    return Cliente(
      id: json['id'],
      userId: json['user_id'],
      nombre: json['nombre'],
      telefono: json['telefono'],
      email: json['email'],
      documentoTipo: json['documento_tipo'],
      documentoNum: json['documento_num'],
      direccion: json['direccion'],
      notas: json['notas'],
      publicId: json['public_id'],
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at']) 
          : null,
      updatedAt: json['updated_at'] != null 
          ? DateTime.parse(json['updated_at']) 
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'user_id': userId,
      'nombre': nombre,
      'telefono': telefono,
      'email': email,
      if (documentoTipo != null) 'documento_tipo': documentoTipo,
      if (documentoNum != null) 'documento_num': documentoNum,
      if (direccion != null) 'direccion': direccion,
      if (notas != null) 'notas': notas,
    };
  }
}
```

### Mascota Model (lib/models/mascota.dart)

```dart
class Mascota {
  final int? id;
  final int clienteId;
  final String nombre;
  final String especie;           // 'Perro', 'Gato', 'Ave', 'Roedor', 'Reptil', 'Otro'
  final String? raza;
  final String sexo;              // 'macho', 'hembra'
  final DateTime? fechaNacimiento;
  final String? color;
  final String? chipId;
  final String? fotoUrl;
  final String? publicId;         // UUID para QR
  final DateTime? createdAt;
  final DateTime? updatedAt;

  Mascota({
    this.id,
    required this.clienteId,
    required this.nombre,
    required this.especie,
    this.raza,
    required this.sexo,
    this.fechaNacimiento,
    this.color,
    this.chipId,
    this.fotoUrl,
    this.publicId,
    this.createdAt,
    this.updatedAt,
  });

  // ✅ CAMPOS REQUERIDOS EN BACKEND:
  // - cliente_id (int, obligatorio)
  // - nombre (string, obligatorio)
  // - especie (enum, obligatorio)
  // - sexo (enum: 'macho', 'hembra', obligatorio)

  factory Mascota.fromJson(Map<String, dynamic> json) {
    return Mascota(
      id: json['id'],
      clienteId: json['cliente_id'],
      nombre: json['nombre'],
      especie: json['especie'],
      raza: json['raza'],
      sexo: json['sexo'],
      fechaNacimiento: json['fecha_nacimiento'] != null
          ? DateTime.parse(json['fecha_nacimiento'])
          : null,
      color: json['color'],
      chipId: json['chip_id'],
      fotoUrl: json['foto_url'],
      publicId: json['public_id'],
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.parse(json['updated_at'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'cliente_id': clienteId,
      'nombre': nombre,
      'especie': especie,
      'sexo': sexo,
      if (raza != null) 'raza': raza,
      if (fechaNacimiento != null)
        'fecha_nacimiento': fechaNacimiento!.toIso8601String().split('T')[0],
      if (color != null) 'color': color,
      if (chipId != null) 'chip_id': chipId,
      if (fotoUrl != null) 'foto_url': fotoUrl,
    };
  }

  // Helper: Calcular edad
  String get edad {
    if (fechaNacimiento == null) return 'Desconocida';
    
    final now = DateTime.now();
    int years = now.year - fechaNacimiento!.year;
    int months = now.month - fechaNacimiento!.month;
    
    if (months < 0) {
      years--;
      months += 12;
    }
    
    if (years > 0) {
      return '$years ${years == 1 ? 'año' : 'años'}';
    } else if (months > 0) {
      return '$months ${months == 1 ? 'mes' : 'meses'}';
    } else {
      return 'Menos de 1 mes';
    }
  }
}
```

### Cita Model (lib/models/cita.dart)

```dart
class Cita {
  final int? id;
  final int clienteId;
  final int mascotaId;
  final int veterinarioId;
  final int servicioId;
  final DateTime fechaHora;
  final String estado;            // 'pendiente', 'confirmada', 'cancelada', 'completada'
  final String? motivo;
  final String? notas;
  final DateTime? createdAt;
  final DateTime? updatedAt;
  
  // Relaciones (opcional)
  final Cliente? cliente;
  final Mascota? mascota;
  final Veterinario? veterinario;
  final Servicio? servicio;

  Cita({
    this.id,
    required this.clienteId,
    required this.mascotaId,
    required this.veterinarioId,
    required this.servicioId,
    required this.fechaHora,
    required this.estado,
    this.motivo,
    this.notas,
    this.createdAt,
    this.updatedAt,
    this.cliente,
    this.mascota,
    this.veterinario,
    this.servicio,
  });

  // ✅ CAMPOS REQUERIDOS EN BACKEND:
  // - cliente_id (int, obligatorio)
  // - mascota_id (int, obligatorio)
  // - veterinario_id (int, obligatorio)
  // - servicio_id (int, obligatorio)
  // - fecha_hora (datetime, obligatorio)
  // - estado (enum, obligatorio, default: 'pendiente')

  factory Cita.fromJson(Map<String, dynamic> json) {
    return Cita(
      id: json['id'],
      clienteId: json['cliente_id'],
      mascotaId: json['mascota_id'],
      veterinarioId: json['veterinario_id'],
      servicioId: json['servicio_id'],
      fechaHora: DateTime.parse(json['fecha_hora']),
      estado: json['estado'],
      motivo: json['motivo'],
      notas: json['notas'],
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.parse(json['updated_at'])
          : null,
      cliente: json['cliente'] != null
          ? Cliente.fromJson(json['cliente'])
          : null,
      mascota: json['mascota'] != null
          ? Mascota.fromJson(json['mascota'])
          : null,
      veterinario: json['veterinario'] != null
          ? Veterinario.fromJson(json['veterinario'])
          : null,
      servicio: json['servicio'] != null
          ? Servicio.fromJson(json['servicio'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'cliente_id': clienteId,
      'mascota_id': mascotaId,
      'veterinario_id': veterinarioId,
      'servicio_id': servicioId,
      'fecha_hora': fechaHora.toIso8601String(),
      'estado': estado,
      if (motivo != null) 'motivo': motivo,
      if (notas != null) 'notas': notas,
    };
  }
}
```

### Servicio Model (lib/models/servicio.dart)

```dart
class Servicio {
  final int? id;
  final String nombre;
  final String? descripcion;
  final String tipo;              // 'consulta', 'vacunacion', 'cirugia', 'peluqueria', 'hospitalizacion', 'otro'
  final double precio;
  final int duracionMinutos;
  final bool disponible;

  Servicio({
    this.id,
    required this.nombre,
    this.descripcion,
    required this.tipo,
    required this.precio,
    required this.duracionMinutos,
    this.disponible = true,
  });

  // ✅ CAMPOS REQUERIDOS EN BACKEND:
  // - nombre (string, obligatorio)
  // - tipo (enum, obligatorio)
  // - precio (decimal, obligatorio)
  // - duracion_minutos (int, obligatorio)

  factory Servicio.fromJson(Map<String, dynamic> json) {
    return Servicio(
      id: json['id'],
      nombre: json['nombre'],
      descripcion: json['descripcion'],
      tipo: json['tipo'],
      precio: double.parse(json['precio'].toString()),
      duracionMinutos: json['duracion_minutos'],
      disponible: json['disponible'] == 1 || json['disponible'] == true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'nombre': nombre,
      'tipo': tipo,
      'precio': precio,
      'duracion_minutos': duracionMinutos,
      'disponible': disponible,
      if (descripcion != null) 'descripcion': descripcion,
    };
  }
}
```

### Veterinario Model (lib/models/veterinario.dart)

```dart
class Veterinario {
  final int? id;
  final int userId;
  final String nombre;
  final String especialidad;
  final String? numeroColegiadoVet;
  final String telefono;
  final String email;
  final String? foto;
  final bool disponible;

  Veterinario({
    this.id,
    required this.userId,
    required this.nombre,
    required this.especialidad,
    this.numeroColegiadoVet,
    required this.telefono,
    required this.email,
    this.foto,
    this.disponible = true,
  });

  // ✅ CAMPOS REQUERIDOS EN BACKEND:
  // - user_id (int, obligatorio)
  // - nombre (string, obligatorio)
  // - especialidad (string, obligatorio)
  // - telefono (string, obligatorio)
  // - email (string, obligatorio)

  factory Veterinario.fromJson(Map<String, dynamic> json) {
    return Veterinario(
      id: json['id'],
      userId: json['user_id'],
      nombre: json['nombre'],
      especialidad: json['especialidad'],
      numeroColegiadoVet: json['numero_colegiado_vet'],
      telefono: json['telefono'],
      email: json['email'],
      foto: json['foto'],
      disponible: json['disponible'] == 1 || json['disponible'] == true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'user_id': userId,
      'nombre': nombre,
      'especialidad': especialidad,
      'telefono': telefono,
      'email': email,
      'disponible': disponible,
      if (numeroColegiadoVet != null) 'numero_colegiado_vet': numeroColegiadoVet,
      if (foto != null) 'foto': foto,
    };
  }
}
```

### Notificación Model (lib/models/notificacion.dart)

```dart
class Notificacion {
  final int? id;
  final int userId;
  final String tipo;              // 'cita_recordatorio', 'cita_confirmada', 'cita_cancelada', 'resultado_disponible', 'general'
  final String titulo;
  final String mensaje;
  final Map<String, dynamic>? data;
  final bool leida;
  final DateTime? leidaEn;
  final DateTime? createdAt;

  Notificacion({
    this.id,
    required this.userId,
    required this.tipo,
    required this.titulo,
    required this.mensaje,
    this.data,
    this.leida = false,
    this.leidaEn,
    this.createdAt,
  });

  // ✅ CAMPOS REQUERIDOS EN BACKEND:
  // - user_id (int, obligatorio)
  // - tipo (enum, obligatorio)
  // - titulo (string, obligatorio)
  // - mensaje (text, obligatorio)

  factory Notificacion.fromJson(Map<String, dynamic> json) {
    return Notificacion(
      id: json['id'],
      userId: json['user_id'],
      tipo: json['tipo'],
      titulo: json['titulo'],
      mensaje: json['mensaje'],
      data: json['data'] != null 
          ? Map<String, dynamic>.from(json['data'])
          : null,
      leida: json['leida'] == 1 || json['leida'] == true,
      leidaEn: json['leida_en'] != null
          ? DateTime.parse(json['leida_en'])
          : null,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : null,
    );
  }
}
```

---

## 🔐 Autenticación

### Auth Service (lib/services/auth_service.dart)

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/app_config.dart';
import '../models/user.dart';

class AuthService {
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user_data';

  // Login tradicional
  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('${AppConfig.baseUrl}auth/login'),
        headers: AppConfig.headers,
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      ).timeout(AppConfig.requestTimeout);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        // Guardar token y usuario
        await _saveToken(data['token']);
        await _saveUser(data['user']);

        return {
          'success': true,
          'user': User.fromJson(data['user']),
          'token': data['token'],
        };
      } else {
        final error = jsonDecode(response.body);
        return {
          'success': false,
          'message': error['message'] ?? 'Error de autenticación',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Error de conexión: ${e.toString()}',
      };
    }
  }

  // Register
  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String role,  // 'cliente', 'veterinario', 'recepcion'
  }) async {
    try {
      final response = await http.post(
        Uri.parse('${AppConfig.baseUrl}auth/register'),
        headers: AppConfig.headers,
        body: jsonEncode({
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
          'role': role,
        }),
      ).timeout(AppConfig.requestTimeout);

      if (response.statusCode == 201) {
        final data = jsonDecode(response.body);
        
        await _saveToken(data['token']);
        await _saveUser(data['user']);

        return {
          'success': true,
          'user': User.fromJson(data['user']),
          'token': data['token'],
        };
      } else {
        final error = jsonDecode(response.body);
        return {
          'success': false,
          'message': error['message'] ?? 'Error en el registro',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Error de conexión: ${e.toString()}',
      };
    }
  }

  // Logout
  Future<void> logout() async {
    final token = await getToken();
    if (token != null) {
      try {
        await http.post(
          Uri.parse('${AppConfig.baseUrl}auth/logout'),
          headers: AppConfig.headersWithAuth(token),
        ).timeout(AppConfig.requestTimeout);
      } catch (e) {
        print('Error al cerrar sesión en el servidor: $e');
      }
    }

    await _clearToken();
    await _clearUser();
  }

  // Obtener token guardado
  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }

  // Obtener usuario guardado
  Future<User?> getUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userData = prefs.getString(_userKey);
    if (userData != null) {
      return User.fromJson(jsonDecode(userData));
    }
    return null;
  }

  // Verificar si está autenticado
  Future<bool> isAuthenticated() async {
    final token = await getToken();
    return token != null;
  }

  // ✅ NUEVO: Login con Google (Firebase)
  Future<Map<String, dynamic>> loginWithGoogle() async {
    try {
      // 1. Iniciar Google Sign-In
      final GoogleSignIn googleSignIn = GoogleSignIn(
        scopes: ['email', 'profile'],
      );

      // 2. Seleccionar cuenta de Google
      final GoogleSignInAccount? googleUser = await googleSignIn.signIn();
      
      if (googleUser == null) {
        return {
          'success': false,
          'message': 'Inicio de sesión cancelado',
        };
      }

      // 3. Obtener detalles de autenticación
      final GoogleSignInAuthentication googleAuth = await googleUser.authentication;

      // 4. Crear credencial de Firebase
      final credential = GoogleAuthProvider.credential(
        accessToken: googleAuth.accessToken,
        idToken: googleAuth.idToken,
      );

      // 5. Sign-in con Firebase
      final UserCredential userCredential = 
          await FirebaseAuth.instance.signInWithCredential(credential);

      // 6. Obtener token de Firebase
      final firebaseToken = await userCredential.user?.getIdToken();

      if (firebaseToken == null) {
        return {
          'success': false,
          'message': 'Error al obtener token de Firebase',
        };
      }

      // 7. Verificar/registrar en backend Laravel
      final response = await http.post(
        Uri.parse('${AppConfig.baseUrl}firebase/verify'),
        headers: AppConfig.headers,
        body: jsonEncode({
          'firebase_token': firebaseToken,
          'email': userCredential.user?.email,
          'name': userCredential.user?.displayName,
          'photo_url': userCredential.user?.photoURL,
          'provider': 'google',
        }),
      ).timeout(AppConfig.requestTimeout);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        // Guardar token Laravel y usuario
        await _saveToken(data['token']);
        await _saveUser(data['user']);

        return {
          'success': true,
          'user': User.fromJson(data['user']),
          'token': data['token'],
          'firebaseToken': firebaseToken,
        };
      } else {
        final error = jsonDecode(response.body);
        return {
          'success': false,
          'message': error['message'] ?? 'Error al verificar con el backend',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Error en Google Sign-In: ${e.toString()}',
      };
    }
  }

  // ✅ NUEVO: Logout con Google
  Future<void> logoutWithGoogle() async {
    try {
      // 1. Logout del backend
      await logout();

      // 2. Logout de Firebase
      await FirebaseAuth.instance.signOut();

      // 3. Logout de Google
      final GoogleSignIn googleSignIn = GoogleSignIn();
      await googleSignIn.signOut();
    } catch (e) {
      print('Error en logout de Google: $e');
    }
  }

  // Private methods
  Future<void> _saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
  }

  Future<void> _saveUser(Map<String, dynamic> user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_userKey, jsonEncode(user));
  }

  Future<void> _clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
  }

  Future<void> _clearUser() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_userKey);
  }
}
```

---

## 🔔 Notificaciones Push (Firebase)

### Firebase Service (lib/services/firebase_service.dart)

```dart
import 'dart:convert';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:http/http.dart' as http;
import '../config/app_config.dart';
import 'auth_service.dart';

class FirebaseService {
  final FirebaseMessaging _firebaseMessaging = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();
  final AuthService _authService = AuthService();

  // Inicializar Firebase Messaging
  Future<void> initialize() async {
    // 1. Solicitar permisos
    NotificationSettings settings = await _firebaseMessaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
      provisional: false,
    );

    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      print('✅ Permisos de notificaciones concedidos');

      // 2. Obtener token FCM
      String? token = await _firebaseMessaging.getToken();
      if (token != null) {
        print('🔑 FCM Token: $token');
        await _sendTokenToServer(token);
      }

      // 3. Escuchar cambios de token
      _firebaseMessaging.onTokenRefresh.listen(_sendTokenToServer);

      // 4. Configurar notificaciones locales
      await _initLocalNotifications();

      // 5. Handlers de notificaciones
      _setupNotificationHandlers();
    } else {
      print('⚠️ Permisos de notificaciones denegados');
    }
  }

  // Configurar notificaciones locales
  Future<void> _initLocalNotifications() async {
    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const iosSettings = DarwinInitializationSettings();
    
    const initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: _onNotificationTapped,
    );

    // Canal de Android
    const channel = AndroidNotificationChannel(
      'high_importance_channel',
      'Notificaciones VetCare',
      description: 'Canal para notificaciones importantes',
      importance: Importance.high,
    );

    await _localNotifications
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(channel);
  }

  // Enviar token al servidor Laravel
  Future<void> _sendTokenToServer(String token) async {
    try {
      final authToken = await _authService.getToken();
      if (authToken == null) return;

      final response = await http.post(
        Uri.parse('${AppConfig.baseUrl}fcm-token'),
        headers: AppConfig.headersWithAuth(authToken),
        body: jsonEncode({
          'token': token,
          'device_type': 'android',  // o 'ios'
        }),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        print('✅ Token FCM registrado en el servidor');
      } else {
        print('❌ Error al registrar token: ${response.body}');
      }
    } catch (e) {
      print('❌ Error enviando token al servidor: $e');
    }
  }

  // Setup handlers
  void _setupNotificationHandlers() {
    // App en foreground
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print('📬 Notificación recibida (foreground): ${message.notification?.title}');
      _showLocalNotification(message);
    });

    // App en background (tap en notificación)
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      print('📬 Notificación abierta (background): ${message.data}');
      _handleNotificationNavigation(message.data);
    });

    // App cerrada (tap en notificación)
    _firebaseMessaging.getInitialMessage().then((RemoteMessage? message) {
      if (message != null) {
        print('📬 Notificación inicial (app cerrada): ${message.data}');
        _handleNotificationNavigation(message.data);
      }
    });
  }

  // Mostrar notificación local
  Future<void> _showLocalNotification(RemoteMessage message) async {
    const androidDetails = AndroidNotificationDetails(
      'high_importance_channel',
      'Notificaciones VetCare',
      channelDescription: 'Canal para notificaciones importantes',
      importance: Importance.high,
      priority: Priority.high,
      icon: '@mipmap/ic_launcher',
    );

    const iosDetails = DarwinNotificationDetails();

    const details = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );

    await _localNotifications.show(
      message.hashCode,
      message.notification?.title ?? 'VetCare',
      message.notification?.body ?? '',
      details,
      payload: jsonEncode(message.data),
    );
  }

  // Manejar tap en notificación
  void _onNotificationTapped(NotificationResponse response) {
    if (response.payload != null) {
      final data = jsonDecode(response.payload!);
      _handleNotificationNavigation(data);
    }
  }

  // Navegar según tipo de notificación
  void _handleNotificationNavigation(Map<String, dynamic> data) {
    final tipo = data['tipo'] as String?;
    final id = data['id'] as String?;

    // Implementa tu lógica de navegación aquí
    switch (tipo) {
      case 'cita_recordatorio':
      case 'cita_confirmada':
        // Navegar a detalles de cita
        print('Navegar a cita: $id');
        break;
      case 'resultado_disponible':
        // Navegar a historial médico
        print('Navegar a historial médico');
        break;
      default:
        print('Navegar a notificaciones');
    }
  }

  // Eliminar token al cerrar sesión
  Future<void> deleteToken() async {
    try {
      final token = await _firebaseMessaging.getToken();
      if (token != null) {
        final authToken = await _authService.getToken();
        if (authToken != null) {
          await http.delete(
            Uri.parse('${AppConfig.baseUrl}fcm-token'),
            headers: AppConfig.headersWithAuth(authToken),
          );
        }
        await _firebaseMessaging.deleteToken();
        print('✅ Token FCM eliminado');
      }
    } catch (e) {
      print('❌ Error eliminando token: $e');
    }
  }
}
```

### Background Handler (lib/main.dart)

```dart
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'firebase_options.dart';

// ⚠️ DEBE estar fuera de cualquier clase, en el nivel superior
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print('📬 Notificación en background: ${message.notification?.title}');
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Inicializar Firebase
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );

  // Registrar handler de background
  FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

  runApp(MyApp());
}
```

---

## 🌐 Servicios API

### API Service Base (lib/services/api_service.dart)

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/app_config.dart';
import 'auth_service.dart';

class ApiService {
  final AuthService _authService = AuthService();

  // GET request
  Future<Map<String, dynamic>> get(String endpoint) async {
    try {
      final token = await _authService.getToken();
      final headers = token != null 
          ? AppConfig.headersWithAuth(token)
          : AppConfig.headers;

      final response = await http.get(
        Uri.parse('${AppConfig.baseUrl}$endpoint'),
        headers: headers,
      ).timeout(AppConfig.requestTimeout);

      return _handleResponse(response);
    } catch (e) {
      return {'success': false, 'message': 'Error: ${e.toString()}'};
    }
  }

  // POST request
  Future<Map<String, dynamic>> post(
    String endpoint, 
    Map<String, dynamic> body,
  ) async {
    try {
      final token = await _authService.getToken();
      final headers = token != null 
          ? AppConfig.headersWithAuth(token)
          : AppConfig.headers;

      final response = await http.post(
        Uri.parse('${AppConfig.baseUrl}$endpoint'),
        headers: headers,
        body: jsonEncode(body),
      ).timeout(AppConfig.requestTimeout);

      return _handleResponse(response);
    } catch (e) {
      return {'success': false, 'message': 'Error: ${e.toString()}'};
    }
  }

  // PUT request
  Future<Map<String, dynamic>> put(
    String endpoint, 
    Map<String, dynamic> body,
  ) async {
    try {
      final token = await _authService.getToken();
      final headers = token != null 
          ? AppConfig.headersWithAuth(token)
          : AppConfig.headers;

      final response = await http.put(
        Uri.parse('${AppConfig.baseUrl}$endpoint'),
        headers: headers,
        body: jsonEncode(body),
      ).timeout(AppConfig.requestTimeout);

      return _handleResponse(response);
    } catch (e) {
      return {'success': false, 'message': 'Error: ${e.toString()}'};
    }
  }

  // DELETE request
  Future<Map<String, dynamic>> delete(String endpoint) async {
    try {
      final token = await _authService.getToken();
      final headers = token != null 
          ? AppConfig.headersWithAuth(token)
          : AppConfig.headers;

      final response = await http.delete(
        Uri.parse('${AppConfig.baseUrl}$endpoint'),
        headers: headers,
      ).timeout(AppConfig.requestTimeout);

      return _handleResponse(response);
    } catch (e) {
      return {'success': false, 'message': 'Error: ${e.toString()}'};
    }
  }

  // Manejar respuesta
  Map<String, dynamic> _handleResponse(http.Response response) {
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return {
        'success': true,
        'data': jsonDecode(response.body),
        'statusCode': response.statusCode,
      };
    } else {
      final error = jsonDecode(response.body);
      return {
        'success': false,
        'message': error['message'] ?? 'Error en la petición',
        'statusCode': response.statusCode,
      };
    }
  }
}
```

---

## ✅ Validaciones y Campos Requeridos

### Form Validators (lib/utils/validators.dart)

```dart
class Validators {
  // Email
  static String? email(String? value) {
    if (value == null || value.isEmpty) {
      return 'El email es requerido';
    }
    final emailRegex = RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$');
    if (!emailRegex.hasMatch(value)) {
      return 'Email inválido';
    }
    return null;
  }

  // Password
  static String? password(String? value) {
    if (value == null || value.isEmpty) {
      return 'La contraseña es requerida';
    }
    if (value.length < 6) {
      return 'Mínimo 6 caracteres';
    }
    return null;
  }

  // Required field
  static String? required(String? value, String fieldName) {
    if (value == null || value.isEmpty) {
      return '$fieldName es requerido';
    }
    return null;
  }

  // Phone
  static String? phone(String? value) {
    if (value == null || value.isEmpty) {
      return 'El teléfono es requerido';
    }
    if (value.length < 9) {
      return 'Teléfono inválido';
    }
    return null;
  }

  // DNI/RUC
  static String? documento(String? value, String tipo) {
    if (value == null || value.isEmpty) {
      return 'El documento es requerido';
    }
    
    if (tipo == 'DNI' && value.length != 8) {
      return 'DNI debe tener 8 dígitos';
    }
    
    if (tipo == 'RUC' && value.length != 11) {
      return 'RUC debe tener 11 dígitos';
    }
    
    return null;
  }

  // Future date (para citas)
  static String? futureDate(DateTime? value) {
    if (value == null) {
      return 'La fecha es requerida';
    }
    if (value.isBefore(DateTime.now())) {
      return 'La fecha debe ser futura';
    }
    return null;
  }

  // Past date (para fecha de nacimiento)
  static String? pastDate(DateTime? value) {
    if (value == null) {
      return 'La fecha es requerida';
    }
    if (value.isAfter(DateTime.now())) {
      return 'La fecha debe ser pasada';
    }
    return null;
  }
}
```

---

## 📱 Ejemplo de Uso Completo

### Pantalla de Login con Google Sign-In (lib/screens/login_screen.dart)

```dart
import 'package:flutter/material.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:firebase_auth/firebase_auth.dart';
import '../services/auth_service.dart';
import '../services/firebase_service.dart';
import '../utils/validators.dart';

class LoginScreen extends StatefulWidget {
  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _authService = AuthService();
  final _firebaseService = FirebaseService();
  
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  
  bool _isLoading = false;
  bool _isGoogleLoading = false;

  // Login tradicional (email/password)
  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final result = await _authService.login(
        _emailController.text,
        _passwordController.text,
      );

      if (result['success']) {
        // Inicializar Firebase después del login
        await _firebaseService.initialize();

        // Navegar a home
        Navigator.pushReplacementNamed(context, '/home');
      } else {
        _showError(result['message']);
      }
    } finally {
      setState(() => _isLoading = false);
    }
  }

  // ✅ NUEVO: Login con Google
  Future<void> _loginWithGoogle() async {
    setState(() => _isGoogleLoading = true);

    try {
      final result = await _authService.loginWithGoogle();

      if (result['success']) {
        // Inicializar Firebase después del login
        await _firebaseService.initialize();

        // Navegar a home
        Navigator.pushReplacementNamed(context, '/home');
      } else {
        _showError(result['message']);
      }
    } finally {
      setState(() => _isGoogleLoading = false);
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Iniciar Sesión'),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Logo
              SizedBox(height: 40),
              Icon(
                Icons.pets,
                size: 80,
                color: Theme.of(context).primaryColor,
              ),
              SizedBox(height: 16),
              Text(
                'VetCare',
                style: TextStyle(
                  fontSize: 32,
                  fontWeight: FontWeight.bold,
                ),
              ),
              SizedBox(height: 48),

              // Login tradicional
              TextFormField(
                controller: _emailController,
                decoration: InputDecoration(
                  labelText: 'Email',
                  prefixIcon: Icon(Icons.email),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                keyboardType: TextInputType.emailAddress,
                validator: Validators.email,
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _passwordController,
                decoration: InputDecoration(
                  labelText: 'Contraseña',
                  prefixIcon: Icon(Icons.lock),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                obscureText: true,
                validator: Validators.password,
              ),
              SizedBox(height: 24),

              // Botón login tradicional
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _login,
                  style: ElevatedButton.styleFrom(
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: _isLoading
                      ? CircularProgressIndicator(color: Colors.white)
                      : Text(
                          'Iniciar Sesión',
                          style: TextStyle(fontSize: 16),
                        ),
                ),
              ),

              SizedBox(height: 24),

              // Divider
              Row(
                children: [
                  Expanded(child: Divider()),
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16),
                    child: Text(
                      'O',
                      style: TextStyle(color: Colors.grey),
                    ),
                  ),
                  Expanded(child: Divider()),
                ],
              ),

              SizedBox(height: 24),

              // ✅ Botón Google Sign-In
              SizedBox(
                width: double.infinity,
                height: 50,
                child: OutlinedButton.icon(
                  onPressed: _isGoogleLoading ? null : _loginWithGoogle,
                  icon: _isGoogleLoading
                      ? SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : Image.network(
                          'https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg',
                          height: 24,
                        ),
                  label: Text(
                    'Continuar con Google',
                    style: TextStyle(fontSize: 16),
                  ),
                  style: OutlinedButton.styleFrom(
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    side: BorderSide(color: Colors.grey),
                  ),
                ),
              ),

              SizedBox(height: 24),

              // Link a registro
              TextButton(
                onPressed: () {
                  Navigator.pushNamed(context, '/register');
                },
                child: Text('¿No tienes cuenta? Regístrate'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }
}
```

---

## 🎯 Checklist de Implementación

### ✅ Configuración Inicial
- [ ] Cambiar `baseUrl` a `http://127.0.0.1:8000/api/`
- [ ] Ejecutar `adb reverse tcp:8000 tcp:8000` (emulador)
- [ ] Agregar dependencias en `pubspec.yaml`
- [ ] Configurar Firebase (`firebase_options.dart`)
- [ ] **Obtener SHA-1 y SHA-256** (`cd android && .\gradlew signingReport`)
- [ ] **Agregar SHA en Firebase Console** → Project Settings → Add fingerprint
- [ ] **Descargar nuevo google-services.json** y reemplazar en `android/app/`

### ✅ Autenticación
- [ ] Implementar `AuthService`
- [ ] Crear pantallas de login/register
- [ ] Guardar token en SharedPreferences
- [ ] Implementar logout

### ✅ Notificaciones
- [ ] Inicializar Firebase en `main.dart`
- [ ] Implementar `FirebaseService`
- [ ] Configurar background handler
- [ ] Solicitar permisos de notificaciones
- [ ] Enviar token FCM al backend

### ✅ Modelos y Servicios
- [ ] Crear todos los modelos de datos
- [ ] Implementar `ApiService` base
- [ ] Crear servicios específicos (Citas, Mascotas, etc.)
- [ ] Validar campos requeridos

### ✅ UI/UX
- [ ] Pantallas de CRUD para cada entidad
- [ ] Manejo de errores
- [ ] Estados de carga
- [ ] Navegación completa

---

## 🚨 Errores Comunes y Soluciones

### 1. SocketException: Connection failed
**Solución:** Ejecutar `adb reverse tcp:8000 tcp:8000`

### 2. Firebase network-request-failed
**Solución:** Verificar que `google-services.json` (Android) o `GoogleService-Info.plist` (iOS) estén configurados

### 3. 401 Unauthorized
**Solución:** Token expirado o inválido, hacer logout/login nuevamente

### 4. 422 Validation Error
**Solución:** Revisar que todos los campos requeridos estén presentes y con el formato correcto

### 5. Notificaciones no llegan
**Solución:** 
- Verificar permisos de notificaciones
- Verificar que el token FCM se haya enviado al backend
- Revisar la tabla `fcm_tokens` en la base de datos

---

## 📞 Soporte

Si tienes problemas, verifica:
1. ✅ Servidor Laravel corriendo: `php artisan serve --host=0.0.0.0 --port=8000`
2. ✅ ADB reverse activo: `adb reverse tcp:8000 tcp:8000`
3. ✅ Base de datos MySQL corriendo
4. ✅ Firebase configurado correctamente
5. ✅ Permisos de internet en `AndroidManifest.xml`

---

**¡Listo! Ahora tienes todo para integrar tu Flutter con el backend VetCare de forma completa. 🚀**
