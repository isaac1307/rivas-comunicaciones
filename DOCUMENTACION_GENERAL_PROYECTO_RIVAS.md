# Documentación General del Proyecto: Rivas Comunicaciones (ISP / WISP)

Este documento describe de principio a fin **todo lo que hace el sistema web de Rivas Comunicaciones**, cómo funcionan sus módulos operativos, comerciales, financieros y de red, y cómo interactúan sus componentes internos.

---

## 1. Resumen Ejecutivo

**Rivas Comunicaciones** es una plataforma web integral desarrollada en **Laravel 13** y **PHP 8.5** para la gestión completa de un Proveedor de Servicios de Internet (ISP / WISP de Fibra Óptica e Inalámbrico). 

El sistema unifica en una sola aplicación:
1. **Portal Comercial Público:** Captación de clientes, exhibición de planes y promociones vigentes, mapa de cobertura y buzón de contacto.
2. **Gestión de Reservaciones e Instalaciones:** Solicitud en línea de nuevas contrataciones o visitas técnicas con folio de rastreo y **conversión automática a cliente activo** al concluir la instalación.
3. **Mesa de Ayuda (Tickets de Soporte):** Recepción de reportes de falla por teléfono de contrato (sin exponer datos personales), generación de folio único y seguimiento público y administrativo.
4. **Motor Financiero y de Cobranza:** Facturación mensual automatizada (vía Cron/Scheduler o manual), cobro en ventanilla con aplicación en cascada (**FIFO**), manejo de **saldo a favor**, recibos imprimibles, control de morosidad y **anulación auditable con reversión matemática de saldos**.
5. **Automatización de Red Multi-Router (MikroTik RouterOS v7 REST API):** Aprovisionamiento automático de ancho de banda (`Simple Queues`), corte automático de servicio por morosidad (`Firewall Address-List: morosos`) y reconexión automática al liquidar adeudos vencidos mediante trabajos en segundo plano (**Queued Jobs**).
6. **Seguridad, Roles y Auditoría Forense:** Control de acceso basado en 3 roles (`administrador`, `operador`, `cobrador`), autenticación robusta (Rate Limiting, 2FA TOTP y Passkeys WebAuthn), enmascaramiento de datos sensibles (PII), encriptación de credenciales de routers y bitácora automática con diferencias en JSON (`valores_anteriores` y `valores_nuevos`) exportable a CSV.

---

## 2. ¿Qué hace cada módulo del sistema?

### 2.1. Portal Público (Clientes y Prospectos)
Accesible sin iniciar sesión, diseñado con identidad visual propia (`Plus Jakarta Sans`, Bootstrap 5.3 y sistema de microinteracciones `motion.blade.php`):
* **Inicio (`/`):** Presenta la propuesta de valor del ISP, buscador rápido de folios (tanto de reservaciones `RES-...` como de tickets `TKT-...`), planes destacados y llamados a la acción.
* **Nosotros (`/nosotros`):** Historia, misión, visión y valores de Rivas Comunicaciones.
* **Cobertura (`/cobertura`):** Información de zonas con disponibilidad de fibra óptica y enlaces inalámbricos.
* **Planes (`/planes`):** Catálogo dinámico de planes activos ordenados por precio, mostrando velocidad de descarga (`Mbps`), velocidad de carga (`Mbps`), tarifa mensual y promociones asociadas. Solo se listan planes con estado `activo`.
* **Promociones (`/promociones`):** Muestra exclusivamente las ofertas vigentes (`estado = activa` y fecha actual dentro del rango `fecha_inicio` a `fecha_vencimiento`) junto con los planes a los que aplican.
* **Contacto (`/contacto`):** Formulario protegido con límite de peticiones (`throttle:10,1`) que guarda mensajes comerciales o dudas en `mensajes_contacto` para su atención en el panel administrativo.

---

### 2.2. Reservaciones e Instalaciones (`/reservaciones/...` y `/admin/reservaciones`)
Gestiona el ciclo completo desde que un prospecto solicita el servicio hasta que queda instalado y facturado:
1. **Solicitud en Línea (`GET /reservaciones/crear`, `POST /reservaciones`):**
   * El solicitante elige el tipo de servicio (`instalacion_nueva`, `visita_tecnica`, `atencion_personalizada`), el plan de internet deseado, su nombre, teléfono, dirección, correo y fecha preferente.
   * Al guardarse, el observer `ReservacionObserver` genera automáticamente un folio único con formato `RES-YYYYMMDD-XXXX`.
