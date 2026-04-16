### Manual del Módulo de Roles

*(explicado como para un niño de 7 años curioso)*

---

## 1. ¿Qué es un “rol”?

Imagina que tu empresa es una **casa grande** y que cada persona tiene una **llave especial**:

- Algunos tienen una llave que abre **todas** las puertas (son como “admin”).
- Otros solo pueden entrar a la **cocina** (por ejemplo, “Vendedor”).
- Otros solo pueden entrar a la **caja** (por ejemplo, “Cajero”).

Un **rol** es esa **llave especial** que dice **qué puede hacer y qué no puede hacer** cada usuario dentro del sistema.

En este módulo vas a **crear, ver, editar y borrar** esos roles.

---

## 2. Qué ves al entrar al módulo de Roles

Cuando abres “Roles” (desde el menú de seguridad o configuración), la pantalla tiene **cuatro partes principales**:

1. **Cabecera** (título y botones grandes de arriba).
2. **Tarjetas de resumen** (widgets).
3. **Bloque de filtros** (para buscar y filtrar).
4. **Tabla de roles** (lista con filas) + paginación y acciones.

---

## 3. Cabecera de Roles

Arriba de todo ves:

- **Título:** `Roles`  
  Te recuerda en qué módulo estás.
- **Subtítulo:** `Listado y permisos en tiempo real.`  
  Te dice que aquí gestionas roles y sus permisos.

A la derecha hay **botones grandes**:

- **Botón “Ver PDF”**  
  - Ícono: `fa-file-pdf` (un iconito de PDF).  
  - ¿Qué hace?  
    - Abre un **reporte en PDF** con la lista de roles.  
    - Se abre en otra pestaña del navegador, así no pierdes la vista actual.

- **Botón “Nuevo rol”**  
  - Ícono: `fa-plus` (un símbolo de más).  
  - ¿Qué hace?  
    - Te lleva a la pantalla donde puedes **crear un rol nuevo**.  
    - Ejemplos de nombres de rol: `Vendedor`, `Cajero`, `Supervisor`.

Según los permisos del usuario, alguno de estos botones puede **no aparecer**.

---

## 4. Tarjetas de resumen (widgets de arriba)

Debajo de la cabecera hay **4 tarjetas pequeñas** que te dan un resumen rápido:

1. **Roles**  
   - Muestra cuántos roles existen en tu empresa.  
   - Texto orientativo: “Roles · En tu empresa”.

2. **Usuarios**  
   - Muestra cuántos usuarios hay en total.  
   - Te da idea de cuántas personas usan el sistema.

3. **Permisos**  
   - Muestra cuántos permisos distintos tiene el sistema.  
   - Un permiso es una “acción específica”, como “ver ventas” o “crear productos”.

4. **Roles de sistema**  
   - Muestra cuántos roles “especiales” del sistema hay (por ejemplo `admin`, `user`, `superadmin`).  
   - Normalmente estos son más delicados y no se deben borrar ni modificar a la ligera.

Estas tarjetas **solo muestran información**, no tienen botones.  
Sirven para que, de un vistazo, sepas:

> “¿Mi sistema está creciendo? ¿Cuántos roles y usuarios tengo?”

---

## 5. Bloque de filtros

### 5.1. Cómo se abre y cierra

Hay un panel llamado **“Filtros”** con un botón que dice:

- “**Filtros avanzados**” (cuando los filtros están ocultos), o  
- “**Ocultar filtros**” (cuando los filtros están visibles).

Este botón:

- Cambia de ícono entre `fa-filter` y `fa-sliders-h`.  
- Muestra u oculta los **campos de búsqueda y filtro**.  
- Es útil para tener la pantalla limpia si no estás usando filtros.

### 5.2. Filtros disponibles

Dentro del panel de filtros, normalmente verás:

- **Buscar**  
  - Campo de texto con ícono de lupa.  
  - ¿Qué hace?  
    - Filtra los roles por **nombre**.  
    - Si escribes “admin”, solo verás roles que contengan “admin”.

