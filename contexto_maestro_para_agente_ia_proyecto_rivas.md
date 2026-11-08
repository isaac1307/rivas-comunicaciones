# SYSTEM PROMPT & CONTEXTO MAESTRO: Rivas Comunicaciones (Laravel 13 & PHP 8.5)

Este documento es la **fuente de verdad técnica y funcional absoluta** del sistema web de **Rivas Comunicaciones**. Cualquier agente de inteligencia artificial o desarrollador que interactúe con este repositorio debe leer, respetar y aplicar obligatoriamente la arquitectura, convenciones y reglas descritas aquí.

---

## 1. DESCRIPCIÓN Y OBJETIVO DEL SISTEMA
* **Organización:** Rivas Comunicaciones (Proveedor de Servicios de Internet / WISP / ISP de Fibra Óptica e Inalámbrico).
* **Propósito del Sistema:** Plataforma web monolítica e integral construida sobre **Laravel 13** que gestiona:
  1. **Portal Público:** Catálogo comercial de planes de internet, promociones vigentes, validación de cobertura, consulta pública de folios con enmascaramiento de datos sensibles (PII) y captación de prospectos y mensajes de contacto con protección anti-abuso (`throttle`).
  2. **Reservaciones e Instalaciones:** Solicitud en línea de nuevas conexiones o soporte y conversión automática de reservaciones completadas en clientes activos del padrón, con emisión opcional de su primera mensualidad.
  3. **Mesa de Ayuda (Soporte Técnico):** Levantamiento público de tickets validado por número telefónico de contrato (sin exponer el padrón de clientes), asignación de folio único, categorización de incidencias, seguimiento público y resolución administrativa.
  4. **Módulo Integral de Cobranza, Facturación y Saldo a Favor:** Emisión mensual automatizada e idempotente de cargos por cliente, descuento automático de `saldo_a_favor`, recepción de pagos en ventanilla/transferencia con asignación inteligente en cascada (**FIFO**), acreditación de excedentes, anulación con reversión determinista de saldos, generación de recibos imprimibles y control de morosidad.
  5. **Automatización de Red Multi-Router (MikroTik RouterOS v7 REST API):** Gestión de múltiples nodos/routers con contraseñas encriptadas (`Crypt`), aprovisionamiento automático de ancho de banda por cliente (`Simple Queues`), suspensión automática por morosidad (`Firewall Address-List: morosos`) y reconexión inmediata al liquidar adeudos vencidos mediante trabajos asíncronos en cola (`ShouldQueue`).
  6. **Seguridad y Auditoría Forense:** Autenticación protegida con control de roles (`administrador`, `operador`, `cobrador`), soporte para autenticación de dos factores (2FA TOTP) y Passkeys WebAuthn (Fortify), rate limiting y bitácora automatizada de cambios con diff JSON (`valores_anteriores` / `valores_nuevos`) y exportación a CSV.

---

## 2. STACK TECNOLÓGICO Y VERSIONES REALES

| Componente | Tecnología / Versión | Notas de Implementación |
| :--- | :--- | :--- |
| **Lenguaje** | **PHP 8.5.4** | Constructor property promotion, tipado estricto en retornos y parámetros, Backed Enums. |
| **Framework Backend** | **Laravel 13.35.0** | Arquitectura moderna con `routes/console.php`, Queued Jobs y `Http` client con Basic Auth. |
| **Base de Datos** | **MySQL 8.0 / MariaDB / SQLite** | Motores transaccionales con bloqueo pesimista (`lockForUpdate`) y claves foráneas estrictas (`restrictOnDelete` / `nullOnDelete`). |
| **Automatización de Red** | **MikroTik RouterOS v7 REST API** | Integración asíncrona (`app/Jobs/Red/*`) vía HTTP Basic Auth contra `/rest/queue/simple` y `/rest/ip/firewall/address-list`. |
| **Autenticación** | **Laravel Fortify 1.41** | 2FA, Passkeys vía `PasskeyAuthenticatable`, verificación de correo y login administrativo customizado por `nombre_usuario`. |
| **Componentes Reactivos** | **Livewire 4.4.7 + Flux UI 2.20.1** | Empleado en el starter-kit para el panel de usuario y ajustes de perfil y seguridad (`/settings`). |
| **Frontend Público y Admin** | **Bootstrap 5.3.3 + Icons 1.11.3 (CDN)** | Layouts en Blade: `resources/views/layouts/app.blade.php` y `resources/views/layouts/admin.blade.php`. |
| **Diseño y Tipografía** | **Plus Jakarta Sans + Design Tokens** | Paleta personalizada en variables CSS (`--rc-primary`, `--rc-accent`, `--rc-dark`, `--rc-dark-surface`). |
| **Motor de Animaciones** | **Motion Partial (`motion.blade.php`)** | Curvas cúbicas (`--ease-out`, `--ease-drawer`), microinteracciones activas (`scale(0.97)`), `@starting-style` y soporte para `prefers-reduced-motion`. |
| **Herramientas de Calidad** | **Laravel Pint 1.32 + Larastan 3.13** | Reglas de estilo oficiales de Laravel y análisis estático estricto. |
| **Suite de Pruebas** | **PHPUnit 12.5** | **119 pruebas** (117 exitosas, 2 omitidas, **388+ aserciones**) que validan todos los flujos críticos sin regresiones. |

