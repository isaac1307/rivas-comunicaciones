# SYSTEM PROMPT & PROJECT CONTEXT: Rivas Comunicaciones (Laravel Migration)

## 1. DESCRIPCIÓN Y OBJETIVO DEL PROYECTO
**Organización:** Rivas Comunicaciones (Proveedor de Servicios de Internet - ISP local).
**Objetivo Principal:** Desarrollar una plataforma web monolítica en **Laravel** para la promoción de servicios de telecomunicaciones, captación de prospectos (reservaciones de instalación), soporte técnico (tickets digitales) y gestión administrativa integral.
**Contexto de la IA:** Este documento proporciona la arquitectura exacta, base de datos y directrices de código. Como agente de IA, debes seguir estrictamente estas reglas al generar migraciones, modelos, controladores y vistas.

---

## 2. STACK TECNOLÓGICO Y ARQUITECTURA
El proyecto migrará de una arquitectura PHP/MVC nativa al ecosistema estándar de Laravel.

*   **Backend:** Laravel 10/11 (PHP 8.2+).
*   **Base de Datos:** MySQL 8.0.
*   **Frontend (Público y Admin):** Laravel Blade, HTML5, CSS3, JavaScript (Vanilla/ES6), Bootstrap 5.3.
*   **Autenticación:** Laravel Breeze (Blade) o implementación manual estándar con guards para el panel administrativo.
*   **Patrón de Diseño:** MVC estricto. Lógica de negocio en Modelos/Services, validaciones en FormRequests.

---

## 3. ESQUEMA DE BASE DE DATOS Y MODELOS ELOQUENT
A continuación, se detalla el mapeo exacto de las 9 tablas requeridas a Convenciones de Laravel (Migraciones y Modelos). **Regla de IA:** Usa los nombres de tablas y modelos exactamente como se definen aquí, respetando el idioma español para el dominio del negocio, pero usando sintaxis de Laravel.

### 3.1. `User` (Tabla: `usuarios`)
*   **Propósito:** Administradores y operadores del sistema.
*   **Campos de Migración:**
    *   `$table->id();`
    *   `$table->string('nombre_usuario', 50)->unique();`
    *   `$table->string('password');` // (hash bcrypt)
    *   `$table->string('nombre_completo', 150);`
    *   `$table->enum('rol', ['administrador', 'operador'])->default('administrador');`
    *   `$table->enum('estado', ['activo', 'inactivo'])->default('activo');`
    *   `$table->timestamp('ultimo_acceso')->nullable();`
    *   `$table->timestamps();` (Mapea `fecha_registro` a `created_at`)
*   **Relaciones Eloquent:**
    *   `public function bitacoras() { return $this->hasMany(Bitacora::class); }`

### 3.2. `Plan` (Tabla: `planes`)
*   **Campos de Migración:**
    *   `$table->id();`
    *   `$table->string('nombre', 100)->unique();`
    *   `$table->integer('velocidad_descarga');`
    *   `$table->integer('velocidad_carga');`
    *   `$table->decimal('precio_mensual', 8, 2);`
    *   `$table->text('descripcion')->nullable();`
    *   `$table->enum('estado', ['activo', 'inactivo'])->default('activo');`
*   **Relaciones Eloquent:**
    *   `public function clientes() { return $this->hasMany(Cliente::class); }`
    *   `public function reservaciones() { return $this->hasMany(Reservacion::class); }`
    *   `public function promociones() { return $this->belongsToMany(Promocion::class, 'promocion_plan'); }`

### 3.3. `Promocion` (Tabla: `promociones`)
*   **Campos de Migración:**
    *   `$table->id();`
    *   `$table->string('titulo', 200);`
    *   `$table->text('descripcion');`
    *   `$table->date('fecha_inicio');`
    *   `$table->date('fecha_vencimiento');`
    *   `$table->enum('estado', ['activa', 'inactiva'])->default('activa');`
