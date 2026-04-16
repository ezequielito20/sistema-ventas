### Manual del Módulo de Categorías

*(explicado como para un niño de 7 años curioso)*

---

## 1. ¿Qué es una “categoría”?

Imagina que tu tienda es un **súper grande**:

- Tienes una estantería para **bebidas**.  
- Otra para **snacks**.  
- Otra para **limpieza**.  

Cada una de esas estanterías es una **categoría**:  
sirve para **agrupar productos parecidos** y encontrar todo más fácil.

En el módulo de **Categorías**:

- Creas, editas y borras estas “estanterías virtuales”.  
- Ves cuántos productos tiene cada una.  
- Ves si se está usando o no.

---

## 2. Qué ves al entrar al módulo de Categorías

La pantalla se divide en:

1. **Cabecera** (título, subtítulo y botones grandes).  
2. **Tarjetas de resumen** (estadísticas rápidas).  
3. **Bloque de filtros** (para buscar y segmentar categorías).  
4. **Tabla de categorías** (lista principal).  
5. **Paginación con selector de registros por página**.  
6. **Modales** (detalle y eliminación).

---

## 3. Cabecera de Categorías

Arriba de todo:

- **Título:** `Categorías`  
  - Te recuerda que estás organizando productos en grupos.
- **Subtítulo:** `Organiza productos por categorías dentro de tu empresa.`  
  - Explica el propósito del módulo.

A la derecha tienes dos botones importantes:

- **“Ver PDF”**  
  - Ícono: `fa-file-pdf`.  
  - ¿Qué hace?
    - Abre un **reporte PDF** con la lista de categorías.  
    - Se abre en otra pestaña del navegador.

- **“Nueva categoría”**  
  - Ícono: `fa-plus`.  
  - ¿Qué hace?
    - Te lleva al formulario para **crear una categoría nueva**.  
    - Ejemplos de nombres:
      - “Bebidas”, “Snacks”, “Electrónica”, “Limpieza”.

Dependiendo de los permisos del usuario, alguno de estos botones puede no salir.

---

## 4. Tarjetas de resumen (widgets de Categorías)

Debajo de la cabecera verás **4 tarjetas**:

1. **Categorías (Total)**  
   - Muestra cuántas categorías tienes en la empresa.  
   - Texto tipo: “Categorías · Registradas”.

2. **Esta semana**  
   - Cuántas categorías se crearon en los últimos 7 días.  
   - Te dice si tu catálogo está creciendo.

3. **Con productos**  
   - Cuántas categorías tienen **al menos un producto** asignado.  
   - Indica cuántas categorías están realmente en uso.

4. **Sin productos**  
   - Cuántas categorías están **vacías** (0 productos).  
   - Te ayuda a detectar categorías que tal vez puedas limpiar o completar.

Estas tarjetas son solo para **ver información rápida**, no tienen botones ni acciones.

---

## 5. Bloque de filtros en Categorías

### 5.1. Mostrar / ocultar filtros

Como en otros módulos:

- Botón `Filtros avanzados` / `Ocultar filtros`.  
- Ícono alterna entre `fa-filter` y `fa-sliders-h`.  
- Sirve para **mostrar u ocultar** el formulario de filtros.

Cuando los filtros están ocultos:

- Tienes más espacio para ver la tabla.  
- Si necesitas filtrar, vuelves a abrirlos con el mismo botón.

### 5.2. Filtros disponibles

Dentro del panel de filtros verás:

- **Buscar** (`search`)  
  - Campo de búsqueda con ícono de lupa.  
  - ¿Qué hace?
    - Filtra categorías por:
      - **Nombre**.  
      - **Descripción** (si tu consulta lo incluye).  
    - Ejemplo: si escribes “bebidas”, verás solo categorías relacionadas con “bebidas”.

