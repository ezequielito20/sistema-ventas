# Admin UI v2 — Patrones reutilizables (metodología y componentes)

Este documento resume lo aplicado en el módulo **Roles** (listado admin v2) para replicarlo en otros módulos sin redescubrir reglas ni CSS.

## Orden y distribución en pantalla (convención)

1. **`ui-panel`** superior: título (`ui-panel__title`), subtítulo corto y orientado al usuario (`ui-panel__subtitle`), acciones a la derecha (`ui-btn` PDF / Nuevo, etc.).
2. **KPI:** grid `grid-cols-2 gap-2 xs:gap-3 lg:grid-cols-4` + `<x-ui.stat-card>` (una tarjeta por métrica).
3. **`ui-panel`** de filtros (si aplica): solo lo necesario; **misma fila** para campos relacionados.
4. **`ui-panel`** de listado: título “Listado” + subtítulo con totales/paginación; cuerpo con tabla y, si aplica, cards móviles debajo del breakpoint de tabla.

**Texto visible:** subtítulos y labels en lenguaje de negocio; **no** incluir nombres de stack (“Livewire”, versiones) en copy de UI.

## Metodología

1. **Vistas en paralelo**  
   - Shell: `resources/views/admin/v2/<modulo>/…`  
   - Componente Livewire: `app/Livewire/<Name>.php` + `resources/views/livewire/<kebab>.blade.php`  
   - El controlador apunta solo a la vista v2; **no reescribir** Blades legacy salvo acuerdo explícito.

2. **Un solo origen de estilo**  
   - Tokens: `resources/sass/_ui-tokens.scss`  
   - Design system: `resources/sass/app.scss` (`@layer components` + reglas finales **sin** `@layer` cuando haya que vencer CSS legacy cargado después de Vite, p. ej. `public/css/shared/components.css`).

3. **Build**  
   - Tras cambios SCSS: `npm run build` (o `npm run dev` en desarrollo).

4. **Referencias de pantalla**  
   - Rutas de preview existentes: `admin.ui.notifications.preview`, `admin.ui.design-system.preview`, `admin.ui.charts.preview`, `admin.ui.shell-preview`.

## Componentes Blade

| Uso | Componente / clase | Ubicación / notas |
| --- | --- | --- |
| KPI / métricas arriba del listado | `<x-ui.stat-card>` | `resources/views/components/ui/stat-card.blade.php` — envuelve `ui-widget` + `ui-widget--dense` + variante de color. |
| Contenedor de página / bloque | `ui-panel`, `ui-panel__header`, `ui-panel__body` | `app.scss` |
| Botones principales (PDF, Nuevo, etc.) | `ui-btn`, `ui-btn-primary`, `ui-btn-ghost` + `md:py-2.5 md:px-5 md:text-[0.95rem]` en **md+** | |
| Tabla de datos | `ui-table-wrap` > `ui-table` | Opcional: clase modificadora (ver abajo). |
| Badges | `ui-badge`, `ui-badge-success`, `ui-badge-warning`, … | |

## Fila de filtros (búsqueda + selects)

- Poner **todos los controles en una sola fila** mientras quepa: usar **`flex flex-row flex-nowrap items-end gap-3 sm:gap-4`**, campo principal con **`flex-1 min-w-0 basis-0`**, campos angostos (select) con **ancho fijo** (`w-36 sm:w-44` o similar) y **`shrink-0`**.
- **Evitar** `grid` con **`grid-cols-[…]`** arbitrarios como patrón principal: en algunos builds puede no reflejarse y los campos se apilan; **flex** es más predecible.
- Mantener filtros al mínimo necesario (en Roles solo búsqueda + tipo; sin secciones colapsables salvo requisito explícito).

## Tablas de listado

### Clase `ui-table--nowrap-actions`

Para tablas con columna final solo de iconos:

- Añadir en `<table>`: `class="ui-table ui-table--nowrap-actions"`.
- Efecto: `table-layout` sigue siendo **automático** (reparto natural, sin `table-fixed` ni `colgroup` salvo necesidad futura).
- Última columna: `width: 1%` + `white-space: nowrap` para que la columna no “coma” un desierto horizontal y los iconos no partan línea.
- Fila de iconos: `ui-icon-action-row` con **`flex-nowrap`** (y gaps razonables).

**Evitar** en listados genéricos: `table-layout: fixed` + `colgroup` con anchos agresivos; en pantallas anchas puede agrupar columnas a la derecha o crear huecos raros. Si hace falta fijar anchos en otro módulo, documentarlo en el commit y probar en 1024px y 1366px.

### Alineación encabezado ↔ celdas numéricas

En `app.scss`, `.ui-table thead th { text-align: left }` tiene **más especificidad** que la utilidad Tailwind `text-center` en el `<th>`.

Por eso existen reglas explícitas:

- `.ui-table thead th.text-center`
- `.ui-table tbody td.text-center`

Usar **`text-center`** en `<th>` y `<td>` de columnas numéricas para que títulos y valores queden alineados.

## Acciones por icono (filas)

- Botón o enlace: `ui-icon-action` + variante:
  - `ui-icon-action--info` — ver / detalle  
  - `ui-icon-action--warning` — permisos / clave  
  - `ui-icon-action--primary` — editar  
  - `ui-icon-action--danger` — eliminar  
- Contenedor: `ui-icon-action-row` (flex, **nowrap** en tablas).
- Los colores del glifo Font Awesome pueden perderse por orden de carga / capas CSS; al final de `app.scss` hay reglas **fuera de `@layer`** con `!important` por variante para el botón y el `<i>`.

## Regla Cursor del proyecto

Convenciones v2 resumidas en **`.cursor/rules/ui-admin-v2.mdc`** (widgets, tabla, commits de diseño).

## Checklist rápido al copiar a otro módulo

- [ ] Orden de bloques: panel cabecera → KPI → filtros → listado (tabla + móvil si aplica).
- [ ] Subtítulos legibles para el usuario final (sin mencionar stack en pantalla).
- [ ] KPI con `x-ui.stat-card` y grid `grid-cols-2 lg:grid-cols-*`.
- [ ] Filtros: **flex** `flex-nowrap`, campo principal `flex-1`, selects con ancho fijo.
- [ ] Tabla: `ui-table--nowrap-actions` si la última columna es solo iconos.
- [ ] Columnas numéricas: `text-center` en `th` y `td` (alineación resuelta en SCSS).
- [ ] Acciones: variantes `ui-icon-action--*` y fila `flex-nowrap`.
- [ ] `npm run build` y prueba responsive (≥768px para tabla desktop).
