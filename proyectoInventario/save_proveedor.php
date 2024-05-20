<?php
if (isset($_POST['save_proveedor'])){
    echo "Guardando DATOS";
    
    include("db.php");

    $idProveedor = $_POST["idProveedor"];
    $nombreProveedor = $_POST["nombreProveedor"];
    $descripcionProveedor = $_POST["descripcionProveedor"];
    $direccionProveedor = $_POST["direccionProveedor"];
    $telefono = $_POST["telefono"];
    $Correo = $_POST["Correo"];
    $infoAdicional = $_POST["infoAdicional"];

    $sql = "INSERT INTO Proveedores (idProveedor, nombreProveedor, descripcionProveedor, direccionProveedor, telefono, Correo, infoAdicional)
            VALUES ('$idProveedor' ,'$nombreProveedor', '$descripcionProveedor', '$direccionProveedor', '$telefono', '$Correo', '$infoAdicional')";

    if ($conn->query($sql) === TRUE) {
        header("location: dashboard.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }


}
else{
echo "Error, datos no recibidos aaaaaaaaaaaaaa";
}
?>