2. **Consulta Pública con Protección de Privacidad (`GET /reservaciones/{folio}`):**
   * Cualquier persona con el folio puede consultar el avance (`pendiente`, `programada`, `completada`, `cancelada`).
   * **Protección PII:** La vista pública enmascara automáticamente el teléfono (`***-***-1234`), la dirección y el nombre mediante métodos del modelo `Reservacion` (`telefonoEnmascarado()`, `direccionEnmascarada()`, `nombreEnmascarado()`).
3. **Gestión y Conversión Automática a Cliente (`PUT /admin/reservaciones/{reservacion}`):**
   * Los operadores y administradores actualizan el estado y agregan observaciones técnicas.
   * Cuando el estado pasa a **`completada`**, la clase de negocio `App\Actions\Reservaciones\CompletarReservacion` ejecuta dentro de una transacción de base de datos:
     * Busca si el cliente ya existe (por `cliente_id`, `telefono` o `correo_electronico`).
     * Si no existe, **crea automáticamente el registro en `clientes`** con estado `activo`, asignándole el plan contratado y vinculando la reservación. Si ya existía, lo reactiva y actualiza su plan/dirección.
     * Si la reservación es de tipo `instalacion_nueva` y la opción `cobranza.generar_cargo_al_completar_reservacion` está activa, **emite automáticamente el primer cargo de mensualidad** del mes en curso (de forma idempotente).

---

### 2.3. Mesa de Ayuda y Tickets de Soporte (`/soporte/ticket/...` y `/admin/tickets`)
Permite a los clientes reportar incidencias y al equipo técnico darles seguimiento hasta su cierre:
1. **Levantamiento Público Seguro (`GET /soporte/ticket/crear`, `POST /soporte/ticket`):**
   * Por seguridad y privacidad, el formulario público **no muestra ninguna lista desplegable de clientes**.
   * El cliente ingresa el **número telefónico registrado en su contrato**, elige la categoría (`falla_servicio`, `soporte_tecnico`, `consulta`, `cambio_plan`, `cancelacion`) y describe el problema.
   * `StoreTicketPublicRequest` valida que el teléfono corresponda a un cliente `activo` o `suspendido`.
   * `TicketObserver` genera automáticamente el folio `TKT-YYYYMMDD-XXXX`.
2. **Seguimiento Público (`GET /soporte/ticket/{folio}`):**
   * El cliente consulta en tiempo real el estado de su reporte (`abierto`, `en_proceso`, `resuelto`, `cerrado`), la fecha de resolución y las observaciones técnicas del equipo de soporte.
3. **Atención Administrativa (`/admin/tickets`):**
   * Administradores y operadores pueden filtrar tickets por estado o categoría, levantar tickets internos para cualquier cliente activo y actualizar el dictamen técnico (`observaciones_admin`).
   * Cuando un ticket se marca como `resuelto` o `cerrado`, el sistema sella automáticamente la `fecha_resolucion` si aún no estaba definida.

---

### 2.4. Padrón de Clientes y Parámetros Técnicos WISP (`/admin/clientes`)
Centraliza el expediente comercial y técnico de cada suscriptor:
* **Datos Comerciales y de Contrato:** Nombre completo, dirección, teléfono, correo electrónico, plan contratado (`plan_id`), fecha de alta, estado (`activo`, `suspendido`, `inactivo`), `saldo_a_favor` y notas.
* **Datos Técnicos de Red (WISP / FTTH):**
  * `router_id`: Nodo o enrutador MikroTik al que está conectado el cliente.
  * `serie_ont`: Número de serie o dirección MAC de la ONT / antena / módem.
  * `usuario_pppoe` / `ip_asignada`: Identificador PPPoE o dirección IP del cliente para control de tráfico en el router.
  * `coordenadas_gps`: Ubicación geográfica (`latitud, longitud`) de la instalación.
* **Buscador Inteligente:** Permite localizar clientes por nombre, teléfono, dirección, número de serie de ONT (`serie_ont`) o usuario/IP (`usuario_pppoe`).
* **Protección de Integridad Histórica:** Ningún cliente puede ser eliminado si tiene cargos o pagos registrados (`tieneHistorialDeCobranza()`); además, solo el rol `administrador` tiene permiso para eliminar clientes sin historial financiero.

