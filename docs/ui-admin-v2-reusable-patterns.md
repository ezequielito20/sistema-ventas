# Admin UI v2 — Patrones reutilizables (metodología y componentes)

Este documento resume lo aplicado en el módulo **Roles** (listado Livewire v2) para replicarlo en otros módulos sin redescubrir reglas ni CSS.

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

- [ ] KPI con `x-ui.stat-card` y grid `grid-cols-2 lg:grid-cols-*`.
- [ ] Tabla: `ui-table--nowrap-actions` si la última columna es solo iconos.
- [ ] Columnas numéricas: `text-center` en `th` y `td` (alineación resuelta en SCSS).
- [ ] Acciones: variantes `ui-icon-action--*` y fila `flex-nowrap`.
- [ ] `npm run build` y prueba responsive (≥768px para tabla desktop).
