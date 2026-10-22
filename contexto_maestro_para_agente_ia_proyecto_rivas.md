# SYSTEM PROMPT & CONTEXTO MAESTRO: Rivas Comunicaciones (Laravel 13 & PHP 8.5)

Este documento es la **fuente de verdad técnica y funcional absoluta** del sistema web de **Rivas Comunicaciones**. Cualquier agente de inteligencia artificial o desarrollador que interactúe con este repositorio debe leer, respetar y aplicar obligatoriamente la arquitectura, convenciones y reglas descritas aquí.

---

## 1. DESCRIPCIÓN Y OBJETIVO DEL SISTEMA
* **Organización:** Rivas Comunicaciones (Proveedor de Servicios de Internet / WISP / ISP de Fibra Óptica).
* **Propósito del Sistema:** Plataforma web monolítica e integral construida sobre **Laravel** que gestiona:
  1. **Portal Público:** Catálogo comercial de planes de internet, promociones activas, validación de cobertura, consulta pública de folios y captación de prospectos y mensajes de contacto.
  2. **Reservaciones e Instalaciones:** Solicitud en línea de nuevas conexiones o soporte y conversión automática de reservaciones completadas en clientes activos del padrón.
  3. **Mesa de Ayuda (Soporte Técnico):** Levantamiento de tickets con folio único, categorización de incidencias, seguimiento público y resolución administrativa.
  4. **Módulo Integral de Cobranza y Facturación:** Emisión mensual automatizada de cargos por cliente, recepción de pagos en ventanilla/transferencia con asignación inteligente en cascada (FIFO), anulación con reversión de saldos, generación de recibos imprimibles y control de morosidad.
  5. **Seguridad y Auditoría:** Autenticación protegida con control de roles (`administrador`, `operador`, `cobrador`), soporte para autenticación de dos factores (2FA) y Passkeys (Fortify), rate limiting y bitácora automatizada de cambios con exportación a CSV.

---

## 2. STACK TECNOLÓGICO Y VERSIONES REALES

| Componente | Tecnología / Versión | Notas de Implementación |
| :--- | :--- | :--- |
| **Lenguaje** | **PHP 8.5.4** | Constructor property promotion, tipado estricto en retornos y parámetros. |
| **Framework Backend** | **Laravel 13.35.0** | Arquitectura moderna con `routes/console.php` para scheduled tasks. |
| **Base de Datos** | **MySQL 8.0 / MariaDB** | Motores transaccionales InnoDB, claves foráneas con restricciones estrictas (`restrictOnDelete` / `nullOnDelete`). |
| **Autenticación** | **Laravel Fortify 1.41** | 2FA, Passkeys vía `PasskeyAuthenticatable`, verificación de correo y login administrativo customizado por `nombre_usuario`. |
| **Componentes Reactivos** | **Livewire 4.4.7 + Flux UI 2.20.1** | Empleado en el starter-kit para el panel de usuario y ajustes de perfil (`/settings`). |
| **Frontend Público y Admin** | **Bootstrap 5.3.3 + Icons 1.11.3 (CDN)** | Layouts en Blade: `resources/views/layouts/app.blade.php` y `resources/views/layouts/admin.blade.php`. |
| **Diseño y Tipografía** | **Plus Jakarta Sans + Design Tokens** | Paleta personalizada en variables CSS (`--rc-primary`, `--rc-accent`, `--rc-dark`, `--rc-dark-surface`). |
| **Motor de Animaciones** | **Motion Partial (`motion.blade.php`)** | Curvas cúbicas (`--ease-out`, `--ease-drawer`), microinteracciones activas (`scale(0.97)`), `@starting-style` y soporte para `prefers-reduced-motion`. |
| **Herramientas de Calidad** | **Laravel Pint 1.32 + Larastan 3.13** | Reglas de estilo oficiales de Laravel y análisis estático estricto. |
| **Suite de Pruebas** | **PHPUnit 12.5** | +77 pruebas unitarias y de integración que validan flujos críticos sin regresiones. |

---

## 3. ARQUITECTURA DE CÓDIGO Y PATRONES DE DISEÑO

El sistema sigue una arquitectura por capas limpia que evita controladores obesos y distribuye las responsabilidades:

```
app/
├── Actions/                  # Lógica de negocio transaccional y casos de uso complejos
│   ├── Cobranza/
│   │   ├── GenerarCargosMensuales.php  # Facturación masiva periódica idempotente
│   │   ├── RegistrarPago.php           # Registro transaccional con asignación FIFO
│   │   └── AnularPago.php              # Reversión de saldos y auditoría
│   ├── Fortify/                        # Acciones de registro y reseteo de contraseñas
│   └── Reservaciones/
│       └── CompletarReservacion.php    # Conversión automática de prospecto a cliente
├── Enums/                    # Backed Enums de PHP 8.5 para estados, tipos y roles
│   ├── RolUsuario.php        # Administrador, Operador, Cobrador
│   ├── EstadoCliente.php     # Activo, Suspendido, Inactivo
│   ├── EstadoCargo.php       # Pendiente, Parcial, Pagado, Cancelado
│   ├── TipoCargo.php         # Mensualidad, Instalacion, Reconexion, Otro
│   ├── EstadoPago.php        # Aplicado, Anulado
│   ├── MetodoPago.php        # Efectivo, Transferencia, Deposito, Tarjeta
│   ├── EstadoReservacion.php # Pendiente, Programada, Completada, Cancelada
│   └── EstadoTicket.php      # Abierto, EnProceso, Resuelto, Cerrado
├── Http/
│   ├── Controllers/
│   │   ├── Admin/            # Controladores del panel (Resource Controllers)
│   │   ├── Auth/             # Login administrativo con rate limiter customizado
│   │   └── Public...         # Controladores del portal público de clientes
│   ├── Middleware/
│   │   └── EnsureUserHasRole.php # Middleware 'role' con soporte para roles variádicos
│   └── Requests/             # FormRequests estrictos para toda entrada de datos
│       ├── Admin/            # Validaciones del panel de control
│       └── Public/           # Validaciones del portal público
├── Models/                   # Modelos Eloquent con Scopes, Casts y relaciones
├── Observers/                # Generación automática de Folios (RES-, TKT-, PAG-)
└── Traits/
    └── Auditable.php         # Registro automático de bitácora en eventos Eloquent con diff JSON
```

### 3.1. Patrón Actions (Casos de Uso Transaccionales)
Toda lógica multidominio o que involucre más de un modelo se delega a una **Action** invocada mediante inyección de dependencias:
* **`Cobranza\RegistrarPago`:** Ejecuta `DB::transaction()` con bloqueo pesimista en orden estricto (`Cliente::lockForUpdate()` -> `Cargo::lockForUpdate()`). Aplica el pago en cascada (**FIFO**) sobre cargos vencidos/pendientes. Si existe remanente y se permite saldo a favor, acredita el excedente en `pagos.monto_saldo_favor` y en `clientes.saldo_a_favor`.
* **`Cobranza\AnularPago`:** Bloquea el pago, al cliente y los cargos afectados con `lockForUpdate()`. Si el pago acreditó saldo a favor, verifica que siga disponible y lo revierte. Recalcula el `monto_pagado` de cada cargo sumando de forma determinista únicamente los pagos en estado `'aplicado'`, restaurando los saldos con exactitud matemática.
* **`Cobranza\GenerarCargosMensuales`:** Procesa en lotes (`chunkById(200)`) a clientes activos (o suspendidos si `facturar_suspendidos` está habilitado). Si el cliente posee `saldo_a_favor`, lo descuenta automáticamente del nuevo cargo mensual emitido de forma idempotente.
* **`Reservaciones\CompletarReservacion`:** Al completar una reservación, vincula o da de alta al prospecto en `clientes` con estado `'activo'`. Si es una `'instalacion_nueva'`, emite automáticamente el primer cargo mensual del periodo para el cliente si está configurado en `config/cobranza.php`.

### 3.2. Observers para Generación de Folios
La generación de folios nunca se hace en controladores. Se utilizan los Observers de Eloquent vinculados con el atributo PHP 8 `#[ObservedBy]`:
* `ReservacionObserver`: Genera folios con formato `RES-YYYYMMDD-XXXX` (letras mayúsculas aleatorias), reintentando hasta garantizar unicidad.
* `TicketObserver`: Genera folios con formato `TKT-YYYYMMDD-XXXX`.
* `PagoObserver`: Genera folios con formato `PAG-YYYYMMDD-XXXX`.