---

## 3. ARQUITECTURA DE CÓDIGO Y PATRONES DE DISEÑO

El sistema sigue una arquitectura por capas limpia que evita controladores obesos y distribuye las responsabilidades:

```
app/
├── Actions/                  # Lógica de negocio transaccional y casos de uso complejos
│   ├── Cobranza/
│   │   ├── GenerarCargosMensuales.php  # Facturación masiva periódica idempotente + descuento de saldo a favor en centavos
│   │   ├── RegistrarPago.php           # Registro transaccional FIFO en centavos + saldo a favor + reconexión MikroTik
│   │   └── AnularPago.php              # Reversión determinista de saldos + suspensión MikroTik si queda moroso
│   ├── Fortify/                        # Acciones de registro y reseteo de contraseñas
│   ├── Red/
│   │   ├── MikrotikRestClient.php          # Cliente HTTP centralizado (HTTP/HTTPS TLS) para RouterOS v7 REST API
│   │   ├── GestionarEstadoServicioCliente.php # Corte y reconexión inmediata de servicio de cliente
│   │   ├── SuspenderClientesMorosos.php    # Corte automatizado en lote para clientes con cargos vencidos
│   │   ├── SincronizarPlanesMikrotik.php   # Aprovisionamiento de perfiles PPP, PCQ, colas y firewall
│   │   └── GenerarScriptMikrotik.php       # Generador de script .rsc para terminal de RouterOS
│   └── Reservaciones/
│       └── CompletarReservacion.php    # Conversión automática de prospecto a cliente + primer cargo mensual
├── Console/
│   └── Commands/
│       ├── GenerarCargosMensualesCommand.php   # Comando Artisan cobranza:generar-cargos
│       └── SuspenderClientesMorososCommand.php # Comando Artisan red:suspender-morosos
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
│   │   ├── Admin/            # Controladores del panel (Auth, Dashboard, Clientes, Cobranza, Cargo, Pago, Bitacora, etc.)
│   │   └── Public...         # Controladores del portal público (Home, Contacto, PublicPlan, PublicPromocion, PublicReservacion, PublicTicket)
│   ├── Middleware/
│   │   └── EnsureUserHasRole.php # Middleware 'role' con soporte para roles variádicos
│   └── Requests/             # FormRequests estrictos para toda entrada de datos
│       ├── Admin/            # Validaciones del panel de control
│       └── Store*.php        # Validaciones del portal público (Contacto, Reservación, Ticket con validación telefónica)
├── Jobs/
│   └── Red/                  # Trabajos en cola (ShouldQueue) para sincronización con MikroTik RouterOS v7 REST API
│       ├── AsignarPlanRedJob.php           # Crea (PUT) o actualiza (PATCH) Simple Queue con límites de velocidad
│       ├── ReconectarServicioRedJob.php    # Busca (GET) y elimina (DELETE) IP del cliente de address-list 'morosos'
│       ├── SincronizarPlanesRouterJob.php  # Aprovisiona planes, perfiles PPP, colas y reglas de firewall al crear o sincronizar nodos
│       └── SuspenderServicioRedJob.php     # Agrega (PUT) IP del cliente a address-list 'morosos'
├── Models/                   # Modelos Eloquent con Scopes, Casts, Encriptación y Relaciones
│   └── Router.php            # Nodo MikroTik con encriptación transparente de api_password
├── Observers/                # Generación automática de Folios (RES-, TKT-, PAG-)
└── Traits/
    └── Auditable.php         # Registro automático de bitácora en eventos Eloquent con diff JSON
```

