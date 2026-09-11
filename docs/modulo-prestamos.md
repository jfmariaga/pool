# Módulo de Préstamos — Base de datos y carga inicial

Documenta el esquema del módulo y los **casos actuales cargados desde**
`docs/Tablas requerimientos.xlsx` (hojas *Contratos Actuales* y *Prestamos Personales*),
con corte al **2026-09-10**.

Seeder: [`database/seeders/PrestamosExcelSeeder.php`](../database/seeders/PrestamosExcelSeeder.php)

```bash
php artisan db:seed --class=PrestamosExcelSeeder   # trunca las 4 tablas y recarga
```

---

## 0. Estado del desarrollo (última sesión: 2026-09-10)

### Hecho
- **Migraciones** (`database/migrations/2026_09_10_1200*`–`1205*`): `prestamo_inversionistas`,
  `prestamo_clientes` (cédula **única**), `prestamos`, `prestamo_movimientos`, permisos
  (`ver/crear/editar/eliminar prestamos`, `ver/gestionar prestamo-inversionistas`).
- **Modelos**: `Prestamo` (con todos los cálculos como accessors), `PrestamoMovimiento`,
  `PrestamoCliente`, `PrestamoInversionista`.
- **Componente Livewire** `App\Livewire\Prestamos\Prestamos` + vistas en
  `resources/views/livewire/prestamos/` (`prestamos`, `form-prestamo`, `form-pago`,
  `form-inversionista`).
- **Ruta** `/prestamos` (`web.php`) + ítem de menú en *Contabilidad*.
- **Pestañas**: Dashboard retroventa · Dashboard préstamos · Retroventa (listado) ·
  Préstamos personales (listado) · Inversionistas. Cada dashboard con sus KPIs.
- Tablas de listado **compactas**; el detalle completo (prenda, cédula, promedio, tasa,
  trazabilidad de movimientos) está en el modal del botón 👁.
- Reglas implementadas y verificadas: interés sobre capital vigente; **primer mes causado
  desde la entrega** (accessor `meses_causados = meses_corridos + 1`, tope `plazo_meses` en
  retroventa); plazo dinámico de 4 meses que se corre con cada pago de interés; adjudicación
  de prenda al vencer; columna **MESES del Excel = meses pagados** (corre el vencimiento).
- **Dark mode**: correcciones en `public/css/theme.css` (nav-tabs, filas resaltadas,
  paginación DataTables, badges, KPI cards). Meses en español corregidos en `public/js/basic.js`.
- **Datos cargados** desde el Excel (ver secciones 2 y 3). Estado retroventa: 22 activos /
  1 vencido / 2 pagados. Capital vigente total ≈ $119 M.

### Pendiente / para revisar
- Confirmar las **tasas de reparto por inversionista** (hoy: SAMIR/YAMILE/KEVIN 7 %,
  GIOVAN/LAURA/SOL 5 %, tomadas de las fórmulas del Excel) — o eliminar el concepto de
  "ganancia casa" si no aplica.
- Confirmar el **ajuste automático de la columna MESES** cuando el Excel marca `ACTIVO`
  pero ya estaría vencido (hoy se sube al mínimo para mantenerlo vigente).
- Revisar reglas finas de **pagos parciales de interés** (menos de un mes) — el esquema
  (`saldo_interes_favor`) ya lo soporta pero falta acordar el comportamiento exacto.
- Definir si **Préstamos personales** necesitan fecha de vencimiento / plazo (hoy no tienen).
- UX del formulario de nuevo préstamo (autocompletar cliente por cédula, tasa por defecto).
- Repasar dark mode en el resto de la aplicación (fuera del módulo).

---

## 1. Esquema

### `prestamo_inversionistas`
Dueños del capital de las retroventas. Su `tasa` es lo que **gana** el inversionista al mes.

| Columna | Tipo | Nota |
|---|---|---|
| id | bigint PK | |
| nombre | string | |
| tasa | decimal(6,4) | fracción mensual (0.05 = 5 %) |
| telefono | string null | |
| activo | tinyint | 1/0 |
| timestamps | | |

### `prestamo_clientes`
Catálogo propio del módulo (no se relaciona con la tabla `clientes` del sistema).
Se crea con `firstOrCreate` por `cedula` (retroventa) o por `nombre` (personales).

| Columna | Tipo |
|---|---|
| id | bigint PK |
| nombre | string |
| cedula | string null · **UNIQUE** | no se puede repetir; en retroventa es obligatoria |
| telefono | string null |
| timestamps | |

