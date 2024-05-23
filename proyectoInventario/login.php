<?php
    include("db.php");
    

    $nombreUsuario = $_POST["usuario"];
    $contrasenia = $_POST["contrasenia"];
    

    $sql = "SELECT * FROM Usuario WHERE nombreUsuario = ? AND contrasenia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nombreUsuario, $contrasenia);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) { // credenciales correctasss  :D, redirigir a dashboard
        $stmt->close();
        $conn->close(); 
        header("Location: dashboard.php?usuario=$nombreUsuario");
        exit();
    } else {// Credenciales incorrectas = mensaje de error
        
        echo "<h2>ERROR DE CREDENCIALES</h2>";
    }

    $stmt->close();
    $conn->close();   

?>