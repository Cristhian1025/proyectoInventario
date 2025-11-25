<?php
include("db.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$registroActualizado = false;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM Proveedores WHERE idProveedor = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "Proveedor no encontrado.";
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreProveedor = $_POST['nombreProveedor'];
    $descripcionProveedor = $_POST['descripcionProveedor'];
    $direccionProveedor = $_POST['direccionProveedor'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['Correo'];
    $infoAdicional = $_POST['infoAdicional'];

    $sql = "UPDATE Proveedores SET 
            nombreProveedor='$nombreProveedor', 
            descripcionProveedor='$descripcionProveedor', 
            direccionProveedor='$direccionProveedor', 
            telefono='$telefono', 
            Correo='$correo', 
            infoAdicional='$infoAdicional' 
            WHERE idProveedor = $id";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = 'Entrada actualizada correctamente';
        $_SESSION['message_type'] = 'success';
        
        header("Location: dashboard.php");
        exit();
    } else {
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

