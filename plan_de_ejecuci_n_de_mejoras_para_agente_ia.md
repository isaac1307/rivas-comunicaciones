# PLAN DE EJECUCIÓN Y ESPECIFICACIÓN TÉCNICA PARA AGENTE DE IA
**Proyecto:** Rivas Comunicaciones (Laravel 13.35 & PHP 8.5.4)  
**Objetivo:** Implementar las mejoras arquitectónicas, de seguridad, blindaje financiero (Cobranza) y dominio WISP/ISP, respetando estrictamente las convenciones del `contexto_maestro_para_agente_ia_proyecto_rivas.md`.

---

## INSTRUCCIONES OBLIGATORIAS PARA EL AGENTE DE IA

Actúa como un **Arquitecto de Software Senior especialista en Laravel 13 y PHP 8.5**. Debes ejecutar este plan de mejoras de manera secuencial (Fase por Fase). Antes de escribir cualquier línea de código, ten presentes estas **Reglas Inquebrantables**:

1. **Cero Regresiones:** Los +77 tests existentes en `tests/Feature/` y `tests/Unit/` deben seguir pasando en todo momento.
2. **Nomenclatura del Dominio:** Tablas, columnas, rutas, modelos, variables de negocio y mensajes de validación **SIEMPRE en español**. Palabras clave del framework y sufijos técnicos (`Controller`, `Request`, `Observer`, `Action`, `Enum`) en inglés.
3. **Arquitectura por Capas:**
   * Prohibido usar `$request->validate()` en controladores; usa o crea siempre un `FormRequest`.
   * Prohibido poner lógica transaccional en controladores; usa clases en `app/Actions/`.
   * Prohibido generar folios fuera de `app/Observers/`.
4. **Calidad Continua:** Al finalizar cada fase, debes ejecutar obligatoriamente:
   * `vendor/bin/pint --dirty --format agent`
   * `vendor/bin/phpstan analyse`
   * `php artisan test --compact`

---

## FASE 1: Tipado Fuerte con Backed Enums y Evolución de Auditoría (`Auditable`)

### 1.1. Creación de Backed Enums en `app/Enums/`
Reemplaza el uso de *magic strings* en estados y roles creando Enums nativos de PHP 8.5 con tipado `string` y métodos auxiliares para etiquetas/colores en Bootstrap 5.3:

* `app/Enums/RolUsuario.php`: `Administrador = 'administrador'`, `Operador = 'operador'`, `Cobrador = 'cobrador'`.
* `app/Enums/EstadoCliente.php`: `Activo = 'activo'`, `Suspendido = 'suspendido'`, `Inactivo = 'inactivo'`.
* `app/Enums/EstadoCargo.php`: `Pendiente = 'pendiente'`, `Parcial = 'parcial'`, `Pagado = 'pagado'`, `Cancelado = 'cancelado'`.
* `app/Enums/TipoCargo.php`: `Mensualidad = 'mensualidad'`, `Instalacion = 'instalacion'`, `Reconexion = 'reconexion'`, `Otro = 'otro'`.
* `app/Enums/EstadoPago.php`: `Aplicado = 'aplicado'`, `Anulado = 'anulado'`.
* `app/Enums/MetodoPago.php`: `Efectivo = 'efectivo'`, `Transferencia = 'transferencia'`, `Deposito = 'deposito'`, `Tarjeta = 'tarjeta'`.
* `app/Enums/EstadoReservacion.php`: `Pendiente = 'pendiente'`, `Programada = 'programada'`, `Completada = 'completada'`, `Cancelada = 'cancelada'`.
* `app/Enums/EstadoTicket.php`: `Abierto = 'abierto'`, `EnProceso = 'en_proceso'`, `Resuelto = 'resuelto'`, `Cerrado = 'cerrado'`.

> **Nota de Compatibilidad:** Mantén compatibilidad en los Modelos Eloquent verificando si los tests existentes comparan contra strings (`$cargo->estado === 'pagado'`) o instancias de Enum. Si los tests actuales comparan strings directamente, utiliza los Enums como constantes de dominio (`EstadoCargo::Pagado->value`) y reglas `Rule::enum(...)` en los `FormRequests` para no romper aserciones previas.

### 1.2. Trazabilidad JSON (Antes/Después) en `bitacoras`
En un sistema con módulo de cobranza es indispensable auditar qué valor exacto cambió.

