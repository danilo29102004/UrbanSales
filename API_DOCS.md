# UrbanSole - API Documentation

## Autenticación

### 1. Registro de usuario
```bash
POST /auth/registro
Content-Type: application/json

{
  "nombre": "Juan Pérez",
  "email": "juan@example.com",
  "password": "miPassword123"
}

Response 201:
{
  "mensaje": "Usuario registrado exitosamente",
  "token": "abc123xyz...",
  "usuario": {
    "id": 1,
    "nombre": "Juan Pérez",
    "email": "juan@example.com"
  }
}
```

### 2. Login
```bash
POST /auth/login
Content-Type: application/json

{
  "email": "juan@example.com",
  "password": "miPassword123"
}

Response 200:
{
  "mensaje": "Login exitoso",
  "token": "abc123xyz...",
  "usuario": {
    "id": 1,
    "nombre": "Juan Pérez",
    "email": "juan@example.com"
  }
}
```

### 3. Obtener usuario actual
```bash
GET /auth/me
Authorization: Bearer {token}

Response 200:
{
  "usuario": {
    "id": 1,
    "nombre": "Juan Pérez",
    "email": "juan@example.com",
    "roles": ["ROLE_USER"]
  }
}
```

### 4. Logout
```bash
POST /auth/logout
Authorization: Bearer {token}

Response 200:
{
  "mensaje": "Sesión cerrada"
}
```

---

## Zapatillas

### 1. Listar todas las zapatillas
```bash
GET /zapatillas
GET /zapatillas?categoria_id=1

Response 200:
{
  "zapatillas": [
    {
      "id": 1,
      "modelo": "Air Max",
      "marca": "Nike",
      "talla": "42",
      "precio": "150.00",
      "stock": 10,
      "categoria": "Deportivas",
      "vendedor": "Juan Pérez"
    }
  ]
}
```

### 2. Obtener zapatilla específica
```bash
GET /zapatillas/1

Response 200:
{
  "zapatilla": {
    "id": 1,
    "modelo": "Air Max",
    "marca": "Nike",
    "talla": "42",
    "precio": "150.00",
    "stock": 10,
    "categoria": "Deportivas",
    "vendedor": "Juan Pérez"
  }
}
```

### 3. Crear zapatilla (solo vendedor aprobado)
```bash
POST /zapatillas/crear
Authorization: Bearer {token}
Content-Type: application/json

{
  "vendedor_id": 1,
  "modelo": "Air Max",
  "marca": "Nike",
  "talla": "42",
  "precio": "150.00",
  "stock": 10,
  "categoria_id": 1
}

Response 201:
{
  "mensaje": "Zapatilla creada exitosamente",
  "zapatilla": {
    "id": 1,
    "modelo": "Air Max",
    "marca": "Nike",
    "precio": "150.00"
  }
}
```

### 4. Obtener mis zapatillas (como vendedor)
```bash
GET /zapatillas/vendedor/1

Response 200:
{
  "zapatillas": [...]
}
```

---

## Carrito

### 1. Ver carrito
```bash
GET /carrito/1

Response 200:
{
  "carrito": {
    "id": 1,
    "items": [
      {
        "id": 1,
        "zapatilla": "Air Max",
        "cantidad": 2,
        "precio_momento": "150.00",
        "subtotal": 300
      }
    ],
    "total": "300.00",
    "cantidad_items": 1
  }
}
```

### 2. Agregar item al carrito
```bash
POST /carrito/agregar
Content-Type: application/json

{
  "usuario_id": 1,
  "zapatilla_id": 1,
  "cantidad": 2
}

Response 201:
{
  "mensaje": "Item agregado al carrito",
  "carrito": {
    "total_items": 1,
    "total": "300.00"
  }
}
```

### 3. Eliminar item
```bash
DELETE /carrito/eliminar/1?usuario_id=1

Response 200:
{
  "mensaje": "Item eliminado del carrito",
  "carrito": {
    "total_items": 0,
    "total": "0.00"
  }
}
```

### 4. Vaciar carrito
```bash
POST /carrito/vaciar/1

Response 200:
{
  "mensaje": "Carrito vaciado"
}
```

---

## Pedidos

