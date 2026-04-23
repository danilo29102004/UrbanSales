# Sistema de Vendedor - UrbanSole

## 📋 Descripción General

El nuevo sistema de vendedor permite que los usuarios se conviertan en vendedores en la plataforma UrbanSole. Los vendedores pueden subir zapatillas que aparecerán en el catálogo disponible para todos los compradores.

## 🚀 Rutas Principales

### Para Usuarios Compradores

- **`GET /`** - Página principal
- **`GET /zapatillas`** - Catálogo de zapatillas (muestra vendedor)
- **`GET /dashboard`** - Dashboard personal con opción de vender

### Para Solicitantes de Vendedor

- **`GET /vendedor/solicitar`** - Formulario para solicitar ser vendedor
- **`POST /vendedor/solicitar`** - Enviar solicitud (sube documento)
- **`GET /vendedor/panel`** - Panel del vendedor (una vez aprobado)

### Para Vendedores Aprobados

- **`GET /zapatillas/crear`** - Formulario para subir nueva zapatilla
- **`POST /zapatillas/crear`** - Crear zapatilla (API)
- **`GET /zapatillas/{id}/editar`** - Formulario para editar zapatilla
- **`POST /zapatillas/{id}/actualizar`** - Guardar cambios de zapatilla
- **`POST /zapatillas/{id}/eliminar`** - Eliminar zapatilla

### Rutas Administrativas

- **`GET /admin/vendedores/solicitudes-pendientes`** - Ver solicitudes pendientes
- **`POST /admin/vendedores/{id}/aprobar`** - Aprobar vendedor
- **`POST /admin/vendedores/{id}/rechazar`** - Rechazar vendedor

## 📝 Proceso: Convertirse en Vendedor

### Paso 1: Solicitar Acceso
1. Usuario autenticado accede a "Vender" en el menú
2. Redirigido a `/vendedor/solicitar`
3. Completa formulario con:
   - **DNI/Cédula**: Número de identificación
   - **Documento**: Archivo de verificación (PDF, DOC, JPG)

### Paso 2: Envío de Solicitud
- El formulario carga el archivo a `/public/uploads/documentos/`
- Se crea registro en tabla `Vendedor` con estado `PENDIENTE`
- Usuario ve confirmación en el mismo formulario

### Paso 3: Revisión (Administrador)
- Admin accede a `/admin/vendedores/solicitudes-pendientes`
- Revisa DNI y documento
- Aprueba o rechaza solicitud

### Paso 4: Notificación al Usuario
- Estado se actualiza en `/vendedor/solicitar`
- Si es aprobado, puede acceder a panel de vendedor
- Si es rechazado, puede volver a intentar

## 🛒 Proceso: Subir Zapatillas

### Requisitos Previos
- Usuario debe ser vendedor aprobado

### Paso 1: Acceder al Panel
- Opción "Panel de Vendedor" en menú lateral
- Visualiza todas sus zapatillas actuales
- Botón "Subir Nueva Zapatilla"

### Paso 2: Completar Formulario
Campos requeridos:
- **Modelo**: Nombre del modelo (ej: Air Max 90)
- **Marca**: Marca (ej: Nike)
- **Talla**: Tamaño (30-50)
- **Categoría**: Categoría de la zapatilla
- **Precio**: Precio en dólares
- **Stock**: Cantidad disponible

### Paso 3: Publicar
- Clic en "Subir Zapatilla"
- La zapatilla aparece inmediatamente en el catálogo
- El vendedor se muestra en la tarjeta del producto

### Paso 4: Gestionar Zapatillas
- En panel, tabla con todas las zapatillas
- Opciones: "Editar" o "Eliminar"
- Los cambios se reflejan inmediatamente en el catálogo

## 🔧 Editar y Eliminar Zapatillas

### Editar
1. Panel de Vendedor → Tabla de zapatillas
2. Botón "Editar" en la zapatilla deseada
3. Cambiar cualquier campo
4. Clic en "Guardar Cambios"

### Eliminar
1. Panel de Vendedor → Tabla de zapatillas
2. Botón "Eliminar" en la zapatilla deseada
3. Confirmar eliminación
4. La zapatilla se remueve del catálogo

## 📊 Dashboard Actualizado

### Para Compradores
- Opción para "Vender Zapatillas"
- Link directo a `/vendedor/solicitar`

### Para Vendedores Pendientes
- Estado de solicitud
- Fecha de solicitud
- Mensaje de espera

### Para Vendedores Aprobados
- Estado: "APROBADO" ✅
- Botón para subir nueva zapatilla
- Link al panel de vendedor

## 🗂️ Estructura de Carpetas

### Nuevas Carpetas
```
public/
├── uploads/
│   └── documentos/        # Almacena documentos de verificación

templates/
├── vendedor/
│   ├── solicitar.html.twig   # Formulario de solicitud
│   └── panel.html.twig       # Panel del vendedor
├── zapatillas/
│   ├── crear.html.twig       # Subir zapatilla
│   └── editar.html.twig      # Editar zapatilla
```

## 🔐 Seguridad

### Validaciones Implementadas
1. **Solo usuarios autenticados** pueden acceder a funciones de vendedor
2. **Solo vendedores aprobados** pueden subir zapatillas
3. **Vendedores solo pueden editar/eliminar sus propias zapatillas**
4. **Validación de campos** en cliente y servidor
5. **Almacenamiento seguro** de documentos en carpeta protegida

### Validaciones de Formulario
- DNI: Campo requerido
- Documento: Archivo requerido (PDF, DOC, JPG, etc.)
- Zapatilla: Todos los campos requeridos
- Talla: Entre 30 y 50
- Precio: Número decimal positivo
- Stock: Número entero no negativo

## 📱 Interfaz de Usuario

### Menú Principal (base.html.twig)
- Link "Vender" condicional (solo usuarios autenticados)
- Redirige a `/vendedor/solicitar`

### Dashboard
- Panel actualizado con estado de vendedor
- Menú lateral con opciones contextuales
- Atajos rápidos

### Catálogo
- Cada tarjeta de zapatilla muestra al vendedor
- Información completa del producto
- Opción de agregar al carrito

## 📋 Entidades Relacionadas

### Usuario
- Relación 1:1 con Vendedor
- Tiene muchas zapatillas como vendedor

### Vendedor
- Relacionado 1:1 con Usuario
- Estados: PENDIENTE, APROBADO, RECHAZADO
- Almacena DNI y ruta de documento

### Zapatilla
- Relacionada con Usuario (vendedor)
- Visibles en catálogo solo si Usuario es vendedor aprobado

## 🐛 Troubleshooting

### "No eres un vendedor aprobado"
- Asegúrate de haber completado el proceso de solicitud
- Admin debe aprobar tu solicitud

### "No tienes permiso para editar esta zapatilla"
- Solo puedes editar zapatillas que subiste
- Verifica que estés autenticado correctamente

### Documento no se carga
- Verifica que `/public/uploads/documentos/` existe
- Permisos de escritura en esa carpeta
- Tamaño de archivo válido

## 🎯 Próximas Mejoras Sugeridas

- Sistema de calificaciones para vendedores
- Estadísticas de ventas
- Comisión automática por ventas
- Historial de transacciones
- Notificaciones por correo
- Sistema de disputas

---

**Versión**: 1.0
**Última actualización**: Abril 2026