*   **Relaciones Eloquent:**
    *   `public function planes() { return $this->belongsToMany(Plan::class, 'promocion_plan'); }`

### 3.4. Pivot: `promocion_plan`
*   **Campos:** `promocion_id` (foreign), `plan_id` (foreign). Ambos primary key compuesta.

### 3.5. `Cliente` (Tabla: `clientes`)
*   **Campos de Migración:**
    *   `$table->id();`
    *   `$table->string('nombre_completo', 200);`
    *   `$table->string('direccion', 300);`
    *   `$table->string('telefono', 15);`
    *   `$table->string('correo_electronico', 150)->nullable();`
    *   `$table->foreignId('plan_id')->nullable()->constrained('planes')->nullOnDelete();`
    *   `$table->date('fecha_alta')->default(now());`
    *   `$table->enum('estado', ['activo', 'inactivo'])->default('activo');`
    *   `$table->text('notas')->nullable();`
*   **Relaciones Eloquent:**
    *   `public function plan() { return $this->belongsTo(Plan::class); }`
    *   `public function tickets() { return $this->hasMany(Ticket::class); }`

### 3.6. `Reservacion` (Tabla: `reservaciones`)
*   **Campos de Migración:**
    *   `$table->id();`
    *   `$table->string('folio', 20)->unique();` // Formato: RES-YYYYMMDD-XXXX
    *   `$table->string('nombre_solicitante', 200);`
    *   `$table->string('telefono', 15);`
    *   `$table->string('direccion', 300);`
    *   `$table->string('correo_electronico', 150)->nullable();`
    *   `$table->enum('tipo_servicio', ['instalacion_nueva', 'visita_tecnica', 'atencion_personalizada']);`
    *   `$table->foreignId('plan_id')->nullable()->constrained('planes')->nullOnDelete();`
    *   `$table->date('fecha_solicitada')->nullable();`
    *   `$table->text('observaciones')->nullable();`
    *   `$table->enum('estado', ['pendiente', 'programada', 'completada', 'cancelada'])->default('pendiente');`
    *   `$table->timestamps();` // Mapea fecha_registro
*   **Relaciones Eloquent:**
    *   `public function plan() { return $this->belongsTo(Plan::class); }`

### 3.7. `Ticket` (Tabla: `tickets`)
*   **Campos de Migración:**
    *   `$table->id();`
    *   `$table->string('folio', 20)->unique();` // Formato: TKT-YYYYMMDD-XXXX
    *   `$table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();`
    *   `$table->enum('categoria', ['falla_servicio', 'soporte_tecnico', 'consulta', 'cambio_plan', 'cancelacion']);`
    *   `$table->text('descripcion');`
    *   `$table->enum('estado', ['abierto', 'en_proceso', 'resuelto', 'cerrado'])->default('abierto');`
    *   `$table->timestamp('fecha_resolucion')->nullable();`
    *   `$table->text('observaciones_admin')->nullable();`
    *   `$table->timestamps();` // Mapea fecha_creacion
*   **Relaciones Eloquent:**
    *   `public function cliente() { return $this->belongsTo(Cliente::class); }`

### 3.8. `MensajeContacto` (Tabla: `mensajes_contacto`)
*   **Campos de Migración:** `id`, `nombre`, `correo_electronico`, `telefono` (nullable), `asunto`, `mensaje`, `boolean('leido')->default(false)`, `timestamps()`.

### 3.9. `Bitacora` (Tabla: `bitacoras`)
*   **Campos de Migración:** `id`, `foreignId('usuario_id')->constrained('usuarios')`, `string('accion', 50)`, `string('descripcion', 300)`, `string('tabla_afectada', 50)->nullable()`, `unsignedBigInteger('registro_id')->nullable()`, `ipAddress('ip_origen')->nullable()`, `timestamps()`.
*   **Relaciones Eloquent:** `public function usuario() { return $this->belongsTo(User::class); }`

