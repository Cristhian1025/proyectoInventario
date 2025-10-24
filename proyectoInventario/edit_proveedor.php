<?php
/**
 * edit_proveedor.php
 *
 * Página para editar la información de un proveedor.
 *
 * Cambios realizados:
 * - Se añadieron comentarios y PHPDoc en español.
 * - Se encapsularon operaciones de lectura/actualización en funciones usando
 *   sentencias preparadas para mejorar la seguridad frente a inyección SQL.
 *
 * NOTA: Este archivo sigue en estilo procedural para mantener compatibilidad
 * con el proyecto; las funciones añadidas son auxiliares y devuelven datos
 * simples para la plantilla HTML al final del archivo.
 */

include("db.php");

/**
 * Obtiene un proveedor por su ID.
 *
 * @param mysqli $conn Conexión mysqli activa.
 * @param int $id Identificador del proveedor.
 * @return array|false Devuelve un array asociativo con los datos del proveedor o false si no existe o hay error.
 */
function obtenerProveedorPorId($conn, int $id)
{
    $stmt = $conn->prepare("SELECT * FROM Proveedores WHERE idProveedor = ?");
    if (!$stmt) return false;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return false;
}

/**
 * Actualiza los datos de un proveedor.
 *
 * @param mysqli $conn Conexión mysqli activa.
 * @param int $id Identificador del proveedor a actualizar.
 * @param array $data Array asociativo con las claves: nombreProveedor, descripcionProveedor,
 *                    direccionProveedor, telefono, Correo, infoAdicional.
 * @return bool True si la actualización se realizó correctamente, false en caso contrario.
 */
function actualizarProveedor($conn, int $id, array $data): bool
{
    $stmt = $conn->prepare("UPDATE Proveedores SET nombreProveedor = ?, descripcionProveedor = ?, direccionProveedor = ?, telefono = ?, Correo = ?, infoAdicional = ? WHERE idProveedor = ?");
    if (!$stmt) return false;
    $stmt->bind_param('ssssssi', $data['nombreProveedor'], $data['descripcionProveedor'], $data['direccionProveedor'], $data['telefono'], $data['Correo'], $data['infoAdicional'], $id);
    return $stmt->execute();
}

$registroActualizado = false;

// --- Manejo de la petición GET (cargar datos del proveedor) ---
if (isset($_GET['id'])) {
    // Aseguramos que $id sea un entero
    $id = (int) $_GET['id'];
    $row = obtenerProveedorPorId($conn, $id);
    if ($row === false) {
        echo "Proveedor no encontrado.";
        exit();
    }
}

// --- Manejo de la petición POST (actualizar proveedor) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recogemos y saneamos las entradas del formulario
    $nombreProveedor = isset($_POST['nombreProveedor']) ? trim($_POST['nombreProveedor']) : '';
    $descripcionProveedor = isset($_POST['descripcionProveedor']) ? trim($_POST['descripcionProveedor']) : '';
    $direccionProveedor = isset($_POST['direccionProveedor']) ? trim($_POST['direccionProveedor']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $correo = isset($_POST['Correo']) ? trim($_POST['Correo']) : '';
    $infoAdicional = isset($_POST['infoAdicional']) ? trim($_POST['infoAdicional']) : '';

    $data = [
        'nombreProveedor' => $nombreProveedor,
        'descripcionProveedor' => $descripcionProveedor,
        'direccionProveedor' => $direccionProveedor,
        'telefono' => $telefono,
        'Correo' => $correo,
        'infoAdicional' => $infoAdicional
    ];

    // Intentamos actualizar usando la función segura
    if (actualizarProveedor($conn, $id, $data)) {
        // Mensaje de sesión (si existe session_start() en includes/header.php lo usará)
        $_SESSION['message'] = 'Entrada actualizada correctamente';
        $_SESSION['message_type'] = 'success';
        
        header("Location: dashboard.php");
        exit();
    } else {
        // Mostramos el error bruto de MySQL para depuración (se puede mejorar)
        echo "Error actualizando registro: " . $conn->error;
    }
    $conn->close();
}
?>

<?php include("includes/header.php") ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Proveedor</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Editar Proveedor</h2>
    <form action="edit_proveedor.php?id=<?php echo $id; ?>" method="POST">
        <div class="form-group">
            <label for="nombreProveedor">Nombre</label>
            <input type="text" class="form-control" id="nombreProveedor" name="nombreProveedor" value="<?php echo $row['nombreProveedor']; ?>" required>
        </div>
        <div class="form-group">
            <label for="descripcionProveedor">Descripción</label>
            <input type="text" class="form-control" id="descripcionProveedor" name="descripcionProveedor" value="<?php echo $row['descripcionProveedor']; ?>" required>
        </div>
        <div class="form-group">
            <label for="direccionProveedor">Dirección</label>
            <input type="text" class="form-control" id="direccionProveedor" name="direccionProveedor" value="<?php echo $row['direccionProveedor']; ?>" required>
            </div>
        <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo $row['telefono']; ?>" required>
        </div>
        <div class="form-group">
            <label for="Correo">Correo</label>
            <input type="email" class="form-control" id="Correo" name="Correo" value="<?php echo $row['Correo']; ?>" required>
        </div>
        <div class="form-group">
            <label for="infoAdicional">Información Adicional</label>
            <input type="text" class="form-control" id="infoAdicional" name="infoAdicional" value="<?php echo $row['infoAdicional']; ?>">
        </div>
        <button type="submit" class="btn btn-primary mx-4 my-4">Actualizar</button>
    </form>
</div>

