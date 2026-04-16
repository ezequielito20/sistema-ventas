### Manual del Módulo de Permisos

*(explicado como para un niño de 7 años curioso)*

---

## 1. ¿Qué es un “permiso”?

Sigue la misma idea de la **casa grande**:

- Un **rol** es la **llave** (por ejemplo “Vendedor”, “Cajero”).  
- Un **permiso** es lo que esa llave **puede hacer**:
  - “Puede abrir la puerta de Ventas”.
  - “Puede crear una venta nueva”.
  - “Puede ver el reporte de ganancias”.

Entonces:

- Un **permiso** es una **acción específica** que el sistema permite o prohíbe.  
- Los **roles** son colecciones de permisos.

En este módulo vas a ver y administrar **toda la lista de permisos del sistema**.

---

## 2. Qué ves al entrar al módulo de Permisos

La pantalla se parece bastante a la de Roles y tiene estas partes:

1. **Cabecera** (título y botones de acción).  
2. **Tarjetas de resumen** (si las tienes configuradas).  
3. **Bloque de filtros** (para buscar permisos).  
4. **Tabla de permisos** (lista detallada).  
5. **Paginación y selector de registros por página**.

---

## 3. Cabecera de Permisos

Arriba de todo:

- **Título:** `Permisos`  
  Te recuerda que estás viendo las capacidades finas del sistema.
- **Subtítulo:** algo como “Gestión detallada de permisos” (puede variar según tu copy).

Botones típicos (dependen de los permisos del usuario):

- **Ver PDF** (si lo tienes configurado)  
  - Ícono `fa-file-pdf`.  
  - ¿Qué hace?  
    - Abre un **reporte PDF** con la lista de permisos.  
    - Se abre en otra pestaña, sin cerrar el módulo.

- **Otros botones opcionales**  
  - Podrías tener botones para sincronizar permisos, recargar o tareas especiales, según cómo amplíes el sistema.

Si un usuario no tiene permiso para reportes, ese botón no aparecerá.

---

## 4. Filtros del módulo de Permisos

### 4.1. Mostrar / ocultar filtros

Como en otros módulos:

- Botón “**Filtros avanzados**” / “**Ocultar filtros**”.  
- Cambia el ícono de filtro (`fa-filter`) a sliders (`fa-sliders-h`).  
- Sirve para mostrar u ocultar el formulario de filtros.

### 4.2. Filtros disponibles

En el panel de filtros de Permisos, normalmente verás:

- **Buscar** (`search`)  
  - Campo de texto con lupa.  
  - ¿Qué hace?
    - Busca permisos por su **nombre técnico** o por una etiqueta más amigable.  
    - Si escribes “ventas”, verás permisos relacionados con ventas.

- **Módulo** (`module`)  
  - Select donde eliges el **grupo o módulo** de negocio:  
    - Ejemplos: `ventas`, `productos`, `clientes`, `caja`, etc.  
  - ¿Qué hace?
    - Muestra solo permisos que pertenecen a ese módulo.  
    - Ejemplo: si eliges `ventas`, ves permisos como:
      - `ventas.index`, `ventas.create`, `ventas.report`, etc.

- **Guard** (`guard`)  
  - Select que indica el “guard” de Laravel (por defecto suele ser `web`).  
  - Si manejas varios guards, aquí puedes filtrar por uno en particular.

- **Limpiar filtros**  
  - Botón que deja:
    - `search` vacío.  
    - `module` vacío.  
    - `guard` vacío.  
  - Vuelve a la **primera página** con **todos los permisos**.

Cada cambio en estos filtros:

- Refresca la tabla automáticamente.  
- Te lleva a la **página 1** para que no te pierdas.

---

## 5. La tabla de permisos

La tabla es el “cuaderno grande” con todas las acciones que el sistema conoce.

### 5.1. Columnas principales

Las columnas más importantes suelen ser:

- **Selección** (checkbox, cuando el modo selección está activo)  
  - Sirve para marcar varios permisos a la vez.

- **Nombre del permiso** (`name`)  
  - Es el nombre **técnico**, por ejemplo:
    - `sales.index`  
    - `categories.create`  
    - `users.report`
  - Es el nombre que usa el backend para verificar acceso.

- **Etiqueta amigable** (`label`)  
  - Texto más entendible para humanos, usando `PermissionFriendlyNames`.  
  - Ejemplos:
    - “Ver listado de ventas”  
    - “Crear categoría”  
    - “Ver reporte de usuarios”

- **Guard** (`guard_name`)  
  - Normalmente `web`.  
  - Indica el contexto de autenticación donde aplica el permiso.

- **Uso en roles / usuarios**  
  - El módulo puede mostrar:
    - Cuántos **roles** tienen ese permiso.  
    - Cuántos **usuarios** están afectados a través de esos roles.  
  - Esto ayuda a saber si el permiso **es importante** o casi no se usa.

- **Fechas**  
  - `Creado` y `Actualizado` (formato `d/m/Y H:i`).  
  - Útil para ver si es un permiso nuevo o antiguo.

- **Acciones**  
  - Columna con íconos para ver detalle, y en algunos casos editar o eliminar.