---

## 4. ARQUITECTURA DE ENRUTAMIENTO Y CONTROLADORES

El sistema se divide en dos áreas principales. **Regla de IA:** Usa controladores de recursos (`Resource Controllers`) siempre que sea posible.

### 4.1. Área Pública (Frontend) - Rutas web (`/`)
*   `HomeController`: `index` (landing), `nosotros`, `cobertura`.
*   `PublicPlanController`: `index` (catálogo).
*   `PublicPromocionController`: `index` (promociones vigentes).
*   `PublicReservacionController`: `create`, `store` (generar solicitud), `show` (consultar por folio).
*   `PublicTicketController`: `create`, `store` (generar ticket de cliente), `show` (consultar por folio).
*   `ContactoController`: `store` (procesar formulario).

### 4.2. Panel Administrativo - Rutas web protegidas (`/admin/...`)
*   **Middleware:** `auth` (y opcionalmente middleware de roles custom `role:administrador|operador`).
*   `Admin\DashboardController`: `index` (Estadísticas con counts de Eloquent).
*   `Admin\PlanController`: CRUD completo.
*   `Admin\PromocionController`: CRUD completo (usar `$promocion->planes()->sync()` para pivot).
*   `Admin\ClienteController`: CRUD completo.
*   `Admin\ReservacionController`: `index`, `show`, `edit`, `update` (cambio de estado).
*   `Admin\TicketController`: `index`, `show`, `edit`, `update` (cambio de estado, observaciones).
*   `Admin\MensajeContactoController`: `index`, `show`, `update` (marcar como leído).
*   `Admin\BitacoraController`: `index` (solo lectura para administradores).

---

## 5. DIRECTRICES Y REGLAS ESTRICTAS PARA LA IA (AI PROMPTING RULES)

Cuando se te solicite generar código para este proyecto, **DEBES** adherirte a las siguientes reglas:

1.  **Generación de Folios (Business Logic):**
    *   No generes los folios (RES-... / TKT-...) en el Controlador.
    *   **Obligatorio:** Utiliza **Observers** de Eloquent (ej. `ReservacionObserver@creating`) o el método `booted()` dentro del Modelo para generar el folio único automáticamente antes de guardar.
2.  **Validación de Datos:**
    *   NUNCA valides directamente en el controlador usando `$request->validate()`.
    *   **Obligatorio:** Genera y utiliza clases **FormRequest** (ej. `StoreReservacionRequest`, `UpdateTicketRequest`).
3.  **Consultas a Base de Datos:**
    *   Prohibido el uso de SQL crudo (`DB::raw` o `DB::select`).
    *   **Obligatorio:** Utiliza Eloquent ORM. Emplea *Eager Loading* (`with('plan')`, `with('cliente')`) para solucionar el problema N+1 en las vistas de listado del panel administrativo.
4.  **Vistas (Blade) e UI:**
    *   Crea un layout maestro (`resources/views/layouts/app.blade.php` para público y `admin.blade.php` para backend).
    *   Utiliza la sintaxis estricta de Blade `{{ $variable }}` para prevenir inyección XSS (Requerimiento RNF-04).
    *   Aplica clases nativas de Bootstrap 5 (`container`, `row`, `col-md-6`, `card`, `table-responsive`, etc.) para cumplir con la responsividad (Requerimiento RNF-02).
5.  **Auditoría (Bitácora):**
    *   Para automatizar la tabla `bitacoras`, sugiere la implementación de un trait genérico o el uso de Observers globales en los modelos principales que registren las acciones (created, updated, deleted) capturando la IP con `request()->ip()`.
6.  **Respuestas de la IA:**
    *   Al generar código, proporciona el archivo completo (sin omitir partes lógicas) a menos que se indique lo contrario.
    *   Usa siempre el idioma español para comentarios, variables, rutas públicas y vistas, pero mantén las palabras clave de PHP/Laravel en inglés estándar.