1. **Migración:** Crea una migración `add_json_changes_to_bitacoras_table` que agregue a la tabla `bitacoras`:
   * `valores_anteriores` (`json`, `nullable`, después de `registro_id`)
   * `valores_nuevos` (`json`, `nullable`, después de `valores_anteriores`)
2. **Actualización de `App\Models\Bitacora`:**
   * Agrega `'valores_anteriores'` y `'valores_nuevos'` a `$fillable` y defínelos como `'array'` en `casts()`.
3. **Actualización de `App\Traits\Auditable`:**
   * En el evento `created`: guarda los atributos filtrados en `valores_nuevos` y `null` en `valores_anteriores`.
   * En el evento `updated`: captura `array_intersect_key($model->getOriginal(), $model->getDirty())` para `valores_anteriores` y `$model->getDirty()` para `valores_nuevos` (excluyendo siempre campos sensibles como `password`, `remember_token`, `two_factor_*`, `updated_at`).
   * En el evento `deleted`: guarda los atributos originales filtrados en `valores_anteriores`.
   * **Soporte CLI:** Si `app()->runningInConsole()` es `true` y no hay request HTTP activa, asigna `'CLI'` en `ip_origen`.

---

## FASE 2: Blindaje Financiero en el Módulo de Cobranza

### 2.1. Soporte para Saldos a Favor (Pagos Excedentes)
Actualmente, si un cliente realiza un pago o transferencia superior a su deuda actual (o paga por adelantado sin cargo emitido), el excedente queda sin rastro contable en los cargos.

1. **Migración:** Crea una migración `add_saldo_a_favor_to_clientes_and_pagos_tables`:
   * En `clientes`: agrega `saldo_a_favor` (`decimal(10, 2)`, `default(0.00)`, después de `estado`).
   * En `pagos`: agrega `monto_saldo_favor` (`decimal(10, 2)`, `default(0.00)`, después de `monto`) para registrar qué porción de ese pago específico se fue a saldo a favor del cliente.
2. **Refactorización de `App\Actions\Cobranza\RegistrarPago`:**
   * Dentro de `DB::transaction()`, establece un **orden estricto de bloqueo pesimista** para prevenir *deadlocks*:
     1. Bloquea primero al cliente: `$cliente = Cliente::query()->lockForUpdate()->findOrFail($clienteId);`
     2. Bloquea después los cargos pendientes/parciales ordenados determinísticamente: `ORDER BY fecha_vencimiento ASC, id ASC` con `->lockForUpdate()`.
   * Aplica el monto en cascada (FIFO) sobre los cargos pendientes/parciales.
   * Si al terminar de iterar los cargos aún queda un remanente (`$montoRestante > 0`):
     * Registra ese remanente en `$pago->monto_saldo_favor = $montoRestante`.
     * Incrementa `$cliente->saldo_a_favor` con dicho remanente y guarda el cliente.
3. **Aplicación Automática en `App\Actions\Cobranza\GenerarCargosMensuales`:**
   * Cuando se genera un nuevo cargo mensual para un cliente activo, verifica dentro de la transacción del cliente si `$cliente->saldo_a_favor > 0`.
   * Si tiene saldo a favor, descuéntalo automáticamente del nuevo cargo (actualizando `monto_pagado` y el `estado` del cargo a `'parcial'` o `'pagado'` según alcance, y reduciendo `clientes.saldo_a_favor`).
4. **Refactorización Segura de `App\Actions\Cobranza\AnularPago`:**
   * Bloquea con `lockForUpdate()` tanto el `$pago`, como el `$cliente` y los `$cargos` afectados.
   * Si el pago que se intenta anular generó `$pago->monto_saldo_favor > 0`:
     * Verifica que el cliente aún tenga suficiente `saldo_a_favor` disponible para revertirlo o ajusta el saldo adecuadamente.
   * Para cada cargo vinculado en `aplicaciones_pago`:
     * En lugar de solo restar aritméticamente sin validación, recalcula de forma determinista el `monto_pagado` sumando las aplicaciones de pagos cuyo estado siga siendo `'aplicado'`, garantizando integridad total frente a pagos múltiples sobre un mismo cargo.

