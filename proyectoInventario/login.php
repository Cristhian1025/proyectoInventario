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
        $_SESSION['nombreUsuario']=$nombreUsuario;
        header("Location: dashboard.php");
        exit();
    } else {// Credenciales incorrectas = mensaje de error
        $_SESSION['message'] = 'usuario o contraseña incorrectos';
        $_SESSION['message_type'] = 'danger';
        header("Location: index.php");
    }
    $stmt->close();
    $conn->close();   
?>