### 3.1. Patrón Actions (Casos de Uso Transaccionales)
Toda lógica multidominio o que involucre más de un modelo se delega a una **Action** invocada mediante inyección de dependencias:
* **`App\Actions\Red\GenerarScriptMikrotik`:** Genera un script ejecutable e idempotente para la consola de RouterOS v7/v6 (WinBox Terminal / SSH / `.rsc`), con servicios REST/API, reglas de morosos, perfiles PPP, tipos PCQ y colas simples padre.
* **`App\Actions\Red\SincronizarPlanesMikrotik`:** Se comunica directamente con la REST API de MikroTik para crear/actualizar perfiles PPP, tipos de cola y reglas de corte de clientes morosos.
* **`Cobranza\RegistrarPago`:** Ejecuta `DB::transaction()` con bloqueo pesimista en orden estricto (`Cliente::lockForUpdate()` -> `Cargo::lockForUpdate()`). Aplica el pago en cascada (**FIFO**) sobre cargos pendientes ordenados por `fecha_vencimiento` ascendente. Si existe remanente y se permite saldo a favor (`permitir_saldo_a_favor`), acredita el excedente en `pagos.monto_saldo_favor` y en `clientes.saldo_a_favor`. Al finalizar la transacción, si el cliente ya no tiene cargos vencidos (`! $cliente->cargos()->vencidos()->exists()`) y cuenta con `ip_asignada` y router activo, despacha **`ReconectarServicioRedJob`**.
* **`Cobranza\AnularPago`:** Bloquea el pago, al cliente y los cargos afectados con `lockForUpdate()`. Si el pago acreditó saldo a favor, verifica que el cliente aún disponga de ese saldo y lo revierte. Marca el pago como `'anulado'` y recalcula el `monto_pagado` de cada cargo sumando de forma determinista únicamente los pagos en estado `'aplicado'` en `aplicaciones_pago`. Al terminar la transacción, si el cliente queda con cargos vencidos (`$clienteAfectado->cargos()->vencidos()->exists()`) y tiene `ip_asignada` y router activo, despacha **`SuspenderServicioRedJob`**.
* **`Cobranza\GenerarCargosMensuales`:** Procesa en lotes (`chunkById(200)`) a clientes activos (o suspendidos si `cobranza.facturar_suspendidos` está habilitado). Si el cliente posee `saldo_a_favor > 0`, lo descuenta automáticamente del nuevo cargo mensual emitido de forma idempotente, ajustando el estado del cargo a `'pagado'` o `'parcial'`.
* **`Reservaciones\CompletarReservacion`:** Al completar una reservación, busca coincidencias por `cliente_id`, `telefono` o `correo_electronico` y vincula o da de alta al prospecto en `clientes` con estado `'activo'`. Si es una `'instalacion_nueva'` y `cobranza.generar_cargo_al_completar_reservacion` está activo, emite automáticamente el primer cargo mensual del periodo para el cliente.

### 3.2. Observers para Generación de Folios
La generación de folios nunca se hace en controladores. Se utilizan los Observers de Eloquent vinculados con el atributo PHP 8 `#[ObservedBy]`:
* `ReservacionObserver`: Genera folios con formato `RES-YYYYMMDD-XXXX` (letras mayúsculas aleatorias), reintentando hasta garantizar unicidad.
* `TicketObserver`: Genera folios con formato `TKT-YYYYMMDD-XXXX`.
* `PagoObserver`: Genera folios con formato `PAG-YYYYMMDD-XXXX`.

### 3.3. Trait `Auditable` (Bitácora Automática con Trazabilidad JSON)
Todos los modelos principales (`Cliente`, `Plan`, `Promocion`, `Reservacion`, `Ticket`, `MensajeContacto`, `Cargo`, `Pago`, `Router`, `User`) implementan el trait `App\Traits\Auditable`.
* Escucha automáticamente los eventos de ciclo de vida `created`, `updated` y `deleted`.
* Registra en la tabla `bitacoras`: usuario, acción (`creacion`, `actualizacion`, `eliminacion`), tabla afectada, ID del registro, `valores_anteriores` (JSON con valores previos al cambio) y `valores_nuevos` (JSON con valores modificados).
* Identifica ejecuciones en consola asignando `'CLI'` en `ip_origen` cuando no hay petición HTTP activa.
* Descarta automáticamente campos ruidosos o de seguridad (`password`, `api_password`, `remember_token`, `ultimo_acceso`, `two_factor_*`, `updated_at`).
* Encapsula los logs en un bloque `try/catch` para que un fallo en auditoría jamás interrumpa la operación principal.

### 3.4. Jobs Asíncronos para Automatización MikroTik (`app/Jobs/Red/`)
Toda comunicación con los enrutadores MikroTik se realiza fuera de las transacciones SQL mediante colas (`ShouldQueue`, `ShouldBeUnique`) y el cliente centralizado `App\Actions\Red\MikrotikRestClient` (con soporte HTTP y HTTPS TLS, política de 5 reintentos y `backoff` exponencial `[10, 30, 60, 300]`):
* **Resolución Estricta de Router Multi-Nodo:** Solo se despachan comandos de red hacia el router asignado explícitamente al cliente (`$cliente->router`), evitando tocar nodos ajenos si un cliente aún no tiene router asignado.
* **`AsignarPlanRedJob`:** Despachado desde `Admin\ClienteController` al crear (`store`) o actualizar (`update`) un cliente con `plan_id`, `router_id` e `ip_asignada`. Calcula el límite `"{velocidad_carga}M/{velocidad_descarga}M"`, consulta `/rest/queue/simple?target={ipCliente}` y ejecuta `PATCH /rest/queue/simple/{.id}` si ya existe la cola o `PUT /rest/queue/simple` si es nueva.
* **`SuspenderServicioRedJob`:** Ejecuta `PUT /rest/ip/firewall/address-list` agregando la IP del cliente a la lista `'morosos'` y cierra sus conexiones activas en `/rest/ip/firewall/connection`. Se despacha automáticamente desde el comando diario `red:suspender-morosos` o al anular un pago que deja cargos vencidos.
* **`ReconectarServicioRedJob`:** Consulta `GET /rest/ip/firewall/address-list?list=morosos&address={ipCliente}` para localizar el `.id` del registro y ejecuta `DELETE /rest/ip/firewall/address-list/{.id}`.

