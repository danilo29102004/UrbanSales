# Guía de Prueba - Sistema de Vendedor

## ✅ Pruebas Funcionales

### 1. Acceso al Sistema de Vendedor

#### Paso 1: Inicia sesión
```
URL: http://localhost:8000/auth/login
- Usa credenciales de usuario existente
```

#### Paso 2: Accede al formulario de vendedor
```
URL: http://localhost:8000/vendedor/solicitar
- Debería ver formulario de solicitud
- Estados posibles:
  - Sin vendedor: Muestra formulario vacío
  - Con solicitud pendiente: Muestra estado PENDIENTE
  - Con solicitud aprobada: Muestra estado APROBADO + botón panel
```

### 2. Solicitar Acceso como Vendedor

#### Paso 1: Completa formulario
```
URL: http://localhost:8000/vendedor/solicitar
- DNI: 12345678-A (o el tuyo)
- Documento: Sube cualquier archivo (PDF, JPG, etc.)
```

#### Paso 2: Envía solicitud
```
- Clic en "Enviar Solicitud"
- Debería ver mensaje de éxito
- Página recarga automáticamente
- Ahora muestra estado PENDIENTE
```

#### Verificar en Base de Datos
```sql
SELECT * FROM vendedor WHERE usuario_id = 1;
-- Debería estar con estado PENDIENTE
```

#### Verificar Documento Almacenado
```bash
ls -la /home/danil/proyectos/UrbanSole/public/uploads/documentos/
-- Debería haber archivo vendedor_*.pdf (o extensión del archivo)
```

### 3. Administrador - Revisar Solicitudes

#### Ver Solicitudes Pendientes
```
URL: http://localhost:8000/admin/vendedores
- Debería listar todas las solicitudes pendientes
- Muestra: usuario, email, DNI, documento, estado, fecha
```

#### Aprobar Vendedor
```
POST: http://localhost:8000/admin/vendedores/{id}/aprobar
- Debería cambiar estado a APROBADO
- Asignar fecha de aprobación
```

#### En Base de Datos
```sql
UPDATE vendedor SET estado = 'APROBADO', fecha_aprobacion = NOW() 
WHERE id = 1;
```

### 4. Subir Zapatillas (Vendedor Aprobado)

#### Paso 1: Accede al panel de vendedor
```
URL: http://localhost:8000/vendedor/panel
- Debería ver información del vendedor aprobado
- Tabla vacía (sin zapatillas aún)
- Botón "Subir Nueva Zapatilla"
```

#### Paso 2: Clic en subir nueva zapatilla
```
URL: http://localhost:8000/zapatillas/crear
- Formulario con campos:
  - Modelo
  - Marca
  - Talla (número)
  - Categoría (select)
  - Precio
  - Stock
```

#### Paso 3: Llena formulario y envía
```
Ejemplo:
- Modelo: Nike Air Max 90
- Marca: Nike
- Talla: 42
- Categoría: Deportivas
- Precio: 129.99
- Stock: 10
```

#### Paso 4: Verifica éxito
```
- Debería ver mensaje de éxito
- Redirige al panel de vendedor
- La zapatilla aparece en la tabla
```

#### En Base de Datos
```sql
SELECT * FROM zapatilla WHERE vendedor_id = 1;
-- Debería mostrar la nueva zapatilla
```

### 5. Ver Zapatillas en Catálogo

#### Accede al catálogo
```
URL: http://localhost:8000/zapatillas
- Las zapatillas se cargan dinámicamente
- Cada zapatilla muestra:
  - Marca
  - Modelo
  - Nombre del Vendedor (NEW!)
  - Precio
  - Botón +
```

#### Verifica que aparezca el vendedor
```
- En la tarjeta debería decir: "Por: [nombre del vendedor]"
- Esto viene de la respuesta JSON de /api/zapatillas
```

#### Test API Directamente
```bash
curl http://localhost:8000/api/zapatillas
# Debería retornar JSON con zapatillas incluyendo vendedor
```

### 6. Editar Zapatilla

#### Accede a editar
```
URL: http://localhost:8000/zapatillas/{id}/editar
- Formulario pre-cargado con datos actuales
- Todos los campos editables
```

#### Edita un campo
```
Ejemplo: Cambiar precio de 129.99 a 99.99
```

#### Guarda cambios
```
POST: http://localhost:8000/zapatillas/{id}/actualizar
- Debería ver mensaje de éxito
- Redirige al panel
- Cambio se refleja en tabla
- Cambio visible en catálogo (recargar)
```