### 3.3. Trait `Auditable` (Bitácora Automática con Trazabilidad JSON)
Todos los modelos principales (`Cliente`, `Plan`, `Promocion`, `Reservacion`, `Ticket`, `MensajeContacto`, `Cargo`, `Pago`, `User`) implementan el trait `App\Traits\Auditable`.
* Escucha automáticamente los eventos de ciclo de vida `created`, `updated` y `deleted`.
* Registra en la tabla `bitacoras`: usuario, acción (`creacion`, `actualizacion`, `eliminacion`), tabla afectada, ID del registro, `valores_anteriores` (JSON con valores previos al cambio) y `valores_nuevos` (JSON con valores modificados).
* Identifica ejecuciones en consola asignando `'CLI'` en `ip_origen` cuando no hay petición HTTP activa.
* Descarta automáticamente campos ruidosos o de seguridad (`password`, `remember_token`, `ultimo_acceso`, `two_factor_*`, `updated_at`).
* Encapsula los logs en un bloque `try/catch` para que un fallo en auditoría jamás interrumpa la operación principal.

---

## 4. ROLES, PERMISOS Y MATRIZ DE ACCESO

El sistema implementa 3 roles diferenciados mediante el middleware `role:rol1,rol2` (`App\Http\Middleware\EnsureUserHasRole`):

| Módulo / Acción | Ruta / Permiso | Administrador | Operador | Cobrador |
| :--- | :--- | :---: | :---: | :---: |
| **Dashboard General** | `/admin/dashboard` |  |  |  (Redirige a Cobranza) |
| **Planes y Promociones** | `/admin/planes`, `/admin/promociones` |  |  |  |
| **Padrón de Clientes** | `/admin/clientes` (Ver y Editar) |  |  |  (Solo consulta en Cobranza) |
| **Eliminación de Clientes** | `DELETE /admin/clientes/{cliente}` |  (Solo sin deuda/pagos) |  |  |
| **Reservaciones e Instalaciones** | `/admin/reservaciones` |  |  |  |
| **Tickets de Soporte** | `/admin/tickets` |  |  |  |
| **Mensajes de Contacto** | `/admin/mensajes` |  |  |  |
| **Panel de Cobranza (Listado/Filtros)** | `/admin/cobranza` |  |  |  |
| **Historial Financiero de Cliente** | `/admin/cobranza/clientes/{cliente}` |  |  |  |
| **Registrar Pagos y Recibos** | `POST /admin/cobranza/pagos`, `/recibo` |  |  |  |
| **Crear y Generar Cargos** | `/admin/cobranza/cargos/crear`, `/generar-masivo`|  |  |  |
| **Anular Pagos Registrados** | `POST /admin/cobranza/pagos/{pago}/anular` |  |  |  |
| **Cancelar Cargos Sin Pago** | `POST /admin/cobranza/cargos/{cargo}/cancelar` |  |  |  |
| **Gestión de Usuarios del Sistema**| `/admin/usuarios` (CRUD) |  |  |  |
| **Bitácora y Exportación CSV** | `/admin/bitacoras`, `/export` |  |  |  |

---

## 5. MÓDULOS FUNCIONALES EN DETALLE

### 5.1. Portal Público
* **Landing Page (`/`):** Hero interactivo de alto impacto, buscador rápido de folios de reservación/ticket, grilla de beneficios, selector de planes destacados con cálculo de velocidad simétrica, sección de infraestructura de fibra y llamados a la acción (CTA).
* **Nosotros (`/nosotros`):** Historia, misión, valores y pilares tecnológicos del ISP local.
* **Cobertura (`/cobertura`):** Mapa interactivo y zonas de cobertura activas en el municipio.
* **Catálogo de Planes (`/planes`):** Desglose completo de velocidades, precios e inclusión de promociones.
* **Promociones Vigentes (`/promociones`):** Listado dinámico de ofertas filtradas por fecha de validez y estado activo.
* **Contacto (`/contacto`):** Formulario directo que persiste en `mensajes_contacto` y notifica al panel administrativo.

### 5.2. Reservaciones y Alta Automática de Clientes
* Un prospecto completa el formulario en `/reservaciones/crear` indicando tipo de servicio (`instalacion_nueva`, `visita_tecnica`, `atencion_personalizada`), plan deseado, dirección y fecha solicitada.
* El sistema le expide de inmediato un **Folio Único** para consulta pública en `/reservaciones/{folio}`.
* Desde el panel administrativo (`/admin/reservaciones`), los operadores gestionan los estados (`pendiente` -> `programada` -> `completada` / `cancelada`).
* **Regla de Negocio:** Al marcar una reservación como `'completada'`, la acción `CompletarReservacion` registra automáticamente al solicitante en la tabla `clientes`, asignándole su plan contratado y estado activo.