### 2.2. Cargo Inicial Automático al Completar Reservación
1. **En `App\Actions\Reservaciones\CompletarReservacion`:**
   * Revisa `config/cobranza.php`. Agrega una clave configurable `'generar_cargo_al_completar_reservacion' => true`.
   * Cuando una reservación de tipo `'instalacion_nueva'` pasa a `'completada'` y el cliente tiene un `plan_id` asignado, si el cliente aún no tiene un cargo de `'mensualidad'` para el periodo actual (`now()->format('Y-m')`), genera de forma idempotente su primer cargo mensual (o permite que el operador lo visualice de inmediato en Cobranza).

---

## FASE 3: Evolución del Dominio WISP / Fibra Óptica en `clientes`

### 3.1. Campos Técnicos de Red y Estado de Suspensión por Mora
Almacenar datos críticos de red (ONT, IP/PPPoE, coordenadas) dentro de un campo de texto libre (`notas`) impide búsquedas rápidas en soporte técnico y futuras integraciones con OLT/MikroTik.

1. **Migración:** Crea una migración `add_network_fields_and_suspendido_state_to_clientes_table`:
   * Modifica la columna `estado` en `clientes` para permitir `('activo', 'suspendido', 'inactivo')` con default `'activo'`. *(Nota: En SQLite para pruebas y MySQL en producción, asegúrate de que la migración sea compatible con ambos drivers).*
   * Agrega las siguientes columnas `nullable` a `clientes`:
     * `serie_ont` (`string`, 100, nullable, index) — Número de serie o MAC de la ONT/Módem.
     * `usuario_pppoe` (`string`, 100, nullable, index) — Usuario PPPoE o IP asignada.
     * `coordenadas_gps` (`string`, 100, nullable) — Latitud, Longitud para instalación/soporte.
2. **Actualización de Modelo, Request y Vistas de Clientes:**
   * Agrega `serie_ont`, `usuario_pppoe`, `coordenadas_gps` y `saldo_a_favor` al modelo `Cliente`.
   * Actualiza el scope de búsqueda en `Cliente` para que los operadores también puedan buscar un cliente por `serie_ont` o `usuario_pppoe`.
   * Actualiza los `FormRequests` (`StoreClienteRequest`, `UpdateClienteRequest`) para validar opcionalmente estos campos y aceptar el estado `'suspendido'`.
   * Agrega los campos técnicos en una sección limpia dentro del formulario de creación/edición de clientes (`resources/views/admin/clientes/`) respetando el diseño Bootstrap 5.3 existente.
3. **Regla de Facturación para Clientes Suspendidos:**
   * En `config/cobranza.php`, define si los clientes en estado `'suspendido'` generan o no cargos mensuales (por defecto `'facturar_suspendidos' => false` para evitar inflar deuda incobrable mientras el servicio está cortado).

---

## FASE 4: Seguridad en Endpoints Públicos (Anti-IDOR, Privacidad PII y Rate Limiting)

### 4.1. Eliminación de Exposición del Padrón en `/soporte/ticket/crear`
**Problema Crítico Actual:** Permitir que cualquier visitante no autenticado en `/soporte/ticket/crear` seleccione un `cliente_id` de una lista pública expone datos personales del padrón o permite crear tickets falsos a nombre de terceros (IDOR).

1. **Refactorización de `PublicTicketController` y `StorePublicTicketRequest`:**
   * En `GET /soporte/ticket/crear`, **NO envíes el listado de todos los clientes a la vista pública**.
   * En el formulario público de creación de ticket, solicita al cliente su **Teléfono registrado** (10 dígitos) —o correo electrónico— junto con la categoría y descripción de la falla.
   * En `StorePublicTicketRequest` / `PublicTicketController@store`:
     * Busca al `Cliente` activo o suspendido cuyo `telefono` coincida con el ingresado.
     * Si no coincide con ningún cliente registrado, devuelve un error de validación amigable en el campo `telefono`: *"No encontramos una cuenta activa asociada a este número telefónico. Verifica tus datos o contáctanos por WhatsApp/teléfono."*
     * Si existe, asocia internamente el `cliente_id` sin haber expuesto jamás el padrón completo.
   * *(Nota de Compatibilidad con Tests)*: Si en el `Request` llega directamente `cliente_id` **Y** además se valida que el `telefono` coincide, o si quieres mantener retrocompatibilidad con tests existentes que envían `cliente_id` en el payload de `POST /soporte/ticket`, permite que si se envía `telefono` se resuelva el cliente por teléfono, y si en entorno de testing se envía `cliente_id`, se valide su existencia sin exponer `Cliente::all()` en el método `create()`.

