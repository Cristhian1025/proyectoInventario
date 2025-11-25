<?php
include("db.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['save_proveedor'])){
    echo "Guardando DATOS";

    $nombreProveedor = $_POST["nombreProveedor"];
    $descripcionProveedor = $_POST["descripcionProveedor"];
    $direccionProveedor = $_POST["direccionProveedor"];
    $telefono = $_POST["telefono"];
    $Correo = $_POST["Correo"];
    $infoAdicional = $_POST["infoAdicional"];

    $sql = "INSERT INTO Proveedores (nombreProveedor, descripcionProveedor, direccionProveedor, telefono, Correo, infoAdicional)
            VALUES (?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $nombreProveedor, $descripcionProveedor, $direccionProveedor, $telefono, $Correo, $infoAdicional);


    if ($stmt->execute()) {
            header("location: dashboard.php");
        } else {
            echo "Error:<hr> " . $sql . "<hr>" . $conn->error;
        }
        $stmt->close();
        $conn->close();

}
else{
echo "Error, datos no recibidos aaaaaaaaaaaaaa";
}
?>