### 5.3. Mesa de Ayuda y Tickets Digitales
* Los clientes pueden reportar fallas en `/soporte/ticket/crear` seleccionando su cuenta y la categoría (`falla_servicio`, `soporte_tecnico`, `consulta`, `cambio_plan`, `cancelacion`).
* El sistema emite un folio con el formato `TKT-YYYYMMDD-XXXX`.
* El cliente puede auditar el progreso de su reporte en `/soporte/ticket/{folio}` sin necesidad de iniciar sesión.
* Los operadores gestionan el ticket (`abierto` -> `en_proceso` -> `resuelto` -> `cerrado`), agregan observaciones técnicas y sellan la fecha de resolución.

### 5.4. Módulo de Cobranza y Cuentas por Cobrar
* **Generación Masiva de Mensualidades:**
  * Comando Artisan: `php artisan cobranza:generar-cargos {--periodo=YYYY-MM}`.
  * Automatizado en el programador de tareas para ejecutarse el primer día de cada mes a las 00:10 hrs.
  * Determina la fecha de vencimiento según el archivo de configuración `config/cobranza.php` (por defecto, día 10 de cada mes).
* **Registro de Pagos en Cascada (FIFO):**
  * Se registra el pago especificando método (`efectivo`, `transferencia`, `deposito`, `tarjeta`) y referencia.
  * El sistema liquida primero los cargos con fecha de vencimiento más antigua. Si el monto cubre parcialmente un cargo, este queda como `'parcial'` y el saldo restante como `'pendiente'`.
  * Generación inmediata de recibo en formato imprimible con diseño profesional.
* **Anulación Segura de Pagos:**
  * Exclusivo para administradores. Requiere justificación escrita obligatoria (`motivo_anulacion`).
  * Los cargos impactados regresan a su estado de adeudo original de manera automática e indivisible.
* **Control de Morosidad:**
  * Filtros dinámicos en el panel: `todos`, `con_adeudo`, `morosos` (clientes con cargos vencidos sin liquidar) y `al_corriente`.
  * KPIs en tiempo real de saldo total por cobrar y recaudación del mes en curso.

### 5.5. Padrón de Clientes y Reglas de Integridad
* Búsqueda por nombre, teléfono, dirección o correo.
* **Regla de Integridad Referencial:** Un cliente no puede ser eliminado de la base de datos si cuenta con historial de cargos o pagos registrados en el sistema. El sistema bloquea la eliminación y orienta al usuario a cambiar su estado a `'inactivo'`.

### 5.6. Seguridad, Usuarios y Auditoría
* **Inicio de Sesión Administrativo:** En `/admin/login` con campo `nombre_usuario`, protegido por Rate Limiter (5 intentos por minuto) y registro de accesos en la bitácora.
* **Protección de Cuentas:** No se permite eliminar la propia cuenta activa ni al último administrador del sistema.
* **Bitácora con Exportación:** El administrador puede filtrar eventos por usuario, acción, modelo o fecha, y descargar un reporte en CSV transmitido por chunks con codificación UTF-8 BOM.

---

## 6. ESQUEMA DE BASE DE DATOS Y MODELOS ELOQUENT

Todas las migraciones se encuentran en `database/migrations/`. Los nombres de las tablas y modelos respetan estrictamente el idioma español para el dominio de negocio:

```
[planes] <──1:N── [clientes] <──1:N── [tickets]
   │                 │   │
   └──1:N──┐         │   └──1:N── [cargos] <──M:N── [pagos] (via aplicaciones_pago)
           ▼         │               ▲
    [reservaciones] ─┘               │
                               [usuarios] ──1:N── [bitacoras]
```

### 6.1. Tabla `usuarios` (`App\Models\User`)
* `id` (bigint, PK)
* `nombre_usuario` (varchar 50, unique)
* `nombre_completo` (varchar 150)
* `name` (varchar 255, mantenido sincronizado para compatibilidad con Laravel Fortify)
* `email` (varchar 255, nullable, unique)
* `email_verified_at` (timestamp, nullable)
* `password` (varchar 255, bcrypt/argon2)
* `rol` (`enum('administrador', 'operador', 'cobrador')`, default `'operador'`)
* `estado` (`enum('activo', 'inactivo')`, default `'activo'`)
* `ultimo_acceso` (timestamp, nullable)
* `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at` (2FA Fortify)
* `remember_token`, `timestamps`