---

### 2.5. Módulo de Cobranza, Facturación y Saldo a Favor (`/admin/cobranza`)
Administra todo el ciclo financiero de cuentas por cobrar y recaudación:

1. **Tablero de Cobranza y Filtros de Morosidad (`GET /admin/cobranza`):**
   * Muestra indicadores (KPIs) en tiempo real: **Total por Cobrar**, **Recaudado en el Mes**, **Clientes Morosos** y **Clientes con Adeudo**.
   * Calcula saldos mediante agregaciones SQL optimizadas (`scopeConSaldo`) sin problemas N+1.
   * Permite filtrar el padrón por: `todos`, `con_adeudo` (tienen cargos pendientes o parciales), `morosos` (tienen cargos cuya `fecha_vencimiento` ya pasó y siguen sin liquidarse) y `al_corriente`.
2. **Estado de Cuenta por Cliente (`GET /admin/cobranza/clientes/{cliente}`):**
   * Desglosa todos los cargos (mensualidades, instalaciones, reconexiones, otros), su monto original, monto abonado, saldo pendiente y los pagos realizados con acceso directo a sus recibos.
3. **Generación Masiva y Automatizada de Mensualidades (`App\Actions\Cobranza\GenerarCargosMensuales`):**
   * Se ejecuta el día 1 de cada mes a las `00:10` vía Scheduler (`php artisan cobranza:generar-cargos`) o manualmente desde el panel (`POST /admin/cobranza/cargos/generar-masivo`).
   * Es **100% idempotente**: omite clientes que ya tengan cargo de tipo `mensualidad` para el periodo `YYYY-MM`.
   * Procesa en bloques (`chunkById(200)`) a los clientes activos (y suspendidos si `config('cobranza.facturar_suspendidos')` está habilitado).
   * **Aplicación Automática de Saldo a Favor:** Si el cliente cuenta con `saldo_a_favor > 0`, el sistema lo descuenta automáticamente de la nueva mensualidad emitida, marcando el cargo como `pagado` (si cubrió el 100%) o `parcial` (si cubrió una parte) y actualizando el saldo a favor restante del cliente.
4. **Registro de Pagos en Cascada FIFO (`App\Actions\Cobranza\RegistrarPago`):**
   * Al recibir un pago (`efectivo`, `transferencia`, `deposito`, `tarjeta`), `PagoObserver` genera el folio `PAG-YYYYMMDD-XXXX`.
   * Utiliza transacción de base de datos con **bloqueo pesimista** (`lockForUpdate()`) sobre el cliente y sus cargos pendientes.
   * Liquida los cargos en orden **FIFO** (primero los de `fecha_vencimiento` más antigua).
   * Registra en la tabla pivote `aplicaciones_pago` exactamente cuánto dinero de ese pago se destinó a cada cargo (`monto_aplicado`), permitiendo que un pago cubra múltiples meses o que un mes se pague en varios abonos parciales.
   * **Excedentes y Saldo a Favor:** Si se habilita `permitir_saldo_a_favor` y el monto pagado supera la deuda total del cliente, el excedente se guarda en `pagos.monto_saldo_favor` y se suma a `clientes.saldo_a_favor`.
5. **Recibo Imprimible (`GET /admin/cobranza/pagos/{pago}/recibo`):**
   * Genera un comprobante listo para impresión con folio, datos del cliente, cajero/cobrador que atendió, método de pago y desglose de conceptos abonados.
6. **Anulación Segura de Pagos (`App\Actions\Cobranza\AnularPago`):**
   * Exclusivo para administradores; exige `motivo_anulacion` obligatorio.
   * Verifica que si el pago generó saldo a favor, el cliente aún disponga de dicho saldo antes de permitir la anulación.
   * Marca el pago como `anulado` y **recalcula determinísticamente** el `monto_pagado` y el `estado` (`pendiente`, `parcial`, `pagado`) de todos los cargos afectados sumando únicamente los pagos vigentes (`estado = 'aplicado'`).
7. **Cargos Manuales y Cancelación (`/admin/cobranza/clientes/{cliente}/cargos`):**
   * Permite emitir cargos individuales por `instalacion`, `reconexion`, `mensualidad` u `otro`.
   * Un administrador puede cancelar un cargo (`estado = 'cancelado'`) únicamente si aún no tiene ningún abono aplicado (`monto_pagado == 0`).

