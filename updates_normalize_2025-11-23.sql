-- Archivo: updates_normalize_2025-11-23.sql
-- Fecha: 2025-11-23
-- Propósito: normalizar valores numéricos NULL o cadena vacía a 0

-- Verificación antes
SELECT 'before_mascotas_peso' as item, COUNT(*) as filas FROM mascotas WHERE peso IS NULL OR peso = '';
SELECT 'before_mascotas_edad' as item, COUNT(*) as filas FROM mascotas WHERE edad IS NULL OR edad = '';
SELECT 'before_citas_duracion' as item, COUNT(*) as filas FROM citas WHERE duracion_minutos IS NULL OR duracion_minutos = '';

-- Actualizaciones
UPDATE mascotas SET peso = 0 WHERE peso IS NULL OR peso = '';
UPDATE mascotas SET edad = 0 WHERE edad IS NULL OR edad = '';
UPDATE citas SET duracion_minutos = 0 WHERE duracion_minutos IS NULL OR duracion_minutos = '';

-- Verificación después
SELECT 'after_mascotas_peso' as item, COUNT(*) as filas FROM mascotas WHERE peso IS NULL OR peso = '';
SELECT 'after_mascotas_edad' as item, COUNT(*) as filas FROM mascotas WHERE edad IS NULL OR edad = '';
SELECT 'after_citas_duracion' as item, COUNT(*) as filas FROM citas WHERE duracion_minutos IS NULL OR duracion_minutos = '';

-- Opcional: mostrar cuántas filas totales existen (contexto)
SELECT 'total_mascotas' as item, COUNT(*) as filas FROM mascotas;
SELECT 'total_citas' as item, COUNT(*) as filas FROM citas;