### 6.2. Tabla `planes` (`App\Models\Plan`)
* `id` (bigint, PK)
* `nombre` (varchar 100, unique)
* `velocidad_descarga` (int, Megabits por segundo)
* `velocidad_carga` (int, Megabits por segundo)
* `precio_mensual` (decimal 8,2)
* `descripcion` (text, nullable)
* `estado` (`enum('activo', 'inactivo')`, default `'activo'`)
* `timestamps`
* *Relaciones:* `hasMany(Cliente::class)`, `hasMany(Reservacion::class)`, `belongsToMany(Promocion::class, 'promocion_plan')`.

### 6.3. Tabla `promociones` (`App\Models\Promocion`) y Pivot `promocion_plan`
* `id` (bigint, PK)
* `titulo` (varchar 200)
* `descripcion` (text)
* `fecha_inicio` (date)
* `fecha_vencimiento` (date)
* `estado` (`enum('activa', 'inactiva')`, default `'activa'`)
* `timestamps`
* *Pivot `promocion_plan`:* `promocion_id`, `plan_id` (PK compuesta, `cascadeOnDelete`).

### 6.4. Tabla `clientes` (`App\Models\Cliente`)
* `id` (bigint, PK)
* `nombre_completo` (varchar 200)
* `direccion` (varchar 300)
* `telefono` (varchar 15)
* `correo_electronico` (varchar 150, nullable)
* `plan_id` (foreignId -> `planes`, nullable, `nullOnDelete`)
* `fecha_alta` (date, default `CURRENT_DATE`)
* `estado` (`enum('activo', 'suspendido', 'inactivo')`, default `'activo'`)
* `saldo_a_favor` (decimal 10,2, default `0.00` - remanentes acreditados por pagos excedentes)
* `notas` (text, nullable)
* `serie_ont` (varchar 100, nullable, index - Número de serie o MAC de la ONT/Módem)
* `usuario_pppoe` (varchar 100, nullable, index - Usuario PPPoE o IP asignada)
* `coordenadas_gps` (varchar 100, nullable - Latitud, Longitud)
* `timestamps`
* *Relaciones:* `belongsTo(Plan::class)`, `hasMany(Ticket::class)`, `hasMany(Cargo::class)`, `hasMany(Pago::class)`.

### 6.5. Tabla `reservaciones` (`App\Models\Reservacion`)
* `id` (bigint, PK)
* `folio` (varchar 20, unique) — Generado por `ReservacionObserver`: `RES-YYYYMMDD-XXXX`
* `nombre_solicitante` (varchar 200)
* `telefono` (varchar 15)
* `direccion` (varchar 300)
* `correo_electronico` (varchar 150, nullable)
* `tipo_servicio` (`enum('instalacion_nueva', 'visita_tecnica', 'atencion_personalizada')`)
* `plan_id` (foreignId -> `planes`, nullable, `nullOnDelete`)
* `cliente_id` (foreignId -> `clientes`, nullable, `nullOnDelete`)
* `fecha_solicitada` (date, nullable)
* `observaciones` (text, nullable)
* `estado` (`enum('pendiente', 'programada', 'completada', 'cancelada')`, default `'pendiente'`)
* `timestamps`

### 6.6. Tabla `tickets` (`App\Models\Ticket`)
* `id` (bigint, PK)
* `folio` (varchar 20, unique) — Generado por `TicketObserver`: `TKT-YYYYMMDD-XXXX`
* `cliente_id` (foreignId -> `clientes`, `cascadeOnDelete`)
* `categoria` (`enum('falla_servicio', 'soporte_tecnico', 'consulta', 'cambio_plan', 'cancelacion')`)
* `descripcion` (text)
* `estado` (`enum('abierto', 'en_proceso', 'resuelto', 'cerrado')`, default `'abierto'`)
* `fecha_resolucion` (timestamp, nullable)
* `observaciones_admin` (text, nullable)
* `timestamps`

### 6.7. Tabla `mensajes_contacto` (`App\Models\MensajeContacto`)
* `id` (bigint, PK)
* `nombre` (varchar 150)
* `correo_electronico` (varchar 150)
* `telefono` (varchar 15, nullable)
* `asunto` (varchar 200)
* `mensaje` (text)
* `leido` (boolean, default `false`)
* `timestamps`

### 6.8. Tabla `cargos` (`App\Models\Cargo`)
* `id` (bigint, PK)
* `cliente_id` (foreignId -> `clientes`, `restrictOnDelete`)
* `plan_id` (foreignId -> `planes`, nullable, `nullOnDelete`)
* `tipo` (`enum('mensualidad', 'instalacion', 'reconexion', 'otro')`, default `'mensualidad'`)
* `periodo` (varchar 7, nullable - Formato `YYYY-MM`)
* `concepto` (varchar 200)
* `monto` (decimal 10,2)
* `monto_pagado` (decimal 10,2, default `0.00`)
* `fecha_vencimiento` (date)
* `estado` (`enum('pendiente', 'parcial', 'pagado', 'cancelado')`, default `'pendiente'`)
* `timestamps`
* *Índices de Rendimiento:* `(cliente_id, periodo)`, `(estado, fecha_vencimiento)`.