---

### 2.6. Automatización de Red WISP y Multi-Router MikroTik (`app/Jobs/Red/` y `App\Models\Router`)
El sistema se integra con routers **MikroTik RouterOS v7** mediante su **API REST nativa** (`http://{ip}/rest/...`) utilizando colas de trabajo asíncronas (`ShouldQueue`) para no bloquear la interfaz del usuario:

1. **Gestión Multi-Router y Seguridad Criptográfica (`App\Models\Router`):**
   * Tabla `routers` con `nombre`, `ip_address`, `api_user`, `api_password` y `estado` (`activo` / `inactivo`).
   * **Encriptación Transparente:** El atributo `api_password` utiliza un mutador/accesor con `Crypt::encryptString()` y `Crypt::decryptString()` para que las contraseñas de los routers nunca se almacenen en texto plano en la base de datos y nunca se expongan en serializaciones JSON ni en la bitácora.
   * Cada cliente puede tener asignado su propio `router_id` (con fallback automático al primer router activo si no tiene uno específico).
2. **Control de Ancho de Banda (`App\Jobs\Red\AsignarPlanRedJob`):**
   * **Cuándo se dispara:** Automáticamente al crear (`ClienteController@store`) o actualizar (`ClienteController@update`) un cliente que tenga `plan_id` e `ip_asignada` (`usuario_pppoe`).
   * **Qué hace en MikroTik:** Calcula el límite `velocidad_cargaM/velocidad_descargaM` (ej. `15M/50M`). Consulta `GET /rest/queue/simple?target={ip}`; si el cliente ya tiene una Simple Queue, actualiza su velocidad con `PATCH /rest/queue/simple/{.id}`, y si no existe, la crea con `PUT /rest/queue/simple`.
3. **Suspensión Automática por Morosidad (`App\Jobs\Red\SuspenderServicioRedJob`):**
   * **Cuándo se dispara:** Al anular un pago (`AnularPago`) si como consecuencia el cliente vuelve a tener cargos vencidos pendientes, o cuando se invoca para cortar el servicio de un cliente moroso.
   * **Qué hace en MikroTik:** Envía una petición `PUT /rest/ip/firewall/address-list` agregando la IP del cliente a la lista `morosos` con el comentario `"Suspendido por sistema - Cliente: {id}"`.
4. **Reconexión Automática al Pagar (`App\Jobs\Red\ReconectarServicioRedJob`):**
   * **Cuándo se dispara:** Automáticamente en `RegistrarPago` cuando, tras aplicar el pago recibido, el cliente ya no tiene ningún cargo vencido pendiente (`! $cliente->cargos()->vencidos()->exists()`).
   * **Qué hace en MikroTik:** Consulta `GET /rest/ip/firewall/address-list?list=morosos&address={ip}` para obtener el identificador interno `.id` en RouterOS y ejecuta `DELETE /rest/ip/firewall/address-list/{.id}`, restableciendo la navegación del cliente al instante.

---

### 2.7. Seguridad, Usuarios, Roles y Bitácora Forense
1. **Tres Roles de Usuario (`App\Enums\RolUsuario` / `EnsureUserHasRole`):**
   * **Administrador (`administrador`):** Acceso total al sistema, gestión de usuarios, bitácora y exportación CSV, eliminación de clientes sin historial, cancelación de cargos y anulación de pagos.
   * **Operador (`operador`):** Acceso al Dashboard, catálogo de planes y promociones, padrón de clientes, reservaciones/instalaciones, tickets de soporte y mensajes de contacto.
   * **Cobrador (`cobrador`):** Acceso focalizado al módulo de Cobranza (tablero de saldos, estado de cuenta de clientes, registro de pagos, emisión de recibos y alta de cargos). Su inicio de sesión redirige automáticamente a `/admin/cobranza`.
2. **Autenticación Dual:**
   * **Login Administrativo (`/admin/login`):** Autenticación por `nombre_usuario` y contraseña con límite de 5 intentos por minuto (`RateLimiter`), validación de usuario `activo` y registro de inicio/cierre de sesión en bitácora.
   * **Ajustes de Cuenta y Seguridad Avanzada (`/settings/...`):** Impulsado por **Laravel Fortify + Livewire 4 + Flux UI**, permite a los usuarios gestionar su perfil, cambiar tema visual (claro/oscuro/sistema), activar **Autenticación de Dos Factores (2FA TOTP con códigos QR y de recuperación)** y registrar **Passkeys (WebAuthn)**.
