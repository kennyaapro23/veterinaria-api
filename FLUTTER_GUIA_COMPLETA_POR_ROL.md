# 📱 Guía Completa Flutter por Rol - VetCare App

## 📑 **Índice**

1. [Estructura del Proyecto Flutter](#estructura-del-proyecto)
2. [Configuración Inicial](#configuración-inicial)
3. [Modelos y Servicios Compartidos](#modelos-y-servicios)
4. [ROL: Cliente](#rol-cliente)
5. [ROL: Veterinario](#rol-veterinario)
6. [ROL: Recepcionista](#rol-recepcionista)
7. [ROL: Administrador](#rol-administrador)
8. [Navegación y Rutas](#navegación-y-rutas)
9. [Widgets Reutilizables](#widgets-reutilizables)

---

## 📂 **Estructura del Proyecto Flutter** {#estructura-del-proyecto}

```
lib/
├── main.dart
├── config/
│   ├── app_config.dart
│   ├── routes.dart
│   └── theme.dart
├── models/
│   ├── user.dart
│   ├── mascota.dart
│   ├── cita.dart
│   ├── cliente.dart
│   ├── veterinario.dart
│   ├── servicio.dart
│   ├── historial_medico.dart
│   ├── factura.dart
│   ├── notificacion.dart
│   └── agenda_disponibilidad.dart
├── services/
│   ├── auth_service.dart
│   ├── api_service.dart
│   ├── mascota_service.dart
│   ├── cita_service.dart
│   ├── cliente_service.dart
│   ├── veterinario_service.dart
│   ├── historial_service.dart
│   ├── factura_service.dart
│   ├── qr_service.dart
│   ├── notificacion_service.dart
│   └── firebase_service.dart
├── screens/
│   ├── auth/
│   │   ├── login_screen.dart
│   │   ├── register_screen.dart
│   │   └── splash_screen.dart
│   ├── cliente/
│   │   ├── cliente_home_screen.dart
│   │   ├── mascotas/
│   │   │   ├── mis_mascotas_screen.dart
│   │   │   ├── mascota_detail_screen.dart
│   │   │   ├── mascota_form_screen.dart
│   │   │   └── mascota_qr_screen.dart
│   │   ├── citas/
│   │   │   ├── mis_citas_screen.dart
│   │   │   ├── agendar_cita_screen.dart
│   │   │   └── cita_detail_screen.dart
│   │   ├── facturas/
│   │   │   ├── mis_facturas_screen.dart
│   │   │   └── factura_detail_screen.dart
│   │   └── perfil/
│   │       └── cliente_perfil_screen.dart
│   ├── veterinario/
│   │   ├── veterinario_home_screen.dart
│   │   ├── agenda/
│   │   │   ├── mis_citas_vet_screen.dart
│   │   │   └── disponibilidad_screen.dart
│   │   ├── pacientes/
│   │   │   ├── pacientes_screen.dart
│   │   │   ├── paciente_detail_screen.dart
│   │   │   └── qr_scanner_screen.dart
│   │   ├── historial/
│   │   │   ├── registrar_consulta_screen.dart
│   │   │   └── historial_detail_screen.dart
│   │   └── perfil/
│   │       └── vet_perfil_screen.dart
│   ├── recepcion/
│   │   ├── recepcion_home_screen.dart
│   │   ├── clientes/
│   │   │   ├── clientes_screen.dart
│   │   │   ├── cliente_form_screen.dart
│   │   │   └── cliente_detail_screen.dart
│   │   ├── mascotas/
│   │   │   ├── mascotas_screen.dart
│   │   │   └── mascota_form_screen.dart
│   │   ├── citas/
│   │   │   ├── calendario_citas_screen.dart
│   │   │   ├── agendar_cita_recep_screen.dart
│   │   │   └── gestionar_cita_screen.dart
│   │   └── facturas/
│   │       ├── facturas_screen.dart
│   │       └── crear_factura_screen.dart
│   ├── admin/
│   │   ├── admin_home_screen.dart
│   │   ├── usuarios/
│   │   │   ├── usuarios_screen.dart
│   │   │   └── usuario_form_screen.dart
│   │   ├── veterinarios/
│   │   │   ├── veterinarios_screen.dart
│   │   │   └── veterinario_form_screen.dart
│   │   ├── servicios/
│   │   │   ├── servicios_screen.dart
│   │   │   └── servicio_form_screen.dart
│   │   ├── reportes/
│   │   │   └── reportes_screen.dart
│   │   └── configuracion/
│   │       └── configuracion_screen.dart
│   └── shared/
│       ├── notificaciones_screen.dart
│       └── qr_scanner_universal_screen.dart
└── widgets/
    ├── custom_app_bar.dart
    ├── custom_drawer.dart
    ├── mascota_card.dart
    ├── cita_card.dart
    ├── loading_widget.dart
    ├── error_widget.dart
    ├── empty_state_widget.dart
    ├── custom_button.dart
    └── custom_text_field.dart
```

---

## ⚙️ **Configuración Inicial** {#configuración-inicial}

### **1. pubspec.yaml**

```yaml
name: vetcare_app
description: Sistema de gestión de clínica veterinaria
version: 1.0.0+1

environment:
  sdk: '>=3.0.0 <4.0.0'

dependencies:
  flutter:
    sdk: flutter
    
  # UI & Navigation
  cupertino_icons: ^1.0.6
  
  # State Management
  provider: ^6.1.1
  
  # HTTP & API
  http: ^1.1.0
  
  # Local Storage
  shared_preferences: ^2.2.2
  
  # Firebase
  firebase_core: ^2.24.2
  firebase_auth: ^4.15.3
  firebase_messaging: ^14.7.9
  google_sign_in: ^6.2.1
  
  # QR Code
  qr_flutter: ^4.1.0
  qr_code_scanner: ^1.0.1
  
  # Images & Files
  image_picker: ^1.0.7
  cached_network_image: ^3.3.1
  
  # Date & Time
  intl: ^0.19.0
  table_calendar: ^3.0.9
  
  # Charts & Graphics
  fl_chart: ^0.66.0
  
  # Utilities
  uuid: ^4.3.3
  path_provider: ^2.1.2
  
dev_dependencies:
  flutter_test:
    sdk: flutter
  flutter_launcher_icons: ^0.13.1
  flutter_launcher_name: ^0.0.1

flutter:
  uses-material-design: true
  
  assets:
    - assets/images/
    - assets/icons/
```

### **2. config/app_config.dart**

```dart
class AppConfig {
  // API Configuration
  static const String baseUrl = 'http://10.0.2.2:8000/api/'; // Android Emulator
  // static const String baseUrl = 'http://127.0.0.1:8000/api/'; // iOS Simulator
  
  static const Duration requestTimeout = Duration(seconds: 30);
  
  static Map<String, String> get headers => {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };
  
  static Map<String, String> authHeaders(String token) => {
    ...headers,
    'Authorization': 'Bearer $token',
  };
  
  // App Info
  static const String appName = 'VetCare';
  static const String appVersion = '1.0.0';
  
  // Roles
  static const String roleCliente = 'cliente';
  static const String roleVeterinario = 'veterinario';
  static const String roleRecepcion = 'recepcion';
  static const String roleAdmin = 'admin';
}
```

### **3. config/theme.dart**

```dart
import 'package:flutter/material.dart';

class AppTheme {
  // Colors
  static const Color primaryColor = Color(0xFF00BCD4); // Cyan
  static const Color secondaryColor = Color(0xFF4CAF50); // Green
  static const Color accentColor = Color(0xFFFF9800); // Orange
  static const Color errorColor = Color(0xFFF44336); // Red
  static const Color successColor = Color(0xFF4CAF50); // Green
  static const Color warningColor = Color(0xFFFFC107); // Amber
  
  // Text Colors
  static const Color textPrimary = Color(0xFF212121);
  static const Color textSecondary = Color(0xFF757575);
  static const Color textHint = Color(0xFFBDBDBD);
  
  // Background Colors
  static const Color backgroundColor = Color(0xFFF5F5F5);
  static const Color cardColor = Colors.white;
  
  // Theme Data
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: primaryColor,
        primary: primaryColor,
        secondary: secondaryColor,
        error: errorColor,
      ),
      scaffoldBackgroundColor: backgroundColor,
      cardTheme: CardTheme(
        color: cardColor,
        elevation: 2,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
      ),
      appBarTheme: AppBarTheme(
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primaryColor,
          foregroundColor: Colors.white,
          padding: EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: primaryColor, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: errorColor),
        ),
      ),
    );
  }
}
```

---

## 🎯 **ROL: CLIENTE** {#rol-cliente}

### **🏠 1. Dashboard Cliente - `cliente_home_screen.dart`**

```dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class ClienteHomeScreen extends StatefulWidget {
  @override
  _ClienteHomeScreenState createState() => _ClienteHomeScreenState();
}

class _ClienteHomeScreenState extends State<ClienteHomeScreen> {
  final MascotaService _mascotaService = MascotaService();
  final CitaService _citaService = CitaService();
  final NotificacionService _notificacionService = NotificacionService();
  
  List<Mascota> _mascotas = [];
  List<Cita> _proximasCitas = [];
  int _notificacionesPendientes = 0;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final user = await AuthService().getUser();
      
      // Cargar mascotas
      _mascotas = await _mascotaService.getMascotasByCliente(user.id);
      
      // Cargar próximas citas
      _proximasCitas = await _citaService.getProximasCitas(user.id);
      
      // Cargar notificaciones pendientes
      _notificacionesPendientes = await _notificacionService.getUnreadCount();
      
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error al cargar datos: ${e.toString()}')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Mi Dashboard'),
        actions: [
          // Badge de notificaciones
          Stack(
            children: [
              IconButton(
                icon: Icon(Icons.notifications),
                onPressed: () => Navigator.pushNamed(context, '/notificaciones'),
              ),
              if (_notificacionesPendientes > 0)
                Positioned(
                  right: 8,
                  top: 8,
                  child: Container(
                    padding: EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: Colors.red,
                      shape: BoxShape.circle,
                    ),
                    child: Text(
                      '$_notificacionesPendientes',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
      drawer: ClienteDrawer(),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: SingleChildScrollView(
                physics: AlwaysScrollableScrollPhysics(),
                padding: EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header de bienvenida
                    _buildWelcomeHeader(),
                    SizedBox(height: 24),
                    
                    // Botones de acceso rápido
                    _buildQuickActions(),
                    SizedBox(height: 24),
                    
                    // Sección de Mis Mascotas
                    _buildMascotasSection(),
                    SizedBox(height: 24),
                    
                    // Sección de Próximas Citas
                    _buildProximasCitasSection(),
                  ],
                ),
              ),
            ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => Navigator.pushNamed(context, '/cliente/agendar-cita'),
        icon: Icon(Icons.add),
        label: Text('Agendar Cita'),
      ),
    );
  }

  Widget _buildWelcomeHeader() {
    return FutureBuilder<User>(
      future: AuthService().getUser(),
      builder: (context, snapshot) {
        if (!snapshot.hasData) return SizedBox();
        final user = snapshot.data!;
        return Card(
          child: Padding(
            padding: EdgeInsets.all(16),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 30,
                  backgroundImage: user.fotoUrl != null
                      ? NetworkImage(user.fotoUrl!)
                      : null,
                  child: user.fotoUrl == null
                      ? Icon(Icons.person, size: 30)
                      : null,
                ),
                SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '¡Hola, ${user.name}!',
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Text(
                        'Bienvenido a VetCare',
                        style: TextStyle(
                          color: Colors.grey,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildQuickActions() {
    return Row(
      children: [
        Expanded(
          child: _buildActionCard(
            icon: Icons.pets,
            title: 'Mis Mascotas',
            color: Colors.blue,
            onTap: () => Navigator.pushNamed(context, '/cliente/mascotas'),
          ),
        ),
        SizedBox(width: 12),
        Expanded(
          child: _buildActionCard(
            icon: Icons.calendar_today,
            title: 'Mis Citas',
            color: Colors.green,
            onTap: () => Navigator.pushNamed(context, '/cliente/citas'),
          ),
        ),
        SizedBox(width: 12),
        Expanded(
          child: _buildActionCard(
            icon: Icons.receipt,
            title: 'Facturas',
            color: Colors.orange,
            onTap: () => Navigator.pushNamed(context, '/cliente/facturas'),
          ),
        ),
      ],
    );
  }

  Widget _buildActionCard({
    required IconData icon,
    required String title,
    required Color color,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      child: Card(
        child: Padding(
          padding: EdgeInsets.all(16),
          child: Column(
            children: [
              Icon(icon, size: 32, color: color),
              SizedBox(height: 8),
              Text(
                title,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMascotasSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Mis Mascotas (${_mascotas.length})',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            TextButton(
              onPressed: () => Navigator.pushNamed(context, '/cliente/mascotas'),
              child: Text('Ver todas'),
            ),
          ],
        ),
        SizedBox(height: 12),
        if (_mascotas.isEmpty)
          EmptyStateWidget(
            icon: Icons.pets,
            message: 'No tienes mascotas registradas',
            actionText: 'Registrar Mascota',
            onAction: () => Navigator.pushNamed(context, '/cliente/mascotas/nueva'),
          )
        else
          SizedBox(
            height: 180,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: _mascotas.length,
              itemBuilder: (context, index) {
                final mascota = _mascotas[index];
                return Padding(
                  padding: EdgeInsets.only(right: 12),
                  child: MascotaCard(
                    mascota: mascota,
                    onTap: () => Navigator.pushNamed(
                      context,
                      '/cliente/mascotas/detalle',
                      arguments: mascota,
                    ),
                  ),
                );
              },
            ),
          ),
      ],
    );
  }

  Widget _buildProximasCitasSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Próximas Citas',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            TextButton(
              onPressed: () => Navigator.pushNamed(context, '/cliente/citas'),
              child: Text('Ver todas'),
            ),
          ],
        ),
        SizedBox(height: 12),
        if (_proximasCitas.isEmpty)
          EmptyStateWidget(
            icon: Icons.event_available,
            message: 'No tienes citas próximas',
            actionText: 'Agendar Cita',
            onAction: () => Navigator.pushNamed(context, '/cliente/agendar-cita'),
          )
        else
          ..._proximasCitas.map((cita) => CitaCard(
            cita: cita,
            onTap: () => Navigator.pushNamed(
              context,
              '/cliente/citas/detalle',
              arguments: cita,
            ),
          )).toList(),
      ],
    );
  }
}
```

### **🐾 2. Mis Mascotas - `mis_mascotas_screen.dart`**

```dart
class MisMascotasScreen extends StatefulWidget {
  @override
  _MisMascotasScreenState createState() => _MisMascotasScreenState();
}

class _MisMascotasScreenState extends State<MisMascotasScreen> {
  final MascotaService _service = MascotaService();
  List<Mascota> _mascotas = [];
  List<Mascota> _mascotasFiltradas = [];
  bool _isLoading = true;
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _loadMascotas();
  }

  Future<void> _loadMascotas() async {
    setState(() => _isLoading = true);
    try {
      final user = await AuthService().getUser();
      _mascotas = await _service.getMascotasByCliente(user.id);
      _mascotasFiltradas = _mascotas;
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: ${e.toString()}')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _filterMascotas(String query) {
    setState(() {
      _searchQuery = query;
      if (query.isEmpty) {
        _mascotasFiltradas = _mascotas;
      } else {
        _mascotasFiltradas = _mascotas.where((mascota) {
          return mascota.nombre.toLowerCase().contains(query.toLowerCase()) ||
                 mascota.especie.toLowerCase().contains(query.toLowerCase()) ||
                 mascota.raza.toLowerCase().contains(query.toLowerCase());
        }).toList();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Mis Mascotas'),
        actions: [
          IconButton(
            icon: Icon(Icons.qr_code_scanner),
            onPressed: () => Navigator.pushNamed(context, '/qr-scanner'),
            tooltip: 'Escanear QR',
          ),
        ],
      ),
      body: Column(
        children: [
          // Barra de búsqueda
          Padding(
            padding: EdgeInsets.all(16),
            child: TextField(
              decoration: InputDecoration(
                hintText: 'Buscar mascota...',
                prefixIcon: Icon(Icons.search),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: Icon(Icons.clear),
                        onPressed: () {
                          _filterMascotas('');
                        },
                      )
                    : null,
              ),
              onChanged: _filterMascotas,
            ),
          ),
          
          // Lista de mascotas
          Expanded(
            child: _isLoading
                ? Center(child: CircularProgressIndicator())
                : _mascotasFiltradas.isEmpty
                    ? EmptyStateWidget(
                        icon: Icons.pets,
                        message: _searchQuery.isEmpty
                            ? 'No tienes mascotas registradas'
                            : 'No se encontraron mascotas',
                        actionText: 'Registrar Mascota',
                        onAction: () => Navigator.pushNamed(
                          context,
                          '/cliente/mascotas/nueva',
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _loadMascotas,
                        child: GridView.builder(
                          padding: EdgeInsets.all(16),
                          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            childAspectRatio: 0.8,
                            crossAxisSpacing: 12,
                            mainAxisSpacing: 12,
                          ),
                          itemCount: _mascotasFiltradas.length,
                          itemBuilder: (context, index) {
                            final mascota = _mascotasFiltradas[index];
                            return MascotaCard(
                              mascota: mascota,
                              showActions: true,
                              onTap: () async {
                                await Navigator.pushNamed(
                                  context,
                                  '/cliente/mascotas/detalle',
                                  arguments: mascota,
                                );
                                _loadMascotas();
                              },
                              onEdit: () async {
                                await Navigator.pushNamed(
                                  context,
                                  '/cliente/mascotas/editar',
                                  arguments: mascota,
                                );
                                _loadMascotas();
                              },
                              onDelete: () => _confirmarEliminar(mascota),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          await Navigator.pushNamed(context, '/cliente/mascotas/nueva');
          _loadMascotas();
        },
        child: Icon(Icons.add),
        tooltip: 'Registrar Mascota',
      ),
    );
  }

  Future<void> _confirmarEliminar(Mascota mascota) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Eliminar Mascota'),
        content: Text('¿Estás seguro de eliminar a ${mascota.nombre}?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: Text('Eliminar'),
          ),
        ],
      ),
    );

    if (confirmar == true) {
      try {
        await _service.deleteMascota(mascota.id);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Mascota eliminada')),
        );
        _loadMascotas();
      } catch (e) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: ${e.toString()}')),
        );
      }
    }
  }
}
```

### **📋 3. Detalle de Mascota - `mascota_detail_screen.dart`**

```dart
class MascotaDetailScreen extends StatefulWidget {
  final Mascota mascota;

  const MascotaDetailScreen({required this.mascota});

  @override
  _MascotaDetailScreenState createState() => _MascotaDetailScreenState();
}

class _MascotaDetailScreenState extends State<MascotaDetailScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final HistorialService _historialService = HistorialService();
  final CitaService _citaService = CitaService();
  
  List<HistorialMedico> _historial = [];
  List<Cita> _citas = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      _historial = await _historialService.getHistorialByMascota(widget.mascota.id);
      _citas = await _citaService.getCitasByMascota(widget.mascota.id);
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: ${e.toString()}')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.mascota.nombre),
        actions: [
          IconButton(
            icon: Icon(Icons.edit),
            onPressed: () => Navigator.pushNamed(
              context,
              '/cliente/mascotas/editar',
              arguments: widget.mascota,
            ),
          ),
          PopupMenuButton(
            itemBuilder: (context) => [
              PopupMenuItem(
                value: 'qr',
                child: ListTile(
                  leading: Icon(Icons.qr_code),
                  title: Text('Ver QR'),
                ),
              ),
              PopupMenuItem(
                value: 'compartir',
                child: ListTile(
                  leading: Icon(Icons.share),
                  title: Text('Compartir'),
                ),
              ),
            ],
            onSelected: (value) {
              if (value == 'qr') {
                Navigator.pushNamed(
                  context,
                  '/cliente/mascotas/qr',
                  arguments: widget.mascota,
                );
              } else if (value == 'compartir') {
                // Implementar compartir
              }
            },
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          tabs: [
            Tab(text: 'Info', icon: Icon(Icons.info)),
            Tab(text: 'Historial', icon: Icon(Icons.medical_services)),
            Tab(text: 'Citas', icon: Icon(Icons.calendar_today)),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildInfoTab(),
          _buildHistorialTab(),
          _buildCitasTab(),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => Navigator.pushNamed(
          context,
          '/cliente/agendar-cita',
          arguments: widget.mascota,
        ),
        icon: Icon(Icons.add),
        label: Text('Agendar Cita'),
      ),
    );
  }

  Widget _buildInfoTab() {
    return SingleChildScrollView(
      padding: EdgeInsets.all(16),
      child: Column(
        children: [
          // Foto de mascota
          Center(
            child: CircleAvatar(
              radius: 60,
              backgroundImage: widget.mascota.fotoUrl != null
                  ? NetworkImage(widget.mascota.fotoUrl!)
                  : null,
              child: widget.mascota.fotoUrl == null
                  ? Icon(Icons.pets, size: 60)
                  : null,
            ),
          ),
          SizedBox(height: 24),
          
          // Información básica
          Card(
            child: Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Información Básica',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  SizedBox(height: 16),
                  _buildInfoRow('Nombre', widget.mascota.nombre),
                  _buildInfoRow('Especie', widget.mascota.especie),
                  _buildInfoRow('Raza', widget.mascota.raza),
                  _buildInfoRow('Sexo', widget.mascota.sexo),
                  _buildInfoRow('Edad', widget.mascota.edad),
                  if (widget.mascota.color != null)
                    _buildInfoRow('Color', widget.mascota.color!),
                  if (widget.mascota.fechaNacimiento != null)
                    _buildInfoRow(
                      'Fecha de Nacimiento',
                      DateFormat('dd/MM/yyyy').format(widget.mascota.fechaNacimiento!),
                    ),
                ],
              ),
            ),
          ),
          SizedBox(height: 16),
          
          // Información médica
          if (widget.mascota.alergias != null ||
              widget.mascota.condicionesMedicas != null ||
              widget.mascota.tipoSangre != null)
            Card(
              child: Padding(
                padding: EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Información Médica',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    SizedBox(height: 16),
                    if (widget.mascota.alergias != null)
                      _buildInfoRow('Alergias', widget.mascota.alergias!),
                    if (widget.mascota.condicionesMedicas != null)
                      _buildInfoRow(
                        'Condiciones Médicas',
                        widget.mascota.condicionesMedicas!,
                      ),
                    if (widget.mascota.tipoSangre != null)
                      _buildInfoRow('Tipo de Sangre', widget.mascota.tipoSangre!),
                  ],
                ),
              ),
            ),
          SizedBox(height: 16),
          
          // Identificación
          Card(
            child: Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Identificación',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  SizedBox(height: 16),
                  if (widget.mascota.chipId != null)
                    _buildInfoRow('Chip ID', widget.mascota.chipId!),
                  if (widget.mascota.microchip != null)
                    _buildInfoRow('Microchip', widget.mascota.microchip!),
                  if (widget.mascota.qrCode != null)
                    ListTile(
                      contentPadding: EdgeInsets.zero,
                      title: Text('Código QR'),
                      subtitle: Text(widget.mascota.qrCode!),
                      trailing: ElevatedButton.icon(
                        onPressed: () => Navigator.pushNamed(
                          context,
                          '/cliente/mascotas/qr',
                          arguments: widget.mascota,
                        ),
                        icon: Icon(Icons.qr_code, size: 20),
                        label: Text('Ver QR'),
                      ),
                    ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              '$label:',
              style: TextStyle(
                fontWeight: FontWeight.w500,
                color: Colors.grey[700],
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(fontWeight: FontWeight.w500),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHistorialTab() {
    if (_isLoading) {
      return Center(child: CircularProgressIndicator());
    }

    if (_historial.isEmpty) {
      return EmptyStateWidget(
        icon: Icons.medical_services,
        message: 'No hay historial médico',
      );
    }

    return ListView.builder(
      padding: EdgeInsets.all(16),
      itemCount: _historial.length,
      itemBuilder: (context, index) {
        final registro = _historial[index];
        return Card(
          margin: EdgeInsets.only(bottom: 12),
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: _getTipoColor(registro.tipo),
              child: Icon(
                _getTipoIcon(registro.tipo),
                color: Colors.white,
              ),
            ),
            title: Text(registro.diagnostico),
            subtitle: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(DateFormat('dd/MM/yyyy').format(registro.fecha)),
                if (registro.veterinario != null)
                  Text('Dr. ${registro.veterinario!.nombre}'),
              ],
            ),
            trailing: Icon(Icons.chevron_right),
            onTap: () {
              // Navegar a detalle de historial
            },
          ),
        );
      },
    );
  }

  Widget _buildCitasTab() {
    if (_isLoading) {
      return Center(child: CircularProgressIndicator());
    }

    if (_citas.isEmpty) {
      return EmptyStateWidget(
        icon: Icons.event_available,
        message: 'No hay citas registradas',
        actionText: 'Agendar Cita',
        onAction: () => Navigator.pushNamed(
          context,
          '/cliente/agendar-cita',
          arguments: widget.mascota,
        ),
      );
    }

    return ListView.builder(
      padding: EdgeInsets.all(16),
      itemCount: _citas.length,
      itemBuilder: (context, index) {
        final cita = _citas[index];
        return CitaCard(
          cita: cita,
          onTap: () => Navigator.pushNamed(
            context,
            '/cliente/citas/detalle',
            arguments: cita,
          ),
        );
      },
    );
  }

  Color _getTipoColor(String tipo) {
    switch (tipo) {
      case 'consulta':
        return Colors.blue;
      case 'vacuna':
        return Colors.green;
      case 'procedimiento':
        return Colors.red;
      case 'control':
        return Colors.orange;
      default:
        return Colors.grey;
    }
  }

  IconData _getTipoIcon(String tipo) {
    switch (tipo) {
      case 'consulta':
        return Icons.medical_services;
      case 'vacuna':
        return Icons.vaccines;
      case 'procedimiento':
        return Icons.healing;
      case 'control':
        return Icons.check_circle;
      default:
        return Icons.note;
    }
  }
}
```

### **📅 4. Agendar Cita - `agendar_cita_screen.dart`**

```dart
class AgendarCitaScreen extends StatefulWidget {
  final Mascota? mascotaPreseleccionada;

  const AgendarCitaScreen({this.mascotaPreseleccionada});

  @override
  _AgendarCitaScreenState createState() => _AgendarCitaScreenState();
}

class _AgendarCitaScreenState extends State<AgendarCitaScreen> {
  final _formKey = GlobalKey<FormState>();
  final MascotaService _mascotaService = MascotaService();
  final VeterinarioService _veterinarioService = VeterinarioService();
  final ServicioService _servicioService = ServicioService();
  final CitaService _citaService = CitaService();
  
  int _currentStep = 0;
  
  // Step 1: Mascota
  List<Mascota> _mascotas = [];
  Mascota? _mascotaSeleccionada;
  
  // Step 2: Servicio
  List<Servicio> _servicios = [];
  List<int> _serviciosSeleccionados = [];
  
  // Step 3: Veterinario
  List<Veterinario> _veterinarios = [];
  Veterinario? _veterinarioSeleccionado;
  
  // Step 4: Fecha y Hora
  DateTime? _fechaSeleccionada;
  String? _horaSeleccionada;
  List<String> _horasDisponibles = [];
  
  // Step 5: Detalles
  final TextEditingController _motivoController = TextEditingController();
  final TextEditingController _notasController = TextEditingController();
  
  bool _isLoading = false;
  bool _isLoadingHorarios = false;

  @override
  void initState() {
    super.initState();
    _mascotaSeleccionada = widget.mascotaPreseleccionada;
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    setState(() => _isLoading = true);
    try {
      final user = await AuthService().getUser();
      _mascotas = await _mascotaService.getMascotasByCliente(user.id);
      _servicios = await _servicioService.getServicios();
      _veterinarios = await _veterinarioService.getVeterinarios();
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: ${e.toString()}')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _loadHorariosDisponibles() async {
    if (_veterinarioSeleccionado == null || _fechaSeleccionada == null) return;
    
    setState(() => _isLoadingHorarios = true);
    try {
      final disponibilidad = await _veterinarioService.getDisponibilidad(
        _veterinarioSeleccionado!.id,
        _fechaSeleccionada!,
      );
      
      _horasDisponibles = disponibilidad['slots_disponibles']
          .where((slot) => slot['disponible'] == true)
          .map<String>((slot) => slot['hora'].toString())
          .toList();
      
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: ${e.toString()}')),
      );
    } finally {
      setState(() => _isLoadingHorarios = false);
    }
  }

  Future<void> _agendarCita() async {
    if (!_formKey.currentState!.validate()) return;
    
    setState(() => _isLoading = true);
    try {
      final user = await AuthService().getUser();
      
      await _citaService.agendarCita({
        'cliente_id': user.id,
        'mascota_id': _mascotaSeleccionada!.id,
        'veterinario_id': _veterinarioSeleccionado!.id,
        'fecha': DateFormat('yyyy-MM-dd').format(_fechaSeleccionada!),
        'hora': _horaSeleccionada!,
        'motivo': _motivoController.text,
        'notas': _notasController.text,
        'servicio_ids': _serviciosSeleccionados,
      });
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Cita agendada exitosamente')),
      );
      
      Navigator.pop(context, true);
      
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: ${e.toString()}')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Agendar Cita'),
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : Form(
              key: _formKey,
              child: Stepper(
                currentStep: _currentStep,
                onStepContinue: _onStepContinue,
                onStepCancel: _onStepCancel,
                onStepTapped: (step) => setState(() => _currentStep = step),
                controlsBuilder: (context, details) {
                  return Row(
                    children: [
                      if (_currentStep < 4)
                        ElevatedButton(
                          onPressed: details.onStepContinue,
                          child: Text('Siguiente'),
                        ),
                      if (_currentStep == 4)
                        ElevatedButton(
                          onPressed: _agendarCita,
                          child: Text('Agendar Cita'),
                        ),
                      SizedBox(width: 12),
                      if (_currentStep > 0)
                        TextButton(
                          onPressed: details.onStepCancel,
                          child: Text('Atrás'),
                        ),
                    ],
                  );
                },
                steps: [
                  // Step 1: Seleccionar Mascota
                  Step(
                    title: Text('Mascota'),
                    content: _buildMascotaStep(),
                    isActive: _currentStep >= 0,
                    state: _currentStep > 0 ? StepState.complete : StepState.indexed,
                  ),
                  
                  // Step 2: Seleccionar Servicio
                  Step(
                    title: Text('Servicio'),
                    content: _buildServicioStep(),
                    isActive: _currentStep >= 1,
                    state: _currentStep > 1 ? StepState.complete : StepState.indexed,
                  ),
                  
                  // Step 3: Seleccionar Veterinario
                  Step(
                    title: Text('Veterinario'),
                    content: _buildVeterinarioStep(),
                    isActive: _currentStep >= 2,
                    state: _currentStep > 2 ? StepState.complete : StepState.indexed,
                  ),
                  
                  // Step 4: Seleccionar Fecha y Hora
                  Step(
                    title: Text('Fecha y Hora'),
                    content: _buildFechaHoraStep(),
                    isActive: _currentStep >= 3,
                    state: _currentStep > 3 ? StepState.complete : StepState.indexed,
                  ),
                  
                  // Step 5: Detalles
                  Step(
                    title: Text('Detalles'),
                    content: _buildDetallesStep(),
                    isActive: _currentStep >= 4,
                    state: StepState.indexed,
                  ),
                ],
              ),
            ),
    );
  }

  void _onStepContinue() {
    if (_currentStep == 0 && _mascotaSeleccionada == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Selecciona una mascota')),
      );
      return;
    }
    
    if (_currentStep == 1 && _serviciosSeleccionados.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Selecciona al menos un servicio')),
      );
      return;
    }
    
    if (_currentStep == 2 && _veterinarioSeleccionado == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Selecciona un veterinario')),
      );
      return;
    }
    
    if (_currentStep == 3) {
      if (_fechaSeleccionada == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Selecciona una fecha')),
        );
        return;
      }
      if (_horaSeleccionada == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Selecciona una hora')),
        );
        return;
      }
    }
    
    if (_currentStep < 4) {
      setState(() => _currentStep++);
      
      // Cargar horarios disponibles al llegar al step 3
      if (_currentStep == 3) {
        _loadHorariosDisponibles();
      }
    }
  }

  void _onStepCancel() {
    if (_currentStep > 0) {
      setState(() => _currentStep--);
    }
  }

  Widget _buildMascotaStep() {
    return Column(
      children: _mascotas.map((mascota) {
        return RadioListTile<Mascota>(
          value: mascota,
          groupValue: _mascotaSeleccionada,
          onChanged: (value) => setState(() => _mascotaSeleccionada = value),
          title: Text(mascota.nombre),
          subtitle: Text('${mascota.especie} - ${mascota.raza}'),
          secondary: CircleAvatar(
            backgroundImage: mascota.fotoUrl != null
                ? NetworkImage(mascota.fotoUrl!)
                : null,
            child: mascota.fotoUrl == null
                ? Icon(Icons.pets)
                : null,
          ),
        );
      }).toList(),
    );
  }

  Widget _buildServicioStep() {
    return Column(
      children: _servicios.map((servicio) {
        final isSelected = _serviciosSeleccionados.contains(servicio.id);
        return CheckboxListTile(
          value: isSelected,
          onChanged: (value) {
            setState(() {
              if (value == true) {
                _serviciosSeleccionados.add(servicio.id);
              } else {
                _serviciosSeleccionados.remove(servicio.id);
              }
            });
          },
          title: Text(servicio.nombre),
          subtitle: Text('\$${servicio.precio.toStringAsFixed(2)}'),
          secondary: Icon(Icons.medical_services),
        );
      }).toList(),
    );
  }

  Widget _buildVeterinarioStep() {
    return Column(
      children: _veterinarios.map((veterinario) {
        return RadioListTile<Veterinario>(
          value: veterinario,
          groupValue: _veterinarioSeleccionado,
          onChanged: (value) {
            setState(() {
              _veterinarioSeleccionado = value;
              _horaSeleccionada = null; // Reset hora
            });
          },
          title: Text('Dr. ${veterinario.nombre}'),
          subtitle: Text(veterinario.especialidad ?? 'Veterinario General'),
          secondary: CircleAvatar(
            backgroundImage: veterinario.fotoUrl != null
                ? NetworkImage(veterinario.fotoUrl!)
                : null,
            child: veterinario.fotoUrl == null
                ? Icon(Icons.person)
                : null,
          ),
        );
      }).toList(),
    );
  }

  Widget _buildFechaHoraStep() {
    return Column(
      children: [
        // Selector de fecha
        ListTile(
          leading: Icon(Icons.calendar_today),
          title: Text('Fecha'),
          subtitle: Text(
            _fechaSeleccionada != null
                ? DateFormat('dd/MM/yyyy').format(_fechaSeleccionada!)
                : 'Seleccionar fecha',
          ),
          trailing: Icon(Icons.chevron_right),
          onTap: _selectFecha,
        ),
        Divider(),
        
        // Selector de hora
        if (_fechaSeleccionada != null) ...[
          ListTile(
            leading: Icon(Icons.access_time),
            title: Text('Hora'),
            subtitle: Text(_horaSeleccionada ?? 'Seleccionar hora'),
          ),
          if (_isLoadingHorarios)
            Center(child: CircularProgressIndicator())
          else if (_horasDisponibles.isEmpty)
            Padding(
              padding: EdgeInsets.all(16),
              child: Text(
                'No hay horarios disponibles para esta fecha',
                style: TextStyle(color: Colors.red),
              ),
            )
          else
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: _horasDisponibles.map((hora) {
                final isSelected = _horaSeleccionada == hora;
                return FilterChip(
                  label: Text(hora),
                  selected: isSelected,
                  onSelected: (selected) {
                    setState(() => _horaSeleccionada = selected ? hora : null);
                  },
                );
              }).toList(),
            ),
        ],
      ],
    );
  }

  Future<void> _selectFecha() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _fechaSeleccionada ?? now,
      firstDate: now,
      lastDate: now.add(Duration(days: 90)),
    );
    
    if (picked != null) {
      setState(() {
        _fechaSeleccionada = picked;
        _horaSeleccionada = null; // Reset hora
      });
      _loadHorariosDisponibles();
    }
  }

  Widget _buildDetallesStep() {
    return Column(
      children: [
        // Resumen de la cita
        Card(
          child: Padding(
            padding: EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Resumen',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                SizedBox(height: 16),
                _buildResumenRow('Mascota', _mascotaSeleccionada!.nombre),
                _buildResumenRow(
                  'Servicios',
                  _servicios
                      .where((s) => _serviciosSeleccionados.contains(s.id))
                      .map((s) => s.nombre)
                      .join(', '),
                ),
                _buildResumenRow(
                  'Veterinario',
                  'Dr. ${_veterinarioSeleccionado!.nombre}',
                ),
                _buildResumenRow(
                  'Fecha',
                  DateFormat('dd/MM/yyyy').format(_fechaSeleccionada!),
                ),
                _buildResumenRow('Hora', _horaSeleccionada!),
              ],
            ),
          ),
        ),
        SizedBox(height: 16),
        
        // Motivo
        TextFormField(
          controller: _motivoController,
          decoration: InputDecoration(
            labelText: 'Motivo de la consulta *',
            hintText: 'Ej: Control de rutina, síntomas, etc.',
            prefixIcon: Icon(Icons.description),
          ),
          maxLines: 2,
          validator: (value) {
            if (value == null || value.isEmpty) {
              return 'El motivo es obligatorio';
            }
            return null;
          },
        ),
        SizedBox(height: 16),
        
        // Notas adicionales
        TextFormField(
          controller: _notasController,
          decoration: InputDecoration(
            labelText: 'Notas adicionales (opcional)',
            hintText: 'Información adicional para el veterinario',
            prefixIcon: Icon(Icons.note),
          ),
          maxLines: 3,
        ),
      ],
    );
  }

  Widget _buildResumenRow(String label, String value) {
    return Padding(
      padding: EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              '$label:',
              style: TextStyle(
                fontWeight: FontWeight.w500,
                color: Colors.grey[700],
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }
}
```

---

**🎯 Documento Continuará...**

Esta es la **PARTE 1** del documento completo. El archivo incluye:

✅ **Estructura completa del proyecto Flutter**
✅ **Configuración inicial (pubspec.yaml, theme, config)**
✅ **ROL CLIENTE - Pantallas principales:**
- Dashboard completo con estadísticas
- Mis Mascotas (lista con búsqueda y filtros)
- Detalle de Mascota (tabs: info, historial, citas)
- Agendar Cita (wizard de 5 pasos con validación)

**¿Quieres que continúe con:**
- ✅ ROL VETERINARIO (pantallas completas)
- ✅ ROL RECEPCIONISTA (pantallas completas)  
- ✅ ROL ADMINISTRADOR (pantallas completas)
- ✅ Servicios completos (AuthService, MascotaService, CitaService, etc.)
- ✅ Widgets reutilizables (MascotaCard, CitaCard, etc.)
- ✅ Navegación y rutas completas

**El documento completo tendrá más de 5000 líneas de código Flutter listo para usar!** 🚀

¿Continúo con las siguientes secciones?