### 6.9. Tabla `pagos` (`App\Models\Pago`)
* `id` (bigint, PK)
* `folio` (varchar 20, unique) — Generado por `PagoObserver`: `PAG-YYYYMMDD-XXXX`
* `cliente_id` (foreignId -> `clientes`, `restrictOnDelete`)
* `usuario_id` (foreignId -> `usuarios`, nullable, `nullOnDelete` - Cobrador que atendió)
* `monto` (decimal 10,2)
* `monto_saldo_favor` (decimal 10,2, default `0.00` - remanente que se acreditó como saldo a favor)
* `metodo_pago` (`enum('efectivo', 'transferencia', 'deposito', 'tarjeta')`, default `'efectivo'`)
* `referencia` (varchar 100, nullable)
* `fecha_pago` (datetime)
* `notas` (text, nullable)
* `estado` (`enum('aplicado', 'anulado')`, default `'aplicado'`)
* `motivo_anulacion` (varchar 300, nullable)
* `timestamps`

### 6.10. Tabla `aplicaciones_pago` (Pivot Transaccional de Pagos y Cargos)
* `id` (bigint, PK)
* `pago_id` (foreignId -> `pagos`, `cascadeOnDelete`)
* `cargo_id` (foreignId -> `cargos`, `restrictOnDelete`)
* `monto_aplicado` (decimal 10,2)
* `timestamps`
* *Restricción:* Clave única compuesta en `['pago_id', 'cargo_id']`.

### 6.11. Tabla `bitacoras` (`App\Models\Bitacora`)
* `id` (bigint, PK)
* `usuario_id` (foreignId -> `usuarios`, nullable, `nullOnDelete`)
* `accion` (varchar 50) — `creacion`, `actualizacion`, `eliminacion`, `acceso`, `cierre_sesion`
* `descripcion` (varchar 300)
* `tabla_afectada` (varchar 50, nullable)
* `registro_id` (unsignedBigInteger, nullable)
* `valores_anteriores` (json, nullable - estado previo de los atributos modificados)
* `valores_nuevos` (json, nullable - valores resultantes de la operación)
* `ip_origen` (varchar 45, nullable - IP remota o 'CLI')
* `timestamps`

---

## 7. MAPA COMPLETO DE RUTAS

### 7.1. Rutas Públicas (Sin Autenticación)
| Método | URI | Nombre de Ruta | Controlador y Acción |
| :--- | :--- | :--- | :--- |
| `GET` | `/` | `home` | `HomeController@index` |
| `GET` | `/nosotros` | `nosotros` | `HomeController@nosotros` |
| `GET` | `/cobertura` | `cobertura` | `HomeController@cobertura` |
| `GET` | `/planes` | `planes.index` | `PublicPlanController@index` |
| `GET` | `/promociones` | `promociones.index` | `PublicPromocionController@index` |
| `GET` | `/reservaciones/crear` | `reservaciones.crear` | `PublicReservacionController@create` |
| `POST`| `/reservaciones` | `reservaciones.store` | `PublicReservacionController@store` |
| `GET` | `/reservaciones/{folio}` | `reservaciones.consultar`| `PublicReservacionController@show` |
| `GET` | `/soporte/ticket/crear` | `soporte.ticket.crear` | `PublicTicketController@create` |
| `POST`| `/soporte/ticket` | `soporte.ticket.store` | `PublicTicketController@store` |
| `GET` | `/soporte/ticket/{folio}` | `soporte.ticket.consultar`| `PublicTicketController@show` |
| `GET` | `/contacto` | `contacto` | `ContactoController@index` |
| `POST`| `/contacto` | `contacto.enviar` | `ContactoController@store` |

### 7.2. Autenticación Administrativa
| Método | URI | Nombre de Ruta | Controlador y Acción |
| :--- | :--- | :--- | :--- |
| `GET` | `/admin/login` | `admin.login` | `Auth\AdminLoginController@showLoginForm` |
| `POST`| `/admin/login` | `admin.login.submit`| `Auth\AdminLoginController@login` (Rate limited) |
| `POST`| `/admin/logout` | `admin.logout` | `Auth\AdminLoginController@logout` |