> La cédula tiene índice **único** (migración `2026_09_10_120500_make_prestamo_clientes_cedula_unique`).
> Al registrar un préstamo, si la cédula ya existe se **reutiliza** ese cliente
> (`firstOrCreate` por `cedula`). Varias filas con `cedula = NULL` sí están permitidas
> (préstamos personales que no capturan cédula).

### `prestamos`
Fila = un préstamo. `modalidad` distingue las dos pestañas.

| Columna | Tipo | Aplica a | Nota |
|---|---|---|---|
| id | bigint PK | | |
| modalidad | enum(`retroventa`,`personal`) | | |
| cliente_id | FK prestamo_clientes | ambas | |
| inversionista_id | FK prestamo_inversionistas null | retroventa | |
| cartera | string null | personal | `General` / `Laura` / `Mamá` |
| num_contrato | string null | retroventa | |
| peso | decimal(10,2) null | retroventa | gramos de la prenda |
| descripcion_prenda | text null | retroventa | |
| fecha_inicio | date | ambas | |
| fecha_corte | date | ambas | fecha desde la que corre el interés no pagado |
| fecha_cierre | date null | ambas | se llena al pagar/adjudicar |
| monto | decimal(15,2) | ambas | capital inicial |
| saldo_capital | decimal(15,2) | ambas | capital vigente |
| tasa_interes | decimal(6,4) | ambas | fracción mensual que paga el cliente |
| plazo_meses | tinyint (def 4) | retroventa | |
| saldo_interes_favor | decimal(15,2) def 0 | ambas | pagos de interés que aún no completan un mes |
| estado | enum(`activo`,`pagado`,`adjudicado`) | ambas | `vencido` **no se guarda**, se deriva |
| observacion | text null | | |
| usuario_id | bigint null | | usuario que registró |
| timestamps | | | |

**Valores calculados** (accessors del modelo `Prestamo`, no se persisten):
`meses_corridos` (meses completos transcurridos desde el corte),
`meses_causados` (**= meses_corridos + 1**: el primer mes se causa desde que se entrega el
dinero; 0 si el corte está a futuro por interés prepagado; en retroventa se limita a
`plazo_meses`, de modo que a los 4 meses se deben 4 meses de interés, no más),
`interes_mensual` (= saldo_capital × tasa_interes),
`interes_causado` (= interes_mensual × **meses_causados** − saldo_interes_favor),
`fecha_vencimiento` (= fecha_corte + plazo_meses, solo retroventa) — nótese que en el día 0
ya hay 1 mes causado pero **aún no está vencido**,
`esta_vencido`, `estado_mostrar` (`activo` | `vencido` | `pagado` | `adjudicado`),
`promedio` (= monto / peso), `total_adeudado` (= saldo_capital + interes_causado),
`interes_inversionista_mensual` (= saldo_capital × tasa del inversionista).

### `prestamo_movimientos`
Trazabilidad. Un registro por evento del préstamo.

| Columna | Tipo | Nota |
|---|---|---|
| id | bigint PK | |
| prestamo_id | FK prestamos (cascade) | |
| fecha | date | fecha del evento |
| tipo | enum | `desembolso`, `pago_interes`, `abono_capital`, `mixto`, `adjudicacion`, `cancelacion` |
| monto_interes | decimal(15,2) | parte del pago aplicada a interés |
| monto_capital | decimal(15,2) | parte del pago aplicada a capital |
| capital_antes / capital_despues | decimal(15,2) | saldo de capital antes/después |
| fecha_corte_antes / fecha_corte_despues | date null | movimiento de la fecha de corte |
| meses_cubiertos | tinyint | meses completos de interés que cubrió el pago |
| interes_causado | decimal(15,2) | interés que se debía al momento del movimiento |
| observacion | text null | |
| usuario_id | bigint null | |
| created_at | timestamp null | |

### Reglas de cálculo (implementadas y verificadas)
- El interés corre sobre `saldo_capital`. Abonar a capital reduce el interés futuro (ambas modalidades).
- Un pago **cubre primero todo el interés causado**; el excedente abona a capital (tipo `mixto`).
- Cada mes completo de interés pagado adelanta `fecha_corte` un mes. En retroventa eso
  recorre `fecha_vencimiento` → "el cliente vuelve a tener 4 meses".
