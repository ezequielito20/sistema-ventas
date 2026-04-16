### Manual del Módulo de Usuarios

*(explicado como para un niño de 7 años curioso)*

---

## 1. ¿Qué es un “usuario”?

En nuestra **casa grande** (la empresa):

- Un **usuario** es una **persona real** que tiene:
  - Un **nombre**.  
  - Un **correo** (para entrar al sistema).  
  - Uno o varios **roles** (sus llaves de acceso).

El módulo de **Usuarios** es donde ves y manejas **quién puede entrar al sistema** y **con qué permisos** entra.

Aquí podrás:

- Ver la lista de usuarios.  
- Crear nuevos usuarios.  
- Editar sus datos.  
- Ver si tienen el correo verificado o no.  
- Asignarles roles.  
- (Según tu configuración) Eliminar usuarios que ya no se usan.

---

## 2. Qué ves al entrar al módulo de Usuarios

La pantalla se organiza en:

1. **Cabecera** (título y botones grandes de acción).  
2. **Tarjetas de resumen** (widgets de métricas).  
3. **Bloque de filtros** (para buscar y segmentar usuarios).  
4. **Tabla de usuarios** (la lista principal).  
5. **Paginación y selector de registros por página**.  
6. **Modales** (para ver detalle o confirmar eliminación).

---

## 3. Cabecera de Usuarios

Arriba del todo verás:

- **Título:** `Usuarios`  
  - Te recuerda en qué parte del sistema estás.
- **Subtítulo:** `Gestión centralizada de cuentas, verificación y roles.`  
  - Resume la idea: aquí controlas las cuentas y sus permisos.

En la parte derecha aparecen, normalmente:

- **Botón “Ver PDF”**  
  - Ícono: `fa-file-pdf`.  
  - ¿Qué hace?
    - Abre un **reporte en PDF** con la lista actual de usuarios (según filtros y página).  
    - Se abre en una **nueva pestaña**, así no pierdes la vista actual.

- **Botón “Nuevo usuario”**  
  - Ícono: `fa-user-plus`.  
  - ¿Qué hace?
    - Te lleva a la pantalla para **crear una nueva cuenta de usuario**:
      - Nombre.  
      - Correo.  
      - Contraseña (según tu flujo).  
      - Rol(es) asignados.

Según los permisos del usuario conectado:

- Puede ver ambos botones, solo uno, o ninguno.

---

## 4. Tarjetas de resumen (widgets de Usuarios)

Debajo de la cabecera hay **tarjetas pequeñas** que te dan un resumen rápido:

1. **Usuarios (Total)**  
   - Muestra cuántos usuarios existen en tu empresa.  
   - Ejemplo de texto: “Usuarios · En tu empresa”.

2. **Verificados**  
   - Cuántos usuarios tienen el correo **confirmado**.  
   - Indica qué tan bien va el proceso de verificación.

3. **Sin verificar**  
   - Usuarios que **aún no han confirmado** su correo.  
   - Te sirve para detectar cuentas a las que deberías recordarles que validen su email.

4. **Con roles**  
   - Cuántos usuarios tienen al menos **un rol asignado**.  
   - Un usuario sin roles normalmente:
     - No puede hacer casi nada.  
     - O está pendiente de configuración.

Estas tarjetas son solo **informativas**: no tienen botones, pero te dejan ver el estado general de tus usuarios en segundos.

---

## 5. Bloque de filtros en Usuarios

### 5.1. Mostrar / ocultar filtros

Como en otros módulos:

- Hay un botón que dice `Filtros avanzados` o `Ocultar filtros`.  
- El ícono cambia entre `fa-filter` y `fa-sliders-h`.  
- Sirve para **mostrar u ocultar** los campos de filtrado.

Cuando los filtros están ocultos:

- Ves más espacio para la tabla.  
- Si los necesitas, vuelves a abrirlos con el mismo botón.

### 5.2. Filtros disponibles

Dentro del panel de filtros verás varios campos, como:

- **Buscar** (`search`)  
  - Campo de texto con un ícono de lupa.  
  - ¿Qué hace?
    - Filtra usuarios por:
      - **Nombre** (ej. “Juan”).  
      - **Correo** (ej. “@gmail.com”).  
    - Ejemplo: si escribes “juan@”, aparecen usuarios cuyo correo contiene “juan@…”.