- **Productos** (`hasProducts`)  
  - Select con opciones:
    - `Todos`  
    - `Con productos` (`yes`)  
    - `Sin productos` (`no`)
  - ¿Qué hace?
    - Si eliges “Con productos”, ves solo categorías con al menos un producto.  
    - Si eliges “Sin productos”, ves solo las vacías.  
    - “Todos” muestra todas.

- **Desde / Hasta** (`dateFrom` / `dateTo`)  
  - Dos campos de fecha:
    - `Desde`: fecha mínima de creación.  
    - `Hasta`: fecha máxima de creación.
  - ¿Qué hacen?
    - Filtran categorías por fecha de creación.  
    - Ejemplos:
      - Ver solo categorías creadas este mes.  
      - Ver categorías creadas entre dos fechas concretas.

- **Mín. prod.** (`productsMin`)  
  - Campo numérico.  
  - ¿Qué hace?
    - Muestra solo categorías con **al menos** ese número de productos.  
    - Ejemplo: pones `5` y ves categorías con 5 o más productos.

- **Máx. prod.** (`productsMax`)  
  - Campo numérico.  
  - ¿Qué hace?
    - Muestra solo categorías con **como máximo** ese número de productos.  
    - Ejemplo: pones `2` y ves categorías pequeñas con 0, 1 o 2 productos.

- **Botón “Limpiar filtros”**  
  - ¿Qué hace?
    - Borra texto de “Buscar”.  
    - Devuelve “Productos” a `Todos`.  
    - Vacía `Desde`, `Hasta`, `Mín. prod.` y `Máx. prod.`.  
    - Vuelve a mostrar **todas** las categorías, empezando en la **página 1**.

Cada vez que cambias un filtro:

- Se refresca la tabla.  
- La paginación vuelve a la primera página.

---

## 6. Tabla de categorías

La tabla muestra cada categoría en una fila.

### 6.1. Columnas principales

Las más importantes son:

- **Selección** (checkbox, cuando el modo selección está activo)  
  - Permite marcar varias categorías a la vez.

- **Nombre de la categoría**  
  - Nombre amigable, por ejemplo:
    - “Bebidas frías”, “Snacks dulces”.
  - Acompañado de un ícono (ej. tag) dentro de un círculo.

- **Descripción**  
  - Texto corto que describe la categoría.  
  - Si no hay descripción, normalmente se muestra un guion o texto tipo “Sin descripción”.

- **Productos** (`products_count`)  
  - Suele ser una **chapita (badge)** de color:
    - Verde si tiene productos.  
    - Amarilla si tiene 0.
  - Muestra cuántos productos están asociados a esa categoría.

- **Creado**  
  - Fecha y hora de creación (`d/m/Y H:i`).  
  - Útil para saber si es una categoría nueva o antigua.

- **Acciones**  
  - Columna con íconos:
    - Ver detalle.  
    - Editar.  
    - Eliminar.

### 6.2. Botones de acción de cada categoría

En la columna de acciones verás:

- **Ver detalle (`fa-eye`)**  
  - ¿Qué hace?
    - Abre un **modal** con información más completa:
      - Nombre.  
      - Descripción.  
      - Número de productos.  
      - Fechas (creado/actualizado).
  - No cambia nada, solo muestra datos.

- **Editar (`fa-edit`)**  
  - ¿Qué hace?
    - Te lleva al formulario para cambiar:
      - El nombre de la categoría.  
      - La descripción.  
    - Tras guardar, vuelves al listado y ves los cambios aplicados.

- **Eliminar (`fa-trash`)**  
  - ¿Qué hace?
    - Abre un modal pidiendo confirmación para borrar la categoría.  
    - El backend suele impedir eliminar categorías con productos:
      - Si no se puede borrar, muestra el motivo (por ejemplo “Tiene productos asociados”).

Dentro del modal de eliminación:

- Si confirmas, se intenta borrar la categoría.  
- Si no se puede, el sistema te explica el porqué.  
- Si cancelas, no se hace nada.

---

## 7. Modo selección y borrado masivo de categorías