- **Retroventa**: plazo fijo de 4 meses. Al superar `fecha_vencimiento` el préstamo se
  muestra como `vencido` y habilita **Adjudicar prenda** (`estado = adjudicado`, queda el
  movimiento con capital e interés causado).
- **Personales**: mismo mecanismo, sin vencimiento ni adjudicación.

---

## 2. Datos cargados (corte 2026-09-10)

### Retroventa — hoja *Contratos Actuales* (25 contratos)

| Métrica | Valor |
|---|---|
| Contratos importados | 25 |
| Activos | 19 |
| Vencidos (derivado por fecha) | 4 |
| Pagados (Excel `RETIRADO`) | 2 — JUAN ARBELODA, JOHANA RAMIREZ (SAMIR) |
| Capital vigente | $74.720.000 |

Inversionistas y tasa de reparto (del tablero resumen de la hoja 1; cliente paga 7 %):

| Inversionista | Tasa que gana | Nº contratos | Capital | Interés inversionista/mes | Ganancia casa/mes |
|---|---|---|---|---|---|
| SAMIR | 7 % | 6 | $29.100.000 | $2.037.000 | $0 |
| YAMILE | 7 % | 3 | $19.730.000 | $1.381.100 | $0 |
| KEVIN | 7 % | 2 | $5.200.000 | $364.000 | $0 |
| GIOVAN | 5 % | 7 | $12.520.000 | $626.000 | $250.400 |
| LAURA | 5 % | 4 | $5.870.000 | $293.500 | $117.400 |
| SOL | 5 % | 1 | $2.300.000 | $115.000 | $46.000 |
| **Total** | | **23 activos** | **$74.720.000** | | |

(Estas cifras coinciden con el bloque C2:G8 del Excel.)

### Préstamos personales — hoja *Prestamos Personales* (34 préstamos)

| Cartera | Tasa | Registros | Activos | Pagados |
|---|---|---|---|---|
| General | 5 % | 26 | 14 | 12 |
| Laura | 3 % (7 % en operaciones `EMPEÑO`) | 7 | 2 | 5 |
| Mamá | 5 % | 1 | 1 | 0 |

Capital vigente personales: **$44.280.000**.
Cada préstamo trae su movimiento `desembolso` y, si tenía abono en el Excel, un
movimiento `abono_capital` por el total abonado.

---

## 3. Decisiones de mapeo (Excel → BD)

| Situación en el Excel | Cómo se cargó |
|---|---|
| Columna **MESES** del Excel | Son los meses de interés que el cliente **ya pagó**. Cada mes pagado corre el vencimiento un mes: `fecha_corte = fecha_inicio + MESES` y se registra un movimiento `pago_interes` por esos meses. Si el Excel marca el contrato `ACTIVO` pero con esos MESES ya estaría vencido, la columna estaba atrasada y se sube al mínimo que lo mantiene vigente (queda anotado en la observación del movimiento). Se ve en la columna **"Meses pag."** de la tabla y en el detalle. |
| Interés de personales (el Excel mostraba **un mes**, `SALDO × tasa`, no acumulado) | `fecha_corte = hoy` al importar → como el primer mes se causa de inmediato, el interés causado al momento de la carga es de **1 mes** (`saldo × tasa`), igual que el Excel. |
| Estado **RETIRADO** (retroventa) | `estado = pagado`, `saldo_capital = 0`, movimiento `cancelacion`, `fecha_cierre = hoy`. |
| Estado **VENCIDO** (retroventa) | Se importa como `activo`; el sistema lo muestra `vencido` si `hoy > fecha_vencimiento`. Un contrato que el Excel marcaba vencido pero lleva < 4 meses **no** aparecerá vencido (el Excel se marcaba a mano). |
| **DUEÑO** de la retroventa | Inversionista (catálogo `prestamo_inversionistas`). Tasa de reparto tomada de las fórmulas del tablero (D2:D7). |
| Cartera **Laura** con filas `EMPEÑO` a 7 % | Esos préstamos se cargan con `tasa_interes = 0.07`; el resto de Laura a `0.03`. |
| Abono agregado del Excel (una sola celda) | Un único movimiento `abono_capital` con la fecha del préstamo (no hay fechas de abono individuales en el Excel). |
| Filas de agregación / totales de la hoja 2 | Se omiten (no son préstamos). |
| `PESO = 0` (RELOJ CARTIER) | Se guarda 0; `promedio` devuelve 0 (evita división por cero). |