- **Tipo de rol** (`role_type`)  
  - Un select con opciones, por ejemplo:
    - `Todos`  
    - `System` (roles del sistema, como `admin`, `user`, `superadmin`)  
    - `Custom` (roles personalizados creados por la empresa)
  - ¿Qué hace?  
    - Permite ver solo:
      - **Roles del sistema**, o  
      - **Roles personalizados**, o  
      - **Todos**, según lo que elijas.

- **Botón “Limpiar filtros”**  
  - ¿Qué hace?  
    - Vacía la búsqueda y el tipo de rol.  
    - Vuelve a mostrar **todos** los roles.  
    - Vuelve a la **primera página** del listado.

Cada vez que cambias algo en los filtros:

- El sistema **refresca** la tabla de abajo.  
- Siempre se regresa a la **página 1** para que no te confundas.

---

## 6. La tabla de roles

Abajo está la **tabla principal**. Piensa en ella como un **cuaderno con filas**, donde cada fila es un rol.

### 6.1. Columnas típicas

Las columnas más importantes son:

- **(Opcional) Columna de selección**  
  - Aparece cuando activas el **modo selección** (ver sección 7).  
  - Sirve para marcar varios roles a la vez.

- **Rol / Nombre del rol**  
  - Muestra el nombre del rol (ejemplo: `Admin`, `Vendedor`).  
  - Suele ir acompañado de un pequeño ícono o avatar.

- **Usuarios**  
  - Indica cuántos usuarios están usando ese rol.  
  - Sirve para saber si un rol “está en uso” o está vacío.

- **Permisos / métricas relacionadas**  
  - Dependiendo de la vista, puede mostrar cuántos permisos tiene ese rol o aparecer solo en el modal de detalle.

- **Creado / Actualizado**  
  - Fechas de creación y actualización del rol.  
  - Útil para auditoría: saber si es un rol reciente o muy antiguo.

- **Acciones**  
  - Una columna con **botones pequeñitos** (íconos) para ver, editar, gestionar permisos, borrar, etc.

### 6.2. Botones de acción en cada fila

En la última columna “Acciones” verás varios íconos:

- **Ver detalle (`fa-eye`)**  
  - ¿Qué hace?  
    - Abre un **modal** con la información del rol:
      - Nombre  
      - Cuántos usuarios lo usan  
      - Cuántos permisos tiene  
      - Fechas de creación y actualización
  - No modifica nada, solo muestra información.

- **Editar (`fa-edit`)**  
  - ¿Qué hace?  
    - Te lleva a la pantalla donde puedes **cambiar el nombre del rol** u otros datos básicos.  
    - No manipula permisos directamente (eso suele tener su propio botón).

- **Permisos** (ícono de llave, por ejemplo `fa-key`, según tu configuración)  
  - ¿Qué hace?  
    - Abre un **modal especial** donde puedes **marcar o desmarcar permisos** para ese rol.  
    - Allí verás listas de permisos agrupados por módulos del sistema.  
    - Puedes:
      - Activar o desactivar **módulos completos**.  
      - Activar o desactivar **permisos individuales**.

- **Eliminar (`fa-trash`)**  
  - ¿Qué hace?  
    - Abre un modal preguntando si quieres **borrar ese rol**.  
    - El sistema puede impedir borrar ciertos roles, por ejemplo:
      - Roles del sistema (admin, superadmin, etc.).  
      - Roles en uso según las reglas de negocio.

Dentro del modal de eliminar:

- Si confirmas, el sistema intenta borrar el rol (si las reglas lo permiten).  
- Si cancelas, no se hace ningún cambio.

---

## 7. Modo selección y acciones masivas

Encima de la tabla (o en la cabecera del listado) suele haber un botón:

- **“Seleccionar” / “Cancelar selección”**  
  - ¿Qué hace?  
    - Activa o desactiva el **modo selección múltiple**.  
    - Cuando está activo, aparecen **checkboxes** al lado de cada rol.

Cuando el modo selección está activado, verás acciones adicionales, por ejemplo:

- **“Seleccionar página” / “Limpiar página”**  
  - Marca o desmarca todos los roles que se ven en la **página actual**.

- **“Eliminar seleccionados”**  
  - Abre un modal para borrar **varios roles a la vez**.  
  - El sistema:
    - Borra los roles que se pueden borrar.  
    - Deja y explica los que no se pueden borrar (por ejemplo, roles del sistema o roles con restricciones).

Este modo es útil cuando quieres **limpiar muchos roles** de una sola vez, en lugar de ir fila por fila.

---

## 8. Paginación y selector de registros por página

Debajo de la tabla verás:

- **Texto de resumen**, por ejemplo:  
  “Mostrando 1 a 10 de 37 resultados”.

- **Selector de registros por página**  
  - Etiqueta: “Registros por página:”.  
  - Opciones del select: `10`, `25`, `50`, `100`.  
  - ¿Qué hace?
    - Cambia cuántos roles se muestran en cada página.  
    - Siempre vuelve a la **página 1** al cambiar el número, para que no te pierdas.

- **Botones de paginación**:
  - En móvil:
    - Botón **“Anterior”**.  
    - Botón **“Siguiente”**.
  - En escritorio:
    - Flechas izquierda/derecha.  
    - Números de página (1, 2, 3, …).

Sirven para moverte entre páginas de resultados sin perder de vista cuántos registros hay en total.

---

## 9. Flujos típicos de uso del módulo de Roles

### 9.1. Ver rápidamente cómo está la seguridad

1. Entras a **Roles**.  
2. Miras las **tarjetas de resumen**:
   - Cuántos roles.  
   - Cuántos usuarios.  
   - Cuántos permisos.
3. Bajas a la **tabla** para ver la lista detallada y revisar acciones.

### 9.2. Crear un nuevo rol (ejemplo: “Cajero”)

1. Haces clic en **“Nuevo rol”**.  
2. Llenas los campos (nombre del rol, descripción si la hay).  
3. Guardas.  
4. De vuelta en la lista, usas el botón de **permisos** para ese rol:
   - Abres el modal de permisos.  
   - Marcas qué cosas puede ver/hacer el “Cajero”.  
   - Guardas los cambios.
5. Más tarde, en el módulo de **Usuarios**, podrás asignar ese rol a las personas que trabajan como cajeros.

### 9.3. Buscar un rol específico

1. En el panel de **Filtros**, en el campo “Buscar”, escribes algo como “Admin”.  
2. Si quieres ver solo roles de sistema, en “Tipo de rol” eliges `System`.  
3. La tabla se actualiza mostrando solo los roles que coinciden con tu búsqueda.

### 9.4. Ajustar permisos de un rol existente

1. En la fila del rol, haces clic en el botón de **permisos**.  
2. En el modal:
   - Puedes activar módulos completos (por ejemplo “Ventas”).  
   - O activar permisos específicos (por ejemplo “crear venta”, “anular venta”).  
3. Guardas los cambios.  
4. El sistema muestra un mensaje de éxito indicando que los permisos se actualizaron.

### 9.5. Eliminar roles que ya no se usan

1. Activas el **modo selección**.  
2. Marcas uno o varios roles con los **checkboxes** de la primera columna.  
3. Haces clic en **“Eliminar seleccionados”**.  
4. Confirmas en el modal.  
5. El sistema:
   - Elimina los roles permitidos.  
   - Indica en un mensaje si algunos roles no se pudieron eliminar y por qué.

---

## 10. Cómo explicarlo a un cliente

Cuando le expliques el sistema a un cliente, puedes usar esta idea sencilla:

> “En este módulo definimos las **llaves de la casa**.  
> Cada rol es una llave que dice a qué partes del sistema puede entrar una persona y qué puede hacer allí.  
> Arriba ves un resumen rápido (cuántos roles, usuarios y permisos).  
> En el centro tienes filtros para encontrar roles rápido.  
> Abajo tienes la tabla donde puedes ver cada rol, ver su detalle, cambiar sus permisos, editarlo o eliminarlo; incluso puedes hacer cambios masivos cuando la empresa crece.”