### 7.3. Panel Administrativo Protegido (`/admin`, Middleware: `auth`)
* **Área General (`role:administrador,operador`):**
  * `GET /admin/dashboard` (`admin.dashboard`) -> `DashboardController@index`
  * Resource CRUD: `/admin/planes` (`admin.planes.*`)
  * Resource CRUD: `/admin/promociones` (`admin.promociones.*`)
  * Resource CRUD: `/admin/clientes` (`admin.clientes.*` — destroy restringido)
  * `/admin/reservaciones` (`index`, `show`, `edit`, `update`)
  * Resource CRUD: `/admin/tickets` (`admin.tickets.*`)
  * `/admin/mensajes` (`index`, `show`, `update` [marcar leído], `destroy`)
* **Área de Cobranza (`role:administrador,cobrador`):**
  * `GET /admin/cobranza` (`admin.cobranza.index`) -> `CobranzaController@index`
  * `GET /admin/cobranza/clientes/{cliente}` (`admin.cobranza.show`) -> `CobranzaController@show`
  * `POST /admin/cobranza/pagos` (`admin.cobranza.pagos.store`) -> `CobranzaController@storePago`
  * `GET /admin/cobranza/pagos/{pago}/recibo` (`admin.cobranza.pagos.recibo`) -> `CobranzaController@reciboPago`
  * `GET /admin/cobranza/cargos/crear` (`admin.cobranza.cargos.create`) -> `CobranzaController@createCargo`
  * `POST /admin/cobranza/cargos` (`admin.cobranza.cargos.store`) -> `CobranzaController@storeCargo`
  * `POST /admin/cobranza/cargos/generar-masivo` (`admin.cobranza.cargos.generar-masivo`) -> `CobranzaController@generarCargosMasivos`
* **Operaciones Exclusivas de Administrador (`role:administrador`):**
  * `POST /admin/cobranza/pagos/{pago}/anular` (`admin.cobranza.pagos.anular`)
  * `POST /admin/cobranza/cargos/{cargo}/cancelar` (`admin.cobranza.cargos.cancelar`)
  * Resource CRUD: `/admin/usuarios` (`admin.usuarios.*` — excepto show)
  * `GET /admin/bitacoras` (`admin.bitacoras.index`)
  * `GET /admin/bitacoras/exportar` (`admin.bitacoras.export`)

---

## 8. SISTEMA DE DISEÑO, FRONTEND Y MOTION

### 8.1. Tokens de Identidad Visual
Configurados en `resources/views/layouts/app.blade.php` y `resources/views/layouts/admin.blade.php`:
```css
:root {
  --rc-primary: #0284c7;       /* Azul ISP brillante para botones y acentos de marca */
  --rc-primary-hover: #0369a1; /* Estado hover accesible */
  --rc-accent: #38bdf8;        /* Resplandor y detalles secundarios */
  --rc-dark: #0b1329;          /* Fondos oscuros de navegación y pie de página */
  --rc-dark-surface: #111e3b;  /* Superficies elevadas en modo oscuro */
  --rc-border-subtle: #e2e8f0; /* Bordes suaves para tarjetas y tablas */
}
```
* **Tipografía:** Se emplea **Plus Jakarta Sans** de Google Fonts para toda la interfaz, proporcionando legibilidad y estética contemporánea.

### 8.2. Sistema de Movimiento (`motion.blade.php`)
Ubicado en `resources/views/partials/motion.blade.php` e incluido transversalmente:
* **Curvas de Aceleración:**
  * `--ease-out: cubic-bezier(0.23, 1, 0.32, 1)` (Movimientos naturales de entrada).
  * `--ease-drawer: cubic-bezier(0.32, 0.72, 0, 1)` (Apertura de cajones laterales y menús móviles).
* **Microinteracciones Físicas:**
  * Efecto de presión en botones: `.btn:active { transform: scale(0.97); }`.
  * Transición de despliegue en dropdowns y modales con soporte nativo de `@starting-style`.
  * Revelación progresiva con la clase `.reveal-stagger`.
* **Accesibilidad:** Media query `@media (prefers-reduced-motion: reduce)` activo que desactiva transiciones y transformaciones para usuarios con sensibilidad al movimiento.

---

## 9. COMANDOS ARTISAN Y PROGRAMACIÓN DE TAREAS (CRON)

### 9.1. Comandos del Negocio
```bash
# Facturación del mes actual para todos los clientes activos con plan:
php artisan cobranza:generar-cargos

# Facturación manual de un periodo específico (ej. mayo 2026):
php artisan cobranza:generar-cargos --periodo=2026-05
```