---

## 4. ROLES, PERMISOS Y MATRIZ DE ACCESO

El sistema implementa 3 roles diferenciados mediante el middleware `role:rol1,rol2` (`App\Http\Middleware\EnsureUserHasRole`):

| Módulo / Acción | Ruta / Permiso | Administrador | Operador | Cobrador |
| :--- | :--- | :---: | :---: | :---: |
| **Dashboard General** | `/admin/dashboard` | ✅ | ✅ | ❌ (Redirige a `/admin/cobranza`) |
| **Planes y Promociones** | `/admin/planes`, `/admin/promociones` | ✅ | ✅ | ❌ |
| **Padrón de Clientes** | `/admin/clientes` (Crear, Ver, Editar) | ✅ | ✅ | ❌ (Solo consulta en Cobranza) |
| **Eliminación de Clientes** | `DELETE /admin/clientes/{cliente}` | ✅ (Solo sin cargos/pagos) | ❌ | ❌ |
| **Reservaciones e Instalaciones** | `/admin/reservaciones` | ✅ | ✅ | ❌ |
| **Tickets de Soporte** | `/admin/tickets` | ✅ | ✅ | ❌ |
| **Mensajes de Contacto** | `/admin/mensajes` | ✅ | ✅ | ❌ |
| **Panel de Cobranza (Listado/Filtros)** | `/admin/cobranza` | ✅ | ❌ | ✅ |
| **Historial Financiero de Cliente** | `/admin/cobranza/clientes/{cliente}` | ✅ | ❌ | ✅ |
| **Registrar Pagos y Recibos** | `POST /admin/cobranza/clientes/{cliente}/pagos`, `/recibo` | ✅ | ❌ | ✅ |
| **Crear y Generar Cargos** | `/admin/cobranza/clientes/{cliente}/cargos/crear`, `/generar-masivo`| ✅ | ❌ | ✅ |
| **Anular Pagos Registrados** | `POST /admin/cobranza/pagos/{pago}/anular` | ✅ | ❌ | ❌ |
| **Cancelar Cargos Sin Pago** | `POST /admin/cobranza/cargos/{cargo}/cancelar` | ✅ | ❌ | ❌ |
| **Gestión de Usuarios del Sistema**| `/admin/usuarios` (CRUD excepto `show`) | ✅ | ❌ | ❌ |
| **Bitácora y Exportación CSV** | `/admin/bitacoras`, `/admin/bitacoras/export` | ✅ | ❌ | ❌ |

---

## 5. MÓDULOS FUNCIONALES EN DETALLE

### 5.1. Portal Público
* **Landing Page (`/`):** Hero interactivo de alto impacto, buscador rápido de folios de reservación/ticket, grilla de beneficios, selector de planes destacados, sección de infraestructura de fibra y llamados a la acción (CTA).
* **Nosotros (`/nosotros`):** Historia, misión, valores y pilares tecnológicos del ISP local.
* **Cobertura (`/cobertura`):** Mapa interactivo y zonas de cobertura activas en la región.
* **Catálogo de Planes (`/planes`):** Desglose completo de velocidades de descarga/carga, precios e inclusión de promociones activas.
* **Promociones Vigentes (`/promociones`):** Listado dinámico de ofertas filtradas por rango de validez (`scopeVigente`) y estado activo.
* **Contacto (`/contacto`):** Formulario directo con rate limiting (`throttle:10,1`) que persiste en `mensajes_contacto`.

### 5.2. Reservaciones, Privacidad PII y Alta Automática de Clientes
* Un prospecto completa el formulario en `/reservaciones/crear` indicando tipo de servicio (`instalacion_nueva`, `visita_tecnica`, `atencion_personalizada`), plan deseado, dirección y fecha solicitada.
* El sistema le expide de inmediato un **Folio Único** (`RES-YYYYMMDD-XXXX`) para consulta pública en `/reservaciones/{folio}` (protegido con `throttle:30,1`).
* **Enmascaramiento PII en Consulta Pública:** La vista pública utiliza `telefonoEnmascarado()`, `direccionEnmascarada()` y `nombreEnmascarado()` para resguardar los datos personales del solicitante.
* Desde el panel administrativo (`/admin/reservaciones`), los operadores gestionan los estados (`pendiente` -> `programada` -> `completada` / `cancelada`).
* **Regla de Negocio:** Al marcar una reservación como `'completada'`, la acción `CompletarReservacion` registra o activa automáticamente al solicitante en la tabla `clientes`, asignándole su plan contratado y generando su primera mensualidad si aplica.