### 4.2. Enmascaramiento de Datos Sensibles (PII) en Consultas Públicas por Folio
1. **Protección en `/reservaciones/{folio}` y `/soporte/ticket/{folio}`:**
   * Crea un helper o método de presentación en los modelos `Reservacion` y `Ticket` (ej. `$reservacion->nombre_enmascarado`, `$reservacion->direccion_enmascarada`, `$reservacion->telefono_enmascarado`) para las vistas públicas, o requiere los últimos 4 dígitos del teléfono si se desea ver el detalle sensible completo.
   * En la vista pública (`resources/views/public/reservaciones/show.blade.php` y `tickets/show.blade.php`), muestra el estado del folio, fechas, plan, tipo de servicio y observaciones de seguimiento, pero **oculta parcialmente la dirección exacta y el teléfono** (ej. `***-***-4589`) para evitar que alguien que adivine un folio obtenga domicilios particulares.

### 4.3. Rate Limiting en Formularios y Consultas Públicas
1. **En `routes/web.php`:**
   * Aplica middleware `throttle:10,1` (máximo 10 peticiones por minuto por IP) a las rutas `POST` públicas:
     * `POST /reservaciones` (`reservaciones.store`)
     * `POST /soporte/ticket` (`soporte.ticket.store`)
     * `POST /contacto` (`contacto.enviar`)
   * Aplica `throttle:30,1` a las rutas de consulta pública de folios (`reservaciones.consultar`, `soporte.ticket.consultar`).

---

## FASE 5: Suite de Pruebas (PHPUnit 12.5) y Verificación Final

Para dar por concluida la implementación, crea o amplía los archivos de prueba en `tests/Feature/`:

1. **`tests/Feature/CobranzaSaldoAFavorTest.php`:**
   * Verifica que un pago mayor al adeudo total liquida los cargos en orden FIFO y guarda el excedente en `clientes.saldo_a_favor` y `pagos.monto_saldo_favor`.
   * Verifica que al ejecutar `GenerarCargosMensuales`, el `saldo_a_favor` disponible del cliente se aplica automáticamente al nuevo cargo mensual.
   * Verifica que al anular un pago con `AnularPago`, tanto los cargos como el `saldo_a_favor` se revierten con exactitud matemática.
2. **`tests/Feature/BitacoraJsonDiffTest.php`:**
   * Verifica que al actualizar un `Plan` o un `Cliente`, el registro generado en `bitacoras` contiene en `valores_anteriores` el valor previo y en `valores_nuevos` el valor modificado, sin incluir campos sensibles.
3. **`tests/Feature/SeguridadEndpointsPublicosTest.php`:**
   * Verifica que `GET /soporte/ticket/crear` no filtre el listado completo de nombres/direcciones de clientes en el HTML.
   * Verifica que las rutas públicas de creación cuenten con protección de rate limiting (`throttle`).
4. **`tests/Feature/ClienteRedWispTest.php`:**
   * Verifica la creación, edición y búsqueda de clientes por `serie_ont` y `usuario_pppoe`, así como el cambio al estado `suspendido`.

---

## CHECKLIST DE CIERRE PARA EL AGENTE

Antes de reportar la tarea como completada al usuario, ejecuta y confirma que todos los siguientes comandos devuelven **0 errores**:

```bash
# 1. Ejecutar migraciones limpias y en entorno de pruebas
php artisan migrate:fresh --seed

# 2. Formatear todo el código modificado según el estándar oficial de Laravel
vendor/bin/pint --dirty --format agent

# 3. Ejecutar análisis estático con Larastan / PHPStan
vendor/bin/phpstan analyse

# 4. Ejecutar la suite completa de pruebas (deben pasar las +77 originales + las nuevas)
php artisan test --compact
```

Por último, actualiza el archivo `contexto_maestro_para_agente_ia_proyecto_rivas.md` reflejando las nuevas columnas (`saldo_a_favor`, `serie_ont`, `usuario_pppoe`, `coordenadas_gps`, `valores_anteriores`, `valores_nuevos`), el nuevo estado `'suspendido'` y los Enums creados en `app/Enums/`.