3. **Bitácora Forense Automática (`App\Traits\Auditable` y `/admin/bitacoras`):**
   * Todos los modelos de negocio (`Cliente`, `Plan`, `Promocion`, `Reservacion`, `Ticket`, `MensajeContacto`, `Cargo`, `Pago`, `Router`, `User`) registran automáticamente sus eventos `created`, `updated` y `deleted`.
   * Guarda un **diff JSON** con `valores_anteriores` y `valores_nuevos` únicamente de las columnas que cambiaron, omitiendo secretos (`password`, `api_password`, `two_factor_*`, `remember_token`).
   * El panel de bitácoras incluye KPIs de actividad, filtros por fecha, usuario, tabla y tipo de acción, modal de inspección de cambios y **exportación a CSV por streaming (`chunk(500)`)** con codificación UTF-8 BOM para Excel.

---

## 3. Estructura de la Base de Datos (Tablas Principales)

| Tabla | Modelo Eloquent | Función Principal |
| :--- | :--- | :--- |
| `usuarios` | `App\Models\User` | Cuentas del personal (`administrador`, `operador`, `cobrador`), credenciales, 2FA y último acceso. |
| `planes` | `App\Models\Plan` | Catálogo de paquetes de internet (velocidades de carga/descarga en Mbps y precio mensual). |
| `promociones` | `App\Models\Promocion` | Ofertas comerciales con rango de vigencia (`fecha_inicio`, `fecha_vencimiento`). |
| `promocion_plan` | *(Tabla Pivote)* | Relación muchos a muchos entre promociones y planes de internet. |
| `routers` | `App\Models\Router` | Nodos MikroTik con IP, usuario API y contraseña encriptada (`Crypt`). |
| `clientes` | `App\Models\Cliente` | Padrón de suscriptores, plan, router asignado, saldo a favor y datos técnicos (`serie_ont`, `usuario_pppoe`, `coordenadas_gps`). |
| `reservaciones` | `App\Models\Reservacion` | Solicitudes de instalación o visita técnica con folio `RES-YYYYMMDD-XXXX`. |
| `tickets` | `App\Models\Ticket` | Reportes de soporte técnico con folio `TKT-YYYYMMDD-XXXX` ligados a un cliente. |
| `mensajes_contacto`| `App\Models\MensajeContacto`| Mensajes enviados desde el formulario público de contacto. |
| `cargos` | `App\Models\Cargo` | Adeudos emitidos a clientes (mensualidad, instalación, reconexión, otro) y control de vencimiento. |
| `pagos` | `App\Models\Pago` | Cobros recibidos con folio `PAG-YYYYMMDD-XXXX`, método de pago, saldo a favor generado y estado (`aplicado`/`anulado`). |
| `aplicaciones_pago`| *(Tabla Pivote)* | Desglose exacto (`monto_aplicado`) que vincula cada `Pago` con los `Cargos` que cubrió. |
| `bitacoras` | `App\Models\Bitacora` | Historial de auditoría con usuario, IP (`CLI` o remota), acción y JSON de valores anteriores/nuevos. |
| `passkeys` | *(Laravel Fortify)* | Llaves criptográficas WebAuthn para inicio de sesión sin contraseña. |

---

## 4. Calidad, Pruebas y Comandos Principales

* **Suite de Pruebas Automatizadas (PHPUnit 12.5):**
  * Cuenta con **100 pruebas automatizadas (324 aserciones)** distribuidas en pruebas de integración del portal público, seguridad de endpoints y privacidad PII, panel administrativo, generación de folios y auditoría JSON, motor de cobranza FIFO, manejo de saldo a favor, campos de red WISP y sincronización con MikroTik REST API (`Http::fake()` y `Queue::fake()`).
  * Ejecutar todas las pruebas: `php artisan test --compact`
* **Facturación Mensual por Consola:**
  * Mes actual: `php artisan cobranza:generar-cargos`
  * Mes específico: `php artisan cobranza:generar-cargos --periodo=2026-10`
* **Formateo y Análisis Estático:**
  * Estilo de código (Laravel Pint): `vendor/bin/pint --dirty --format agent`
  * Análisis estático (PHPStan / Larastan): `vendor/bin/phpstan analyse`
