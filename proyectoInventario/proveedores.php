<?php
/**
 * proveedores.php
 *
 * Descripción: Formulario para ingresar un nuevo proveedor.
 */

// Incluye el archivo de configuración de la base de datos.  Se usa "require" para asegurar
// que la conexión a la base de datos esté disponible antes de que el script continúe.
require_once("db.php");

// Incluye el encabezado de la página, que contiene elementos como el menú de navegación.
require_once("includes/header.php");
?>

<div class="container mt-5">
    <div class="card card-glass">
        <div class="card-header">
            <h2 class="mb-0">Crear Nuevo Proveedor</h2>
        </div>
        <div class="card-body">
            <form action="save_proveedor.php" method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombreProveedor">Nombre del Proveedor:</label>
                            <input type="text" class="form-control" id="nombreProveedor" name="nombreProveedor"
                                   placeholder="Ingrese el nombre del proveedor" value="" required maxlength="45">
                            <div class="invalid-feedback">Por favor, ingrese el nombre del proveedor.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="descripcionProveedor">Descripción del Proveedor:</label>
                            <textarea class="form-control" id="descripcionProveedor" name="descripcionProveedor"
                                      placeholder="Ingrese una descripción del proveedor" required maxlength="150"></textarea>
                            <div class="invalid-feedback">Por favor, ingrese una descripción del proveedor.</div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="direccionProveedor">Dirección del Proveedor:</label>
                            <input type="text" class="form-control" id="direccionProveedor" name="direccionProveedor"
                                   placeholder="Ingrese la dirección del proveedor" value="" required maxlength="45">
                            <div class="invalid-feedback">Por favor, ingrese la dirección del proveedor.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telefono">Teléfono:</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono"
                                   placeholder="Ingrese el teléfono del proveedor" value="" required maxlength="45">
                            <div class="invalid-feedback">Por favor, ingrese el teléfono del proveedor.</div>
                            <small class="form-text text-muted">Formato: 123-456-7890</small>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="correo">Correo Electrónico:</label>
                            <input type="email" class="form-control" id="correo" name="Correo"
                                   placeholder="Ingrese el correo electrónico del proveedor" value="" required maxlength="45">
                            <div class="invalid-feedback">Por favor, ingrese un correo electrónico válido.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="infoAdicional">Información Adicional:</label>
                            <textarea class="form-control" id="infoAdicional" name="infoAdicional"
                                      placeholder="Ingrese información adicional" maxlength="45"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <input type="submit" class="btn btn-success" name="save_proveedor" value="Guardar Proveedor">
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Incluye el pie de página de la página.
require_once("includes/footer.php");
?>

<script>
    // Función para inicializar y configurar la validación de Bootstrap.
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            // Obtiene todos los formularios a los que queremos aplicar estilos de validación de Bootstrap.
            var forms = document.getElementsByClassName('needs-validation');
            // Itera sobre ellos y evita el envío.
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
