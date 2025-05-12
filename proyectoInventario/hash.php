<?php
// Incluye el archivo de conexión a la base de datos.
require_once('db.php');

// Consulta para obtener usuarios con contraseñas no hasheadas o sin algoritmo hash.
$query = "SELECT idUsuario, contrasenia FROM Usuario WHERE algoritmo_hash IS NULL OR algoritmo_hash = 'bcrypt' OR contrasenia LIKE 'sha256:%'";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $idUsuario = $row['idUsuario'];
        $contraseniaPlano = $row['contrasenia'];
        $algoritmoHash = 'bcrypt'; // Establecemos el algoritmo por defecto

        // Si la contraseña comienza con 'sha256:', asumimos que ya está hasheada con SHA256 y la migramos a bcrypt
        if (strpos($contraseniaPlano, 'sha256:') === 0) {
            $contraseniaPlano = str_replace('sha256:', '', $contraseniaPlano);
            $algoritmoHash = 'SHA256'; // Mantenemos el registro del algoritmo original
        }

        // Hashea la contraseña usando bcrypt.
        $contraseniaHash = password_hash($contraseniaPlano, PASSWORD_BCRYPT);


        // Actualiza la base de datos con la contraseña hasheada y el algoritmo.
        $update_query = "UPDATE Usuario SET contrasenia = ?, algoritmo_hash = ? WHERE idUsuario = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ssi", $contraseniaHash, $algoritmoHash, $idUsuario);
        $stmt->execute();

        if ($stmt->errno == 0) {
            echo "Contraseña del usuario $idUsuario actualizada correctamente a bcrypt.<br>";
        } else {
            echo "Error al actualizar la contraseña del usuario $idUsuario: " . $stmt->error . "<br>";
        }
        $stmt->close();
    }
} else {
    echo "Error al obtener los usuarios: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