- **Estado de verificación** (`verificationStatus`)  
  - Select con opciones típicas:
    - `Todos`  
    - `Verificados` (`verified`)  
    - `Sin verificar` (`unverified`)
  - ¿Qué hace?
    - Muestra solo los usuarios con ese estado de verificación de correo.

- **Rol** (`roleId`)  
  - Select con la lista de roles disponibles: Admin, Vendedor, Cajero, etc.  
  - ¿Qué hace?
    - Muestra solo usuarios que tienen **ese rol** (aunque puedan tener otros también).

- **Fechas “Desde” y “Hasta”** (`dateFrom` / `dateTo`)  
  - Dos campos de tipo fecha:
    - `Desde`: fecha mínima de creación.  
    - `Hasta`: fecha máxima de creación.
  - ¿Qué hacen?
    - Filtran usuarios por la **fecha en que fueron creados**.  
    - Ejemplos de uso:
      - Ver solo usuarios creados este mes.  
      - Ver usuarios creados entre dos fechas concretas.

- **Botón “Limpiar filtros”**  
  - ¿Qué hace?
    - Borra el texto de búsqueda.  
    - Vuelve el estado de verificación a “Todos”.  
    - Quita el rol seleccionado.  
    - Borra las fechas “Desde” y “Hasta”.  
    - Vuelve a mostrar **todos** los usuarios, empezando en la **página 1**.

Cada cambio en un filtro:

- **Refresca** automáticamente la tabla.  
- **Resetea** la paginación a la primera página para que el resultado tenga sentido.

---

## 6. Tabla de usuarios

La tabla es donde ves **fila por fila** la información de cada usuario.

### 6.1. Columnas principales

Las columnas más habituales son:

- **Selección** (checkbox, solo cuando el modo selección está activo)  
  - Sirve para marcar varios usuarios al mismo tiempo.

- **Nombre**  
  - Muestra el nombre completo, por ejemplo: “Ana García”.  
  - Muchas veces viene con un avatar o círculo con iniciales.

- **Correo electrónico**  
  - El email con el que se identifica el usuario.  
  - Es clave para:
    - Iniciar sesión.  
    - Recuperar contraseña.  
    - Recibir notificaciones.

- **Empresa** (si usas multiempresa)  
  - Indica a qué empresa está ligado ese usuario (según `company_id`).

- **Verificación**  
  - Puede aparecer como:
    - Un texto `Verificado` / `Sin verificar`.  
    - Un color verde/amarillo/rojo.  
  - A veces la **fecha de verificación** exacta se ve en el modal de detalle.

- **Roles**  
  - Muestra la lista de roles asignados al usuario.  
  - Ejemplo:
    - “Admin, Vendedor”  
    - “Cajero”
  - Así sabes rápidamente qué tipo de acceso tiene esa persona.

- **Fechas de creación / actualización**  
  - Te permiten ver si es una cuenta antigua o nueva.

- **Acciones**  
  - Columna con **botones de íconos** para:
    - Ver detalle.  
    - Editar.  
    - Eliminar.  
    - (Opcional en tu sistema) Otras acciones especiales como reenviar verificación, resetear contraseña, etc.

### 6.2. Botones de acción por fila

En la columna de acciones verás, típicamente:

- **Ver detalle (`fa-eye`)**  
  - ¿Qué hace?
    - Abre un **modal** con la información extendida del usuario:
      - Nombre.  
      - Email.  
      - Empresa.  
      - Estado de verificación y fecha (si aplica).  
      - Roles que tiene.  
    - No cambia nada, es solo para consulta.

- **Editar (`fa-edit`)**  
  - ¿Qué hace?
    - Te lleva a un formulario donde puedes cambiar:
      - Nombre.  
      - Correo.  
      - Roles asignados.  
      - Otros datos que hayas definido.
    - Al guardar, vuelve al listado con la información actualizada.

- **Eliminar (`fa-trash`)**  
  - ¿Qué hace?
    - Abre un modal de confirmación para **borrar ese usuario**.  
    - El backend suele aplicar reglas de negocio, por ejemplo:
      - No dejar que un usuario se borre a sí mismo.  
      - Bloquear la eliminación de ciertos tipos de cuentas críticas.