### 5.3. Mesa de Ayuda y Tickets Digitales (Sin Exposición de Padrón)
* Los clientes reportan fallas en `/soporte/ticket/crear` ingresando su **número telefónico registrado en contrato** (`telefono`) y seleccionando la categoría (`falla_servicio`, `soporte_tecnico`, `consulta`, `cambio_plan`, `cancelacion`). Jamás se expone un listado público de clientes.
* `StoreTicketPublicRequest` valida que el teléfono pertenezca a un cliente en estado `'activo'` o `'suspendido'`.
* El sistema emite un folio con el formato `TKT-YYYYMMDD-XXXX`.
* El cliente puede auditar el progreso de su reporte en `/soporte/ticket/{folio}` sin necesidad de iniciar sesión.
* Los operadores gestionan el ticket (`abierto` -> `en_proceso` -> `resuelto` -> `cerrado`), agregan observaciones técnicas y el sistema sella automáticamente la `fecha_resolucion` al resolver o cerrar.

### 5.4. Módulo de Cobranza, Saldo a Favor y Sincronización con Red
* **Generación Masiva de Mensualidades:**
  * Comando Artisan: `php artisan cobranza:generar-cargos {--periodo=YYYY-MM}`.
  * Automatizado en el programador de tareas para ejecutarse el primer día de cada mes a las 00:10 hrs.
  * Determina la fecha de vencimiento según `config/cobranza.php` (por defecto, día 10 de cada mes) y aplica automáticamente cualquier `saldo_a_favor` disponible del cliente.
* **Registro de Pagos en Cascada (FIFO) y Reconexión Automática:**
  * Se registra el pago especificando método (`efectivo`, `transferencia`, `deposito`, `tarjeta`) y referencia.
  * El sistema liquida primero los cargos con fecha de vencimiento más antigua. Si el monto cubre parcialmente un cargo, este queda como `'parcial'`. Si hay excedente y `permitir_saldo_a_favor` está activo, se abona a `clientes.saldo_a_favor`.
  * Si tras el pago el cliente queda libre de cargos vencidos, se despacha `ReconectarServicioRedJob` hacia su router MikroTik.
  * Generación inmediata de recibo en formato imprimible con diseño profesional (`/admin/cobranza/pagos/{pago}/recibo`).
* **Anulación Segura de Pagos y Suspensión Automática:**
  * Exclusivo para administradores. Requiere justificación escrita obligatoria (`motivo_anulacion`).
  * Valida que el saldo a favor generado por el pago no haya sido consumido, revierte los abonos de manera determinista y, si el cliente vuelve a quedar con cargos vencidos, despacha `SuspenderServicioRedJob`.
* **Control de Morosidad:**
  * Filtros dinámicos en el panel: `todos`, `con_adeudo`, `morosos` (clientes con cargos vencidos sin liquidar) y `al_corriente`.
  * KPIs en tiempo real de saldo total por cobrar y recaudación del mes en curso.

### 5.5. Padrón de Clientes, Routers WISP y Reglas de Integridad
* Búsqueda flexible por nombre, teléfono, dirección, número de serie ONT (`serie_ont`) o usuario/IP (`usuario_pppoe`).
* **Aprovisionamiento Automático de Ancho de Banda:** Al dar de alta o editar un cliente con `plan_id` y `usuario_pppoe` (`ip_asignada`), `ClienteController` despacha `AsignarPlanRedJob` para crear o actualizar su `Simple Queue` en el router MikroTik asignado.
* **Regla de Integridad Referencial:** Un cliente no puede ser eliminado de la base de datos si cuenta con historial de cargos o pagos registrados en el sistema (`tieneHistorialDeCobranza()`). El sistema bloquea la eliminación y orienta al usuario a cambiar su estado a `'inactivo'`.

### 5.6. Seguridad, Usuarios y Auditoría
* **Inicio de Sesión Administrativo:** En `/admin/login` con campo `nombre_usuario`, protegido por Rate Limiter (5 intentos por minuto) y registro de accesos y cierres de sesión en la bitácora.
* **Protección de Cuentas:** No se permite eliminar la propia cuenta activa ni al último administrador del sistema.
* **Bitácora con Diff JSON y Exportación:** El administrador puede filtrar eventos por usuario, acción, tabla o rango de fechas, inspeccionar `valores_anteriores` y `valores_nuevos`, y descargar un reporte en CSV transmitido por chunks (`chunk(500)`) con codificación UTF-8 BOM.

---

## 6. ESQUEMA DE BASE DE DATOS Y MODELOS ELOQUENT

Todas las migraciones se encuentran en `database/migrations/`. Los nombres de las tablas y modelos respetan estrictamente el idioma español para el dominio de negocio:

```
[routers] ──1:N──┐
                 ▼
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

### 6.4. Tabla `routers` (`App\Models\Router`)
* `id` (bigint, PK)
* `nombre` (varchar 100 - Identificador del nodo, ej. `'Nodo Pinotepa Centro'`)
* `ip_address` (varchar 45 - IPv4/IPv6 de administración del MikroTik)
* `api_user` (varchar 50 - Usuario con permisos de API REST en RouterOS v7)
* `api_password` (text - **Encriptado automáticamente en BD vía `Crypt::encryptString` y desencriptado en lectura vía accesor Eloquent**; oculto en `$hidden` y excluido de `Auditable`)
* `estado` (`enum('activo', 'inactivo')`, default `'activo'`)
* `timestamps`
* *Relaciones:* `hasMany(Cliente::class, 'router_id')`.

### 6.5. Tabla `clientes` (`App\Models\Cliente`)
* `id` (bigint, PK)
* `nombre_completo` (varchar 200)
* `direccion` (varchar 300)
* `telefono` (varchar 15)
* `correo_electronico` (varchar 150, nullable)
* `plan_id` (foreignId -> `planes`, nullable, `nullOnDelete`)
* `router_id` (foreignId -> `routers`, nullable, `nullOnDelete` - Nodo MikroTik asignado)
* `fecha_alta` (date, default `CURRENT_DATE`)
* `estado` (`enum('activo', 'suspendido', 'inactivo')`, default `'activo'`)
* `saldo_a_favor` (decimal 10,2, default `0.00` - remanentes acreditados por pagos excedentes)
* `notas` (text, nullable)
* `serie_ont` (varchar 100, nullable, index - Número de serie o MAC de la ONT/Módem)
* `usuario_pppoe` (varchar 100, nullable, index - Usuario PPPoE o IP asignada; expuesto también vía accesor `$cliente->ip_asignada`)
* `coordenadas_gps` (varchar 100, nullable - Latitud, Longitud)
* `timestamps`
* *Relaciones:* `belongsTo(Plan::class)`, `belongsTo(Router::class)`, `hasMany(Ticket::class)`, `hasMany(Cargo::class)`, `hasMany(Pago::class)`, `hasMany(Reservacion::class)`.

### 6.6. Tabla `reservaciones` (`App\Models\Reservacion`)
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

### 6.7. Tabla `tickets` (`App\Models\Ticket`)
* `id` (bigint, PK)
* `folio` (varchar 20, unique) — Generado por `TicketObserver`: `TKT-YYYYMMDD-XXXX`
* `cliente_id` (foreignId -> `clientes`, `cascadeOnDelete`)
* `categoria` (`enum('falla_servicio', 'soporte_tecnico', 'consulta', 'cambio_plan', 'cancelacion')`)
* `descripcion` (text)
* `estado` (`enum('abierto', 'en_proceso', 'resuelto', 'cerrado')`, default `'abierto'`)
* `fecha_resolucion` (timestamp, nullable)
* `observaciones_admin` (text, nullable)
* `timestamps`

### 6.8. Tabla `mensajes_contacto` (`App\Models\MensajeContacto`)
* `id` (bigint, PK)
* `nombre` (varchar 150)
* `correo_electronico` (varchar 150)
* `telefono` (varchar 15, nullable)
* `asunto` (varchar 200)
* `mensaje` (text)
* `leido` (boolean, default `false`)
* `timestamps`

### 6.9. Tabla `cargos` (`App\Models\Cargo`)
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

### 6.10. Tabla `pagos` (`App\Models\Pago`)
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

### 6.11. Tabla `aplicaciones_pago` (Pivot Transaccional de Pagos y Cargos)
* `id` (bigint, PK)
* `pago_id` (foreignId -> `pagos`, `cascadeOnDelete`)
* `cargo_id` (foreignId -> `cargos`, `restrictOnDelete`)
* `monto_aplicado` (decimal 10,2)
* `timestamps`
* *Restricción:* Clave única compuesta en `['pago_id', 'cargo_id']`.

### 6.12. Tabla `bitacoras` (`App\Models\Bitacora`)
* `id` (bigint, PK)
* `usuario_id` (foreignId -> `usuarios`, nullable, `nullOnDelete`)
* `accion` (varchar 50) — `creacion`, `actualizacion`, `eliminacion`, `acceso`, `cierre_sesion`
* `descripcion` (varchar 300)
* `tabla_afectada` (varchar 50, nullable)
* `registro_id` (unsignedBigInteger, nullable)
* `valores_anteriores` (json, nullable - estado previo de los atributos modificados)
* `valores_nuevos` (json, nullable - valores resultantes de la operación)
* `ip_origen` (varchar 45, nullable - IP remota o `'CLI'`)
* `timestamps`

---

## 7. MAPA COMPLETO DE RUTAS (`routes/web.php` y `routes/settings.php`)

### 7.1. Rutas Públicas (Sin Autenticación)
| Método | URI | Nombre de Ruta | Controlador y Acción | Middleware Adicional |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/` | `home` | `HomeController@index` | — |
| `GET` | `/nosotros` | `nosotros` | `HomeController@nosotros` | — |
| `GET` | `/cobertura` | `cobertura` | `HomeController@cobertura` | — |
| `GET` | `/planes` | `planes.index` | `PublicPlanController@index` | — |
| `GET` | `/promociones` | `promociones.index` | `PublicPromocionController@index` | — |
| `GET` | `/reservaciones/crear` | `reservaciones.create` | `PublicReservacionController@create` | — |
| `POST`| `/reservaciones` | `reservaciones.store` | `PublicReservacionController@store` | `throttle:10,1` |
| `GET` | `/reservaciones/{folio}` | `reservaciones.show` | `PublicReservacionController@show` | `throttle:30,1` |
| `GET` | `/soporte/ticket/crear` | `tickets.create` | `PublicTicketController@create` | — |
| `POST`| `/soporte/ticket` | `tickets.store` | `PublicTicketController@store` | `throttle:10,1` |
| `GET` | `/soporte/ticket/{folio}` | `tickets.show` | `PublicTicketController@show` | `throttle:30,1` |
| `GET` | `/contacto` | `contacto` | `ContactoController@index` | — |
| `POST`| `/contacto` | `contacto.store` | `ContactoController@store` | `throttle:10,1` |