### 9.2. Programación Automática (`routes/console.php`)
```php
use Illuminate\Support\Facades\Schedule;

// Emite automáticamente los cargos de mensualidad el día 1 de cada mes a las 00:10 hrs:
Schedule::command('cobranza:generar-cargos')
    ->monthlyOn(1, '00:10')
    ->withoutOverlapping()
    ->runInBackground();
```
*En el servidor de producción, se debe mantener activo el cron del sistema llamando a `* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1`.*

---

## 10. CALIDAD DE CÓDIGO Y SUITE DE PRUEBAS

### 10.1. Comandos de Verificación
* **Ejecutar Suite Completa:** `php artisan test --compact` (89 pruebas exitosas, 0 fallos).
* **Ejecutar Pruebas Específicas:**
  * `php artisan test --filter=CobranzaSaldoAFavorTest`
  * `php artisan test --filter=BitacoraJsonDiffTest`
  * `php artisan test --filter=SeguridadEndpointsPublicosTest`
  * `php artisan test --filter=ClienteRedWispTest`
  * `php artisan test --filter=CobranzaTest`
  * `php artisan test --filter=AdminPanelTest`
  * `php artisan test --filter=FolioAndAuditObserverTest`
* **Formateo de Código Oficial:** `vendor/bin/pint --dirty --format agent` (Ejecutar siempre antes de concluir cambios en PHP).
* **Análisis Estático:** `vendor/bin/phpstan analyse`

### 10.2. Cuentas de Acceso de Prueba (Seeder)
Generadas mediante `php artisan db:seed`:
* **Administrador:** Usuario: `admin` | Contraseña: `admin123`
* **Operador Técnico:** Usuario: `operador` | Contraseña: `operador123`
* **Cobrador en Ventanilla:** Usuario: `cobrador` | Contraseña: `cobrador123`

---

## 11. DIRECTRICES Y REGLAS ESTRICTAS PARA AGENTES IA

Cualquier agente de IA que genere o modifique código en este proyecto **DEBE acatar obligatoriamente las siguientes directrices**:

1. **Idioma y Nomenclatura del Dominio:**
   * Las entidades del negocio, tablas, columnas, rutas y variables deben escribirse en **idioma español** (`cargos`, `pagos`, `planes`, `reservaciones`, `folio`, `monto_pagado`).
   * Los términos de infraestructura y palabras clave del framework se mantienen en inglés (`User`, `Controller`, `Observer`, `Migration`, `Request`, `StorePagoRequest`).
2. **Generación de Folios Exclusiva en Observers:**
   * Jamás generes folios dentro de los controladores ni en las vistas.
   * Utiliza o extiende los Observers con `#[ObservedBy]` en el Modelo correspondiente.
3. **Casos de Uso Complejos en Actions:**
   * Cualquier lógica que involucre transacciones de base de datos, bloqueo pesimista o coordinación entre varios modelos debe encapsularse en una clase bajo `app/Actions/`.
4. **Validación Estricta en FormRequests:**
   * Prohibido validar directamente en los controladores mediante `$request->validate()`.
   * Crea siempre una clase `FormRequest` dedicada con mensajes claros en español.
5. **Consultas a Base de Datos y Rendimiento:**
   * Prohibido el uso de SQL crudo o concatenación de variables en consultas.
   * Evita problemas de rendimiento N+1 utilizando siempre **Eager Loading** explícito (`with(['plan', 'cliente'])`) al consultar listados.
6. **Seguridad y Auditoría:**
   * Todo modelo representativo de datos de negocio debe implementar `App\Traits\Auditable`.
   * Las vistas Blade deben utilizar `{{ $variable }}` para sanitización XSS automática.
   * Aplica siempre el middleware `role` para proteger rutas administrativas según la matriz de acceso.
7. **Diseño Visual e Integridad del Frontend:**
   * Respeta los layouts existentes (`layouts/app.blade.php` y `layouts/admin.blade.php`).
   * No agregues librerías CSS pesadas adicionales sin consultar. El panel y el portal público utilizan Bootstrap 5.3 + Motion Tokens; el starter-kit `/settings` utiliza Tailwind 4 + Livewire Flux.
8. **Formateo y Tests:**
   * Tras modificar archivos PHP, corre `vendor/bin/pint --dirty --format agent`.
   * Si introduces una nueva funcionalidad o alteras una regla de negocio, agrega o actualiza su respectiva prueba en `tests/Feature/`.