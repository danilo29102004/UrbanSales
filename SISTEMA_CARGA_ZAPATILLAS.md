# 📱 Sistema de Carga de Zapatillas - Guía de Uso

## ✨ Cambios Realizados

### 1. Base de Datos
- ✅ Eliminadas todas las zapatillas ficticias (15 productos)
- ✅ Añadido campo `imagen` a la tabla `zapatilla` (tipo VARCHAR)
- ✅ Migración ejecutada correctamente

### 2. Servicios Creados
- **ZapatillaUploadService**: Maneja la carga y eliminación de imágenes
  - Valida tipos MIME (JPG, PNG, GIF, WebP)
  - Valida tamaño máximo (5MB)
  - Genera nombres únicos para evitar conflictos
  - Almacena en `/public/uploads/zapatillas/`

### 3. Controlador
- **ZapatillaVendedorController**: Gestiona todas las operaciones
  - Listar zapatillas del vendedor
  - Crear nueva zapatilla con imagen
  - Editar zapatilla (cambiar datos e imagen)
  - Eliminar zapatilla (elimina imagen también)
  - Validaciones de seguridad y rol de vendedor

### 4. Interfaz (Templates)
- **lista.html.twig**: Galería de zapatillas del vendedor
- **crear.html.twig**: Formulario para subir nueva zapatilla
- **editar.html.twig**: Formulario para editar zapatilla existente

## 🔒 Rutas y Permisos

Las siguientes rutas requieren estar autenticado como vendedor aprobado:

| Ruta | Método | Función |
|------|--------|---------|
| `/vendedor/zapatillas` | GET | Listar mis zapatillas |
| `/vendedor/zapatillas/crear` | GET/POST | Crear nueva zapatilla |
| `/vendedor/zapatillas/{id}/editar` | GET/POST | Editar zapatilla |
| `/vendedor/zapatillas/{id}/eliminar` | POST | Eliminar zapatilla |

## 👤 Usuarios de Prueba

**Vendedor Aprobado:**
- Email: `juan.test@example.com`
- Contraseña: `password123`
- Estado: APROBADO
- Zapatillas: 0 (ahora vacío - listo para subir nuevas)

## 📤 Cómo Subir una Zapatilla

### Paso 1: Iniciar sesión
1. Ir a `http://192.168.1.143:8000`
2. Hacer clic en "Iniciar sesión"
3. Usar credenciales de vendedor

### Paso 2: Acceder a "Mis Zapatillas"
1. En el panel del vendedor, ir a "Mis Zapatillas"
2. Hacer clic en "+ Subir Nueva Zapatilla"

### Paso 3: Completar el formulario
- **Modelo**: Ej "Jordan 1 Retro High"
- **Marca**: Ej "Air Jordan"
- **Talla**: Ej "10" o "10.5"
- **Precio**: Ej "150.00"
- **Stock**: Ej "5"
- **Categoría**: Seleccionar categoría
- **Foto**: Arrastra o haz clic para subir (JPG, PNG, GIF, WebP)

### Paso 4: Enviar
Hacer clic en "Subir Zapatilla"

## 🖼️ Características de la Carga de Imágenes

✨ **Interfaz moderna:**
- Drag & drop (arrastra y suelta)
- Click para seleccionar archivo
- Previsualización de nombre y tamaño
- Validación en tiempo real

📋 **Validaciones:**
- Tipos de archivo: JPG, PNG, GIF, WebP
- Tamaño máximo: 5MB
- Nombres únicos automáticos
- Ruta: `/uploads/zapatillas/zapatilla_[timestamp].[ext]`

## ✏️ Cómo Editar una Zapatilla

1. En "Mis Zapatillas", hacer clic en "Editar"
2. Modificar cualquier dato
3. (Opcional) Cambiar foto
4. Hacer clic en "Guardar Cambios"

La foto anterior se elimina automáticamente si se sube una nueva.

## 🗑️ Cómo Eliminar una Zapatilla

1. En "Mis Zapatillas", hacer clic en "Eliminar"
2. Confirmar eliminación
3. La foto se elimina automáticamente de la carpeta

## 📁 Estructura de Archivos

```
proyecto/
├── src/
│   ├── Controller/
│   │   └── ZapatillaVendedorController.php (NUEVO)
│   ├── Service/
│   │   └── ZapatillaUploadService.php (NUEVO)
│   └── Entity/
│       └── Zapatilla.php (actualizado con campo imagen)
├── templates/
│   └── vendedor/
│       └── zapatillas/
│           ├── lista.html.twig (NUEVO)
│           ├── crear.html.twig (NUEVO)
│           └── editar.html.twig (NUEVO)
├── public/
│   └── uploads/
│       └── zapatillas/ (NUEVO - almacena imágenes)
├── migrations/
│   └── Version20260430093156.php (NUEVA - añade campo imagen)
└── config/
    └── services.yaml (actualizado)
```

## 🐛 Validaciones y Manejo de Errores

✅ **Seguridad:**
- Solo vendedores aprobados pueden subir
- Solo se pueden editar/eliminar zapatillas propias
- Validación de CSRF en formularios
- Validación de tipos de archivo

✅ **Errores:**
- Archivo no válido → Mensaje de error
- Archivo muy grande → Mensaje de error
- Falta campo obligatorio → Mensaje de error
- Zapatilla no existe → 404

## 🎨 Diseño

- Interfaz moderna y limpia
- Responsive (funciona en móvil)
- Drag & drop intuitivo
- Feedback visual de errores y éxitos
- Galería de productos con imagen

## 💡 Próximas Mejoras Sugeridas

- Galería de múltiples imágenes por zapatilla
- Reordenar productos (arrastrar)
- Estadísticas de ventas
- Descuentos y ofertas
- Sincronización de stock en tiempo real
- Búsqueda avanzada de productos

---

**Estado:** ✅ Sistema de carga completamente funcional y listo para usar
**Fecha:** 30 de Abril de 2026