### 5.2. Botones de acción en cada fila

Según tu configuración actual, lo más habitual es:

- **Ver detalle (`fa-eye`)**  
  - ¿Qué hace?  
    - Abre un **modal** con información detallada:
      - Nombre técnico.  
      - Etiqueta amigable.  
      - Guard.  
      - Cuántos usuarios lo tienen a través de roles.  
      - Cuántos roles lo usan y sus nombres.
  - No hace cambios; solo muestra información.

- **Editar** (si llegas a habilitarlo)  
  - Podría permitir cambiar nombre o guard, pero en muchos sistemas esto está bloqueado por ser delicado.

- **Eliminar (`fa-trash`)**  
  - ¿Qué hace?  
    - Abre un modal preguntando si quieres borrar el permiso.  
    - El sistema:
      - Comprueba si es seguro eliminarlo.  
      - Puede bloquear permisos “críticos” y mostrar un mensaje explicando el porqué.

En el modal de eliminación:

- Si confirmas, el permiso se elimina (y se limpia de los roles que lo tenían, según tu lógica).  
- Si cancelas, no se hace nada.

---

## 6. Modo selección y borrados masivos

Al igual que en Roles:

- Hay un botón **“Seleccionar” / “Cancelar selección”** que activa el modo selección múltiple.  
- Cuando está activo:
  - Aparece un checkbox en la cabecera de la tabla.  
  - Aparecen checkboxes en cada permiso.

Acciones en este modo:

- **“Seleccionar página” / “Limpiar página”**  
  - Marca o desmarca todos los permisos visibles en la **página actual**.

- **“Eliminar seleccionados”**  
  - Intenta borrar todos los permisos marcados.  
  - El sistema:
    - Borra los permisos que se pueden borrar.  
    - Informa si algunos no pudieron borrarse (y por qué).

Este modo es muy útil para **limpiar permisos antiguos**, de prueba o que ya no se necesitan.

---

## 7. Paginación y registros por página

En la parte de abajo de la tabla encontrarás:

- Un texto tipo:  
  “Mostrando 1 a 10 de 243 resultados”.

- El **selector de registros por página**:  
  - Texto “Registros por página:”  
  - Select con opciones: `10`, `25`, `50`, `100`.  
  - ¿Qué hace?
    - Cambia cuántos permisos se muestran en cada página.  
    - Siempre vuelve a la **página 1** cuando cambias el valor.

- Los **controles de paginación**:
  - En móvil:
    - Botones **“Anterior”** y **“Siguiente”**.  
  - En escritorio:
    - Flechas izquierda/derecha.  
    - Números de página (1, 2, 3, …).

Sirven para navegar tranquilamente por páginas de resultados sin perder el contexto.

---

## 8. Flujos típicos dentro del módulo de Permisos

### 8.1. Revisar qué permisos existen en un módulo concreto

Ejemplo: quieres ver todo lo relacionado con ventas.

1. Entras a **Permisos**.  
2. En el filtro **Módulo**, seleccionas `ventas` (o el nombre equivalente que uses).  
3. La tabla muestra solo permisos que pertenecen a ventas:
   - `ventas.index`, `ventas.create`, `ventas.report`, etc.  
4. Si hace falta, usas el campo **Buscar** para refinar aún más (por ejemplo, escribiendo “reporte”).

### 8.2. Ver a detalle un permiso importante

Ejemplo: `sales.report`.

1. Lo localizas con el filtro “Buscar” o usando el filtro de módulo adecuado.  
2. En la fila de ese permiso, haces clic en **Ver detalle** (`fa-eye`).  
3. En el modal ves:
   - Nombre técnico: `sales.report`.  
   - Qué significa (etiqueta amigable): “Ver reporte de ventas”.  
   - Guard: `web`.  
   - Cuántos usuarios y roles lo están usando.
4. Con esa información decides si quieres:
   - Ajustar roles en el módulo de **Roles** (dar o quitar este permiso).  
   - O dejar todo igual porque está bien configurado.

### 8.3. Limpiar permisos que ya no se usan

1. Activas el **modo selección**.  
2. Usas filtros para encontrar permisos viejos, de prueba o de módulos que ya no existen.  
3. Marcas con los checkboxes los permisos que quieres borrar.  
4. Haces clic en **“Eliminar seleccionados”**.  
5. Confirmas la operación en el modal.  
6. El sistema:
   - Elimina los permisos válidos.  
   - Te muestra si algún permiso no se pudo borrar y por qué.

---

## 9. Cómo explicar Permisos a un cliente

Una explicación simple que puedes usar al vender el sistema:

> “Aquí vemos la lista completa de **cosas que el sistema permite hacer**.  
> Cada fila es un permiso, como ‘ver ventas’ o ‘crear productos’.  
> Usamos filtros para encontrar permisos específicos y una tabla para ver quién los usa.  
> Desde aquí puedes revisar qué acciones existen y limpiar las que ya no tienen sentido.  
> Luego, en el módulo de Roles, decides **qué conjunto de permisos** darle a cada tipo de usuario (admin, vendedor, cajero, etc.).”

