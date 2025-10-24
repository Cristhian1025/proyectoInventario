# Documentación del Proyecto de Inventario

## Descripción General

Este proyecto es un sistema de inventario y ventas simple desarrollado en PHP. Permite gestionar productos, proveedores, entradas de inventario y ventas.

## Estructura de Archivos

El proyecto se encuentra en la carpeta `proyectoInventario`. Los archivos más importantes son:

-   `dashboard.php`: Página principal del sistema.
-   `productos.php`: Gestión de productos.
-   `proveedores.php`: Gestión de proveedores.
-   `entradaproductos.php`: Gestión de entradas de inventario.
-   `ventas.php`: Formulario para registrar nuevas ventas.
-   `listado_ventas.php`: Listado de ventas realizadas.
-   `informes.php`: Generación de informes de ventas.
-   `includes/`: Contiene los archivos de cabecera y pie de página.
-   `queries/`: Contiene los archivos con las consultas a la base de datos.
-   `css/`: Contiene los archivos de estilos.
-   `imagenes/`: Contiene las imágenes del proyecto.
-   `fpdf/`: Librería para generar PDFs.

## Documentación de Scripts

A continuación se documentan los scripts más importantes del sistema.

### `db.php`

Este script se encarga de establecer la conexión con la base de datos.

-   **Variables:**
    -   `$conn`: Objeto de conexión a la base de datos.
-   **Lógica:**
    1.  Inicia la sesión de PHP.
    2.  Establece la conexión con la base de datos `inventario` utilizando `mysqli_connect`.
    3.  Verifica si la conexión fue exitosa. Si no, muestra un error y termina la ejecución.

### `ventas.php`

Este script muestra un formulario para registrar una nueva venta.

-   **Lógica:**
    1.  Incluye los archivos `db.php` y `includes/header.php`.
    2.  Obtiene la lista de productos y usuarios (vendedores) de la base de datos.
    3.  Muestra un formulario con los siguientes campos:
        -   Fecha de Venta
        -   Vendedor
        -   Cédula o Nit (opcional)
        -   Nombre del Cliente (opcional)
        -   Productos (se pueden agregar varios productos dinámicamente)
        -   Cantidad
        -   Precio Unitario (se calcula automáticamente)
    4.  Calcula el total de la venta dinámicamente con JavaScript.
    5.  Al enviar el formulario, los datos se envían a `save_venta.php`.

### `save_venta.php`

Este script se encarga de guardar una nueva venta en la base de datos.

-   **Lógica:**
    1.  Recibe los datos del formulario de `ventas.php` por `POST`.
    2.  Valida que el campo `cedulaNit` contenga solo números y tenga una longitud entre 5 y 20 dígitos.
    3.  Inicia una transacción de base de datos.
    4.  Inserta la venta en la tabla `ventas`.
    5.  Recorre los productos vendidos y los inserta en la tabla `detalle_venta`.
    6.  Actualiza el stock de los productos vendidos en la tabla `productos`.
    7.  Si todo es correcto, confirma la transacción (`commit`).
    8.  Si hay algún error, revierte la transacción (`rollback`).
    9.  Redirige al usuario a `listado_ventas.php` con un mensaje de éxito o error.

### `listado_ventas.php`

Este script muestra un listado de las ventas realizadas.

-   **Lógica:**
    1.  Incluye los archivos `db.php`, `queries/venta_querie.php` y `includes/header.php`.
    2.  Permite buscar ventas por `cedulaNit` o `nombreCliente`.
    3.  Muestra una tabla con las ventas, incluyendo los siguientes campos:
        -   ID Venta
        -   Fecha
        -   Vendedor
        -   Cliente
        -   Cédula/NIT
        -   Total
    4.  Ofrece acciones para cada venta:
        -   Generar Factura (PDF)
        -   Eliminar Venta
    5.  Implementa paginación para el listado de ventas.

### `generar_factura.php`

Este script genera una factura en formato PDF para una venta específica.

-   **Lógica:**
    1.  Recibe el `id_venta` por `GET`.
    2.  Utiliza la librería `FPDF` para generar el PDF.
    3.  Obtiene los datos de la venta y sus detalles de la base de datos utilizando la función `getVentaDetailsById` de `queries/venta_querie.php`.
    4.  Muestra los detalles de la factura, incluyendo los datos del cliente si existen.
    5.  Muestra una tabla con los productos vendidos, cantidades y precios.
    6.  Muestra el total de la venta.
    7.  El PDF se descarga automáticamente en el navegador.

### `queries/venta_querie.php`

Este script contiene las funciones para obtener los datos de las ventas de la base de datos.

-   **Funciones:**
    -   `getAllVentas($conn, $page, $recordsPerPage, $searchCedulaNit, $searchNombreCliente)`: Obtiene una lista paginada de ventas, con la opción de filtrar por `cedulaNit` y `nombreCliente`.
    -   `getVentaDetailsById($conn, $idVenta)`: Obtiene los detalles de una venta específica, incluyendo los datos del cliente y los productos vendidos.
