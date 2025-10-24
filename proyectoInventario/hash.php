<?php
/**
 * migrar_contrasenias.php
 *
 * Este script actualiza las contraseñas de los usuarios en la base de datos,
 * aplicando el algoritmo de hash bcrypt para mejorar la seguridad.
 * 
 * - Busca usuarios cuyas contraseñas aún no estén hasheadas o usen un algoritmo obsoleto.
 * - Si encuentra contraseñas con prefijo 'sha256:', las migra a bcrypt.
 * - Registra en la base de datos el algoritmo utilizado en cada actualización.
 */

// Incluye el archivo con la conexión a la base de datos.
require_once('db.php');

// Consulta que selecciona los usuarios cuyas contraseñas necesitan ser actualizadas.
// Incluye casos en los que no hay algoritmo definido o usan formatos antiguos.
$query = "SELECT idUsuario, contrasenia FROM Usuario 
          WHERE algoritmo_hash IS NULL 
          OR algoritmo_hash = 'bcrypt' 
          OR contrasenia LIKE 'sha256:%'";

$result = mysqli_query($conn, $query);

if ($result) {
    // Recorre cada usuario encontrado en la consulta.
    while ($row = mysqli_fetch_assoc($result)) {
        $idUsuario = $row['idUsuario'];
        $contraseniaPlano = $row['contrasenia'];
        $algoritmoHash = 'bcrypt'; // Se define bcrypt como el algoritmo por defecto.

        // Verifica si la contraseña ya estaba hasheada con SHA256.
        // Si es así, elimina el prefijo 'sha256:' y actualiza el tipo de algoritmo.
        if (strpos($contraseniaPlano, 'sha256:') === 0) {
            $contraseniaPlano = str_replace('sha256:', '', $contraseniaPlano);
            $algoritmoHash = 'SHA256'; // Conserva el registro del algoritmo original.
        }

        // Genera un nuevo hash usando bcrypt para la contraseña.
        $contraseniaHash = password_hash($contraseniaPlano, PASSWORD_BCRYPT);

        // Prepara la consulta para actualizar la contraseña y el algoritmo en la base de datos.
        $update_query = "UPDATE Usuario SET contrasenia = ?, algoritmo_hash = ? WHERE idUsuario = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ssi", $contraseniaHash, $algoritmoHash, $idUsuario);

        // Ejecuta la actualización y verifica si fue exitosa.
        $stmt->execute();

        if ($stmt->errno == 0) {
            echo "Contraseña del usuario $idUsuario actualizada correctamente a bcrypt.<br>";
        } else {
            echo "Error al actualizar la contraseña del usuario $idUsuario: " . $stmt->error . "<br>";
        }

        // Cierra la sentencia preparada.
        $stmt->close();
    }
} else {
    // En caso de error al realizar la consulta inicial.
    echo "Error al obtener los usuarios: " . mysqli_error($conn);
}

// Cierra la conexión a la base de datos.
mysqli_close($conn);
?>