### 7.2. Autenticación Administrativa
| Método | URI | Nombre de Ruta | Controlador y Acción |
| :--- | :--- | :--- | :--- |
| `GET` | `/admin/login` | `admin.login` | `Admin\AuthController@showLoginForm` |
| `POST`| `/admin/login` | `admin.login.submit`| `Admin\AuthController@login` (Rate limited: 5/min) |
| `POST`| `/admin/logout` | `admin.logout` | `Admin\AuthController@logout` |

### 7.3. Panel Administrativo Protegido (`/admin`, Middleware: `auth`)
* **Área General (`role:administrador,operador`):**
  * `GET /admin/dashboard` (`admin.dashboard`) -> `Admin\DashboardController@index`
  * Resource CRUD: `/admin/planes` (`admin.planes.*`) -> `Admin\PlanController`
  * Resource CRUD: `/admin/promociones` (`admin.promociones.*`) -> `Admin\PromocionController`
  * Resource CRUD: `/admin/clientes` (`admin.clientes.*` — `destroy` restringido a admin sin historial financiero) -> `Admin\ClienteController`
  * `/admin/reservaciones` (`admin.reservaciones.index`, `show`, `edit`, `update`) -> `Admin\ReservacionController`
  * Resource CRUD: `/admin/tickets` (`admin.tickets.*`) -> `Admin\TicketController`
  * `/admin/mensajes` (`admin.mensajes.index`, `show`, `update` [marcar leído], `destroy`) -> `Admin\MensajeContactoController`
* **Área de Cobranza (`role:administrador,cobrador`):**
  * `GET /admin/cobranza` (`admin.cobranza.index`) -> `Admin\CobranzaController@index`
  * `GET /admin/cobranza/clientes/{cliente}` (`admin.cobranza.show`) -> `Admin\CobranzaController@show`
  * `POST /admin/cobranza/clientes/{cliente}/pagos` (`admin.pagos.store`) -> `Admin\PagoController@store`
  * `GET /admin/cobranza/pagos/{pago}/recibo` (`admin.pagos.recibo`) -> `Admin\PagoController@recibo`
  * `GET /admin/cobranza/clientes/{cliente}/cargos/crear` (`admin.cargos.create`) -> `Admin\CargoController@create`
  * `POST /admin/cobranza/clientes/{cliente}/cargos` (`admin.cargos.store`) -> `Admin\CargoController@store`
  * `POST /admin/cobranza/cargos/generar-masivo` (`admin.cargos.generar-masivo`) -> `Admin\CargoController@generarMasivo`
* **Operaciones Exclusivas de Administrador (`role:administrador`):**
  * `POST /admin/cobranza/pagos/{pago}/anular` (`admin.pagos.anular`) -> `Admin\PagoController@anular`
  * `POST /admin/cobranza/cargos/{cargo}/cancelar` (`admin.cargos.cancelar`) -> `Admin\CargoController@cancelar`
  * Resource CRUD: `/admin/usuarios` (`admin.usuarios.*` — excepto `show`) -> `Admin\UsuarioController`
  * `GET /admin/bitacoras` (`admin.bitacoras.index`) -> `Admin\BitacoraController@index`
  * `GET /admin/bitacoras/export` (`admin.bitacoras.export`) -> `Admin\BitacoraController@export`

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

## 9. CONFIGURACIÓN, COMANDOS ARTISAN Y PROGRAMACIÓN DE TAREAS (CRON)

### 9.1. Archivos de Configuración de Negocio y Red
* **`config/cobranza.php`:**
  * `dia_vencimiento` (`COBRANZA_DIA_VENCIMIENTO`, default `10`)
  * `permitir_saldo_a_favor` (`COBRANZA_PERMITIR_SALDO_A_FAVOR`, default `false`)
  * `facturar_suspendidos` (`COBRANZA_FACTURAR_SUSPENDIDOS`, default `false`)
  * `generar_cargo_al_completar_reservacion` (`COBRANZA_CARGO_AL_COMPLETAR_RESERVACION`, default `true`)
* **`config/mikrotik.php` y `config/services.php` (`services.mikrotik`):**
  * `ip` (`MIKROTIK_IP`, default `'192.168.1.116'`)
  * `user` (`MIKROTIK_USER`, default `'admin'`)
  * `pass` (`MIKROTIK_PASS`, default `''`)
  * `timeout` (`MIKROTIK_TIMEOUT`, default `5` segundos)