En el modal de confirmación:

- Si confirmas, el usuario se borra (o se marca como eliminado, según tu implementación).  
- Si cancelas, todo queda igual.

---

## 7. Modo selección y acciones masivas

Como en Roles y Permisos, existe un **modo selección**:

- Botón `Seleccionar` / `Cancelar selección` arriba del listado.  
- Al activarlo:
  - Aparece un checkbox en la cabecera.  
  - Cada fila de usuario tiene un checkbox propio.

Con el modo selección activo puedes:

- **Seleccionar página**  
  - Marca todos los usuarios visibles en esa página.
- **Limpiar página**  
  - Desmarca todos los de esa página.
- **Eliminar seleccionados** (si lo tienes habilitado)  
  - Abre un modal para eliminar varios usuarios de golpe.

El sistema:

- Elimina los usuarios que se pueden eliminar.  
- Informa en un mensaje si alguno no se pudo eliminar y por qué.

Este modo es ideal para limpiar usuarios de prueba o cuentas que ya no se necesitan.

---

## 8. Paginación y registros por página

Abajo de la tabla encontrarás:

- Un texto tipo:  
  - “Mostrando 1 a 10 de 124 resultados”.
- El selector **“Registros por página”**:
  - Opciones: `10`, `25`, `50`, `100`.  
  - ¿Qué hace?
    - Ajusta cuántos usuarios ves en cada página.  
    - Siempre vuelve automáticamente a la **página 1** al cambiar la opción.
- Los controles de paginación:
  - En móvil:
    - Botones **“Anterior”** y **“Siguiente”**.  
  - En escritorio:
    - Flechas izquierda/derecha.  
    - Números de página (1, 2, 3, …).

---

## 9. Flujos típicos del módulo de Usuarios

### 9.1. Crear un nuevo usuario

1. Entras a **Usuarios**.  
2. Haces clic en **“Nuevo usuario”**.  
3. Rellenas:
   - Nombre.  
   - Correo.  
   - Contraseña (según tu pantalla).  
   - Rol(es) que tendrá.  
4. Guardas.  
5. El usuario aparece en la tabla y queda listo para usar el sistema (siguiendo tu flujo de verificación).

### 9.2. Buscar un usuario específico

1. En el filtro **Buscar**, escribes:
   - Parte del nombre: “Carlos”.  
   - O parte del correo: “@empresa.com”.
2. Si quieres, filtras también por:
   - Estado “Verificados” o “Sin verificar”.  
   - Un rol concreto (por ejemplo “Vendedor”).
3. La tabla se actualiza mostrando solo los usuarios que cumplen con tus filtros.

### 9.3. Revisar el acceso de un usuario

1. Buscas al usuario con los filtros.  
2. Haces clic en **Ver detalle**.  
3. En el modal ves:
   - Roles que tiene.  
   - Estado de verificación.  
   - Información básica.  
4. Si hace falta cambiar algo:
   - Cierras el modal.  
   - Haces clic en **Editar** para ajustar roles, correo, etc.

### 9.4. Eliminar varios usuarios de prueba

1. Activas el **modo selección**.  
2. Con el filtro de “Buscar”, localizas usuarios de prueba (por ejemplo que empiecen por “test-”).  
3. Marcas los que quieras borrar con los checkboxes.  
4. Haces clic en **“Eliminar seleccionados”**.  
5. Confirmas en el modal de eliminación.  
6. El sistema:
   - Elimina los que se pueden eliminar.  
   - Te muestra si alguno no pudo ser eliminado (y por qué).

---

## 10. Cómo explicarlo a un cliente

Puedes usar una explicación así:

> “En esta pantalla ves a **todas las personas** que pueden entrar al sistema.  
> Cada usuario tiene un nombre, un correo y uno o varios roles que definen qué puede hacer.  
> Arriba ves un resumen (cuántos usuarios hay, cuántos están verificados, etc.).  
> En el centro tienes filtros para encontrarlos rápido por nombre, rol o estado.  
> Abajo tienes una tabla donde puedes ver el detalle de cada usuario, editar sus datos, ajustar sus roles o eliminarlo si ya no debe tener acceso.”