### 7. Eliminar Zapatilla

#### En panel de vendedor
```
URL: http://localhost:8000/vendedor/panel
- Botón "Eliminar" en cada zapatilla
- Confirma eliminación
```

#### Después de eliminar
```
- Zapatilla desaparece de la tabla
- Desaparece del catálogo
- No aparece en API
```

### 8. Seguridad - Verificar Permisos

#### Test 1: Usuario sin login
```
URL: http://localhost:8000/vendedor/solicitar
- Debería redirigir a /auth/login
```

#### Test 2: Vendedor no aprobado intenta subir
```
URL: http://localhost:8000/zapatillas/crear
- Debería redirigir a /vendedor/solicitar
```

#### Test 3: Vendedor intenta editar zapatilla de otro
```
- Cambiar ID en URL a zapatilla de otro vendedor
- Debería redirigir a catálogo
```

#### Test 4: Vendedor intenta eliminar zapatilla de otro (API)
```bash
curl -X POST http://localhost:8000/zapatillas/2/eliminar
# Debería retornar error 403: "No tienes permiso"
```

## 📊 Pruebas en Base de Datos

### Verificar Tablas
```sql
-- Usuario
SELECT id, nombre, email FROM usuario LIMIT 5;

-- Vendedor
SELECT id, usuario_id, dni, estado, fecha_solicitud 
FROM vendedor;

-- Zapatilla
SELECT id, vendedor_id, modelo, marca, precio, stock 
FROM zapatilla;
```

### Relaciones
```sql
-- Zapatillas por vendedor
SELECT 
    u.nombre as vendedor,
    z.modelo,
    z.marca,
    z.precio
FROM zapatilla z
JOIN usuario u ON z.vendedor_id = u.id;
```

## 🔍 Pruebas de API

### Listar Zapatillas
```bash
curl http://localhost:8000/api/zapatillas
```

### Zapatillas por Categoría
```bash
curl "http://localhost:8000/api/zapatillas?categoria_id=1"
```

### Zapatillas por Vendedor
```bash
curl http://localhost:8000/zapatillas/vendedor/1
```

### Solicitudes Pendientes (Admin)
```bash
curl http://localhost:8000/admin/vendedores/solicitudes-pendientes
```

## ⚠️ Errores Comunes y Soluciones

### Error: "No autenticado"
- Verifica que iniciaste sesión
- Cookies activas

### Error: "No eres un vendedor aprobado"
- Vendedor no existe: solicita acceso
- Vendedor pendiente: espera aprobación
- Vendedor rechazado: contacta admin

### Error: "Faltan campos obligatorios"
- Verifica que completaste todos los campos
- Los números no deben estar vacíos

### Error al subir documento
- Verifica ruta: `/public/uploads/documentos/` existe
- Permisos de escritura en la carpeta
- Tipo de archivo permitido

### Zapatilla no aparece en catálogo
- Verifica que el vendedor está aprobado
- Recarga la página (cache del navegador)
- Verifica en BD que zapatilla existe

## 📝 Casos de Uso

### Caso 1: Usuario Nueva a Vendedor
```
1. Usuario se registra (crear si no existe)
2. Accede a /vendedor/solicitar
3. Completa solicitud
4. Admin aprueba
5. Vendedor sube zapatillas
6. Zapatillas aparecen en catálogo
```

### Caso 2: Comprador Busca por Vendedor
```
1. Accede a /zapatillas
2. Ve zapatillas con nombre del vendedor
3. Puede identificar vendedores confiables
4. Compra directamente
```

### Caso 3: Vendedor Actualiza Inventario
```
1. Accede a /vendedor/panel
2. Ve todas sus zapatillas
3. Edita precios o stock
4. Cambios se reflejan automáticamente
```

## 🎯 Pruebas de Integración

### Flujo Completo
```
1. Crear usuario (si no existe) ✓
2. Solicitar ser vendedor ✓
3. Admin aprueba ✓
4. Vendedor sube zapatilla ✓
5. Zapatilla en catálogo ✓
6. Comprador ve zapatilla con vendedor ✓
7. Vendedor edita zapatilla ✓
8. Cambio se refleja ✓
9. Vendedor elimina zapatilla ✓
10. Zapatilla desaparece ✓
```

---

**Sugerencia**: Ejecuta estas pruebas en orden para verificar el flujo completo.
