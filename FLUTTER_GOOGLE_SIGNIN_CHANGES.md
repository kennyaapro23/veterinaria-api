# 🔄 Cambios para Habilitar Google Sign-In en Flutter

## 📋 Resumen

El backend **YA SOPORTA** Google Sign-In. Solo necesitas hacer estos cambios en Flutter:

---

## 1️⃣ Actualizar `pubspec.yaml`

```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # Dependencias existentes...
  firebase_core: ^2.24.2
  firebase_auth: ^4.15.3
  firebase_messaging: ^14.7.9
  
  # ✅ AGREGAR ESTA LÍNEA:
  google_sign_in: ^6.2.1
```

Ejecutar:
```bash
flutter pub get
```

---

## 2️⃣ Configurar Firebase (Una sola vez)

### Obtener SHA-1 y SHA-256:
```bash
cd android
.\gradlew signingReport

# Copiar SHA1 y SHA-256 que aparecen en la salida
```

### Agregar en Firebase Console:
1. Ir a: https://console.firebase.google.com/
2. Project Settings → Your apps → Android
3. Click "Add fingerprint"
4. Pegar SHA-1
5. Click "Add fingerprint" nuevamente
6. Pegar SHA-256
7. Descargar nuevo `google-services.json`
8. Reemplazar en `android/app/google-services.json`

---

## 3️⃣ Actualizar `android/app/build.gradle`

```gradle
dependencies {
    // Firebase existentes...
    implementation platform('com.google.firebase:firebase-bom:32.7.0')
    implementation 'com.google.firebase:firebase-auth'
    
    // ✅ AGREGAR ESTA LÍNEA:
    implementation 'com.google.android.gms:play-services-auth:20.7.0'
}
```

---

## 4️⃣ Actualizar `lib/services/auth_service.dart`

### Agregar imports:
```dart
import 'package:google_sign_in/google_sign_in.dart';
import 'package:firebase_auth/firebase_auth.dart';
```

### Agregar método (dentro de la clase `AuthService`):

```dart
// Login con Google
Future<Map<String, dynamic>> loginWithGoogle() async {
  try {
    // 1. Iniciar Google Sign-In
    final GoogleSignIn googleSignIn = GoogleSignIn(
      scopes: ['email', 'profile'],
    );

    // 2. Seleccionar cuenta
    final GoogleSignInAccount? googleUser = await googleSignIn.signIn();
    
    if (googleUser == null) {
      return {
        'success': false,
        'message': 'Inicio de sesión cancelado',
      };
    }

    // 3. Obtener auth de Google
    final GoogleSignInAuthentication googleAuth = 
        await googleUser.authentication;

    // 4. Crear credencial Firebase
    final credential = GoogleAuthProvider.credential(
      accessToken: googleAuth.accessToken,
      idToken: googleAuth.idToken,
    );

    // 5. Sign-in con Firebase
    final UserCredential userCredential = 
        await FirebaseAuth.instance.signInWithCredential(credential);

    // 6. Obtener token Firebase
    final firebaseToken = await userCredential.user?.getIdToken();

    if (firebaseToken == null) {
      return {
        'success': false,
        'message': 'Error al obtener token de Firebase',
      };
    }

    // 7. Verificar con backend Laravel
    final response = await http.post(
      Uri.parse('${AppConfig.baseUrl}firebase/verify'),
      headers: AppConfig.headers,
      body: jsonEncode({
        'firebase_token': firebaseToken,
      }),
    ).timeout(AppConfig.requestTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      
      // Guardar token y usuario
      await _saveToken(data['sanctum_token']);
      await _saveUser(data['user']);

      return {
        'success': true,
        'user': User.fromJson(data['user']),
        'token': data['sanctum_token'],
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
      'message': 'Error: ${e.toString()}',
    };
  }
}
```

---

## 5️⃣ Actualizar `lib/screens/login_screen.dart`

### Agregar variable de estado:
```dart
bool _isGoogleLoading = false;
```

### Agregar método:
```dart
Future<void> _loginWithGoogle() async {
  setState(() => _isGoogleLoading = true);

  try {
    final result = await _authService.loginWithGoogle();

    if (result['success']) {
      // Inicializar Firebase Messaging
      await _firebaseService.initialize();

      // Navegar a home
      Navigator.pushReplacementNamed(context, '/home');
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result['message'])),
      );
    }
  } finally {
    setState(() => _isGoogleLoading = false);
  }
}
```

### Agregar botón en el UI (después del botón de login tradicional):

```dart
SizedBox(height: 24),

// Divider
Row(
  children: [
    Expanded(child: Divider()),
    Padding(
      padding: EdgeInsets.symmetric(horizontal: 16),
      child: Text('O', style: TextStyle(color: Colors.grey)),
    ),
    Expanded(child: Divider()),
  ],
),

SizedBox(height: 24),

// Botón Google Sign-In
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
```

---

## 🎯 Resultado Final

Cuando esté todo configurado:

1. Usuario verá botón **"Continuar con Google"** ✅
2. Al hacer click, se abrirá selector de cuentas de Google ✅
3. Usuario selecciona su cuenta ✅
4. App valida con Firebase automáticamente ✅
5. Backend crea/actualiza usuario ✅
6. Usuario ingresa a la app ✅
7. Notificaciones push funcionan normalmente ✅

---

## 🧪 Probar

```bash
# 1. Ejecutar servidor Laravel
php artisan serve --host=0.0.0.0 --port=8000

# 2. Configurar ADB reverse (emulador)
adb reverse tcp:8000 tcp:8000

# 3. Ejecutar Flutter app
flutter run

# 4. Hacer click en "Continuar con Google"
# 5. Verificar login exitoso
```

---

## ✅ Checklist

- [ ] `google_sign_in: ^6.2.1` agregado a pubspec.yaml
- [ ] `flutter pub get` ejecutado
- [ ] SHA-1 y SHA-256 obtenidos con gradlew signingReport
- [ ] Fingerprints agregados en Firebase Console
- [ ] Nuevo google-services.json descargado y reemplazado
- [ ] play-services-auth agregado a build.gradle
- [ ] Método `loginWithGoogle()` agregado a AuthService
- [ ] Botón "Continuar con Google" agregado a LoginScreen
- [ ] Probado en emulador con cuenta Google real

---

**🚀 ¡Listo! Ahora tu app soporta login con Google**