### 9.2. Comandos del Negocio
```bash
# Facturación del mes actual para todos los clientes activos con plan:
php artisan cobranza:generar-cargos

# Facturación manual de un periodo específico (ej. mayo 2026):
php artisan cobranza:generar-cargos --periodo=2026-05

# Suspensión automática de clientes activos con cargos vencidos:
php artisan red:suspender-morosos --dias-gracia=0
```

### 9.3. Programación Automática (`routes/console.php`)
```php
use Illuminate\Support\Facades\Schedule;

// Emite automáticamente los cargos de mensualidad el día 1 de cada mes a las 00:10 hrs:
Schedule::command('cobranza:generar-cargos')
    ->monthlyOn(1, '00:10')
    ->withoutOverlapping();

// Suspende diariamente a las 06:00 hrs a clientes activos con cargos vencidos:
Schedule::command('red:suspender-morosos')
    ->dailyAt('06:00')
    ->withoutOverlapping();
```
*En el servidor de producción, se debe mantener activo el cron del sistema llamando a `* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1` y un worker de colas (`php artisan queue:work`) para procesar los Jobs de MikroTik.*

---

## 10. CALIDAD DE CÓDIGO Y SUITE DE PRUEBAS

### 10.1. Comandos de Verificación
* **Ejecutar Suite Completa:** `php artisan test --compact` (**100 pruebas**: 98 exitosas, 2 omitidas, **324 aserciones**, 0 fallos).
* **Ejecutar Pruebas Específicas:**
  * `php artisan test --filter=MikrotikJobsTest`
  * `php artisan test --filter=CobranzaSaldoAFavorTest`
  * `php artisan test --filter=BitacoraJsonDiffTest`
  * `php artisan test --filter=SeguridadEndpointsPublicosTest`
  * `php artisan test --filter=ClienteRedWispTest`
  * `php artisan test --filter=CobranzaTest`
  * `php artisan test --filter=AdminPanelTest`
  * `php artisan test --filter=FolioAndAuditObserverTest`
  * `php artisan test --filter=PublicPortalTest`
  * `php artisan test --filter=UsuarioYSeguridadTest`
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
   * Las entidades del negocio, tablas, columnas, rutas y variables deben escribirse en **idioma español** (`cargos`, `pagos`, `planes`, `reservaciones`, `routers`, `folio`, `monto_pagado`, `saldo_a_favor`).
   * Los términos de infraestructura y palabras clave del framework se mantienen en inglés (`User`, `Controller`, `Observer`, `Migration`, `Request`, `Job`, `StorePagoRequest`).
2. **Generación de Folios Exclusiva en Observers:**
   * Jamás generes folios dentro de los controladores ni en las vistas.
   * Utiliza o extiende los Observers con `#[ObservedBy]` en el Modelo correspondiente.
3. **Casos de Uso Complejos en Actions:**
   * Cualquier lógica que involucre transacciones de base de datos, bloqueo pesimista o coordinación entre varios modelos debe encapsularse en una clase bajo `app/Actions/`.
4. **Automatización de Red MikroTik Asíncrona y Segura:**
   * Toda llamada HTTP hacia routers MikroTik debe ejecutarse mediante Jobs en cola bajo `app/Jobs/Red/` (`ShouldQueue`) y **después** de confirmar la transacción de base de datos (`DB::transaction()`).
   * Jamás almacenes contraseñas de routers en texto plano ni las registres en bitácoras; utiliza el modelo `App\Models\Router` cuyo atributo `api_password` se encripta automáticamente con `Crypt`.
5. **Validación Estricta en FormRequests:**
   * Prohibido validar directamente en los controladores mediante `$request->validate()`.
   * Crea siempre una clase `FormRequest` dedicada con mensajes claros en español.
6. **Consultas a Base de Datos y Rendimiento:**
   * Prohibido el uso de SQL crudo o concatenación de variables en consultas.
   * Evita problemas de rendimiento N+1 utilizando siempre **Eager Loading** explícito (`with(['plan', 'cliente', 'router'])`) y scopes de agregación (`conSaldo()`) al consultar listados.
7. **Seguridad, Privacidad PII y Auditoría:**
   * Todo modelo representativo de datos de negocio debe implementar `App\Traits\Auditable`.
   * Nunca expongas el padrón de clientes ni datos personales completos en vistas públicas sin autenticación (usa validación por teléfono y métodos de enmascaramiento).
   * Las vistas Blade deben utilizar `{{ $variable }}` para sanitización XSS automática.
   * Aplica siempre el middleware `role` para proteger rutas administrativas según la matriz de acceso.
8. **Diseño Visual e Integridad del Frontend:**
   * Respeta los layouts existentes (`layouts/app.blade.php` y `layouts/admin.blade.php`).
   * No agregues librerías CSS pesadas adicionales sin consultar. El panel y el portal público utilizan Bootstrap 5.3 + Motion Tokens; el starter-kit `/settings` utiliza Tailwind 4 + Livewire Flux.
9. **Formateo y Tests:**
   * Tras modificar archivos PHP, corre `vendor/bin/pint --dirty --format agent`.
   * Si introduces una nueva funcionalidad o alteras una regla de negocio, agrega o actualiza su respectiva prueba en `tests/Feature/`.