### 1. Crear pedido (Checkout)
```bash
POST /pedidos/checkout
Content-Type: application/json

{
  "usuario_id": 1,
  "metodo_pago": "tarjeta",
  "direccion_envio": "Calle Principal 123, Apt 4B"
}

Response 201:
{
  "mensaje": "Pedido creado exitosamente",
  "pedido": {
    "id": 1,
    "total": "300.00",
    "fecha": "2026-04-21 23:45:00"
  }
}
```

### 2. Ver mis pedidos
```bash
GET /pedidos/usuario/1

Response 200:
{
  "pedidos": [
    {
      "id": 1,
      "fecha": "2026-04-21 23:45:00",
      "total": "300.00",
      "metodo_pago": "tarjeta",
      "direccion_envio": "Calle Principal 123",
      "items": [...]
    }
  ]
}
```

### 3. Obtener pedido específico
```bash
GET /pedidos/1

Response 200:
{
  "pedido": {
    "id": 1,
    "fecha": "2026-04-21 23:45:00",
    "total": "300.00",
    "metodo_pago": "tarjeta",
    "direccion_envio": "Calle Principal 123",
    "usuario": "Juan Pérez",
    "items": [...]
  }
}
```

### 4. Listar todos los pedidos (admin)
```bash
GET /pedidos

Response 200:
{
  "pedidos": [...]
}
```

---

## Vendedores

### 1. Convertir a vendedor
```bash
POST /convertir-vendedor
Content-Type: application/json

{
  "usuario_id": 1,
  "dni": "12345678A",
  "documento": "/uploads/documento.pdf"
}

Response 201:
{
  "mensaje": "Solicitud de vendedor creada",
  "vendedor": {
    "id": 1,
    "estado": "PENDIENTE",
    "fecha_solicitud": "2026-04-21"
  }
}
```

### 2. Ver solicitudes pendientes (admin)
```bash
GET /admin/vendedores/solicitudes-pendientes

Response 200:
{
  "solicitudes": [
    {
      "id": 1,
      "usuario": "Juan Pérez",
      "email": "juan@example.com",
      "dni": "12345678A",
      "documento": "/uploads/documento.pdf",
      "estado": "PENDIENTE",
      "fecha_solicitud": "2026-04-21 23:45:00"
    }
  ]
}
```

### 3. Aprobar vendedor (admin)
```bash
POST /admin/vendedores/1/aprobar

Response 200:
{
  "mensaje": "Vendedor aprobado exitosamente",
  "vendedor": {
    "id": 1,
    "usuario": "Juan Pérez",
    "estado": "APROBADO",
    "fecha_aprobacion": "2026-04-21 23:45:00"
  }
}
```

### 4. Rechazar vendedor (admin)
```bash
POST /admin/vendedores/1/rechazar

Response 200:
{
  "mensaje": "Vendedor rechazado",
  "vendedor": {
    "id": 1,
    "usuario": "Juan Pérez",
    "estado": "RECHAZADO"
  }
}
```

### 5. Listar vendedores aprobados
```bash
GET /admin/vendedores/aprobados

Response 200:
{
  "vendedores": [...]
}
```

---

## Validaciones

### Campos requeridos y validaciones:

**Registro/Login:**
- `nombre`: 3-255 caracteres
- `email`: formato válido
- `password`: mínimo 6 caracteres

**Zapatilla:**
- `modelo`: obligatorio
- `marca`: obligatorio
- `talla`: obligatorio
- `precio`: decimal positivo
- `stock`: entero positivo
- `categoria_id`: debe existir

**Vendedor:**
- `dni`: formato 12345678A
- `documento`: ruta válida

**Dirección de envío:**
- 5-255 caracteres

---

## Códigos de estado HTTP

- `200`: OK - Solicitud exitosa
- `201`: Created - Recurso creado
- `400`: Bad Request - Datos inválidos
- `401`: Unauthorized - No autenticado
- `404`: Not Found - Recurso no encontrado
- `500`: Internal Server Error - Error del servidor

---

## Autenticación con Token

Todos los endpoints protegidos requieren el header:
```
Authorization: Bearer {token}
```

El token se obtiene del login o registro.