Igual que en Roles y Usuarios, hay un **modo selección**:

- Botón `Seleccionar` / `Cancelar selección` sobre el listado.  
- Al activarlo:
  - Aparecen checkboxes en la tabla.  
  - Se habilitan acciones masivas.

Cuando está activo:

- **Seleccionar página / Limpiar página**  
  - Botón que marca o desmarca **todas** las categorías visibles en la página actual.

- **Eliminar seleccionadas**  
  - Abre un modal donde se intenta borrar varias categorías a la vez.  
  - El servicio de negocio decide:
    - Cuáles se pueden borrar.  
    - Cuáles no (por ejemplo, si tienen productos).
  - El mensaje de resultado suele decir:
    - Cuántas se borraron.  
    - Cuáles no y por qué.

Esto es muy útil para limpiar muchas categorías vacías o no usadas de una sola vez.

---

## 8. Paginación y registros por página

Debajo de la tabla verás:

- Un texto como:  
  - “Mostrando 1 a 10 de 37 resultados”.
- El selector **“Registros por página”**:
  - Opciones: `10`, `25`, `50`, `100`.  
  - ¿Qué hace?
    - Ajusta cuántas categorías ves por página.  
    - Siempre te manda de nuevo a la **página 1** al cambiar el valor.
- Los botones de paginación:
  - En móvil:
    - “Anterior” y “Siguiente”.  
  - En escritorio:
    - Flechas izquierda/derecha.  
    - Números de página (1, 2, 3, …).

---

## 9. Flujos típicos en el módulo de Categorías

### 9.1. Crear una nueva categoría

1. Entras a **Categorías**.  
2. Haces clic en **“Nueva categoría”**.  
3. Escribes:
   - Nombre (ej. “Bebidas frías”).  
   - Descripción (opcional pero recomendable).  
4. Guardas.  
5. La categoría aparece en la tabla y ahora podrás asignarle productos desde el módulo de productos.

### 9.2. Buscar categorías “vacías” o con muchos productos

1. En el panel de filtros:
   - En “Productos” eliges `Sin productos` si quieres ver solo las vacías.  
   - O usas “Mín. prod.” y “Máx. prod.” para acotar por cantidad.
2. La tabla muestra solo las categorías que cumplen esas condiciones.  
3. Puedes decidir:
   - Editarlas para usarlas.  
   - O eliminarlas si no tienen sentido.

### 9.3. Revisar una categoría importante

1. Buscas la categoría por nombre en el filtro “Buscar”.  
2. En la fila correspondiente, haces clic en **Ver detalle**.  
3. En el modal ves:
   - Nombre y descripción.  
   - Cuántos productos tiene.  
   - Fechas de creación / actualización.  
4. Si necesitas corregir algo:
   - Cierras el modal.  
   - Usas el botón **Editar** para ajustar la información.

### 9.4. Limpiar categorías antiguas

1. Activas el **modo selección**.  
2. Usas filtros (por fechas y productos = 0) para encontrar categorías viejas o no usadas.  
3. Marcas varias con los checkboxes.  
4. Haces clic en **“Eliminar seleccionadas”**.  
5. Confirmas.  
6. El sistema:
   - Borra las categorías sin productos o válidas según tus reglas.  
   - Te informa de las que no pudo borrar.

---

## 10. Cómo explicarle el módulo de Categorías a un cliente

Puedes usar una explicación sencilla como esta:

> “Aquí organizas tu catálogo en **estanterías** llamadas categorías.  
> Cada categoría agrupa productos parecidos, por ejemplo ‘Bebidas’, ‘Snacks’ o ‘Electrónica’.  
> Arriba ves cuántas categorías tienes, cuántas se usan y cuántas están vacías.  
> En el centro puedes filtrar por nombre, fechas o número de productos.  
> Abajo tienes una tabla donde puedes ver cada categoría, editarla, ver cuántos productos tiene o eliminar las que ya no necesitas, incluso varias a la vez.”

