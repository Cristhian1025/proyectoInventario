<?php
session_start();  // Asegúrate de que esta línea esté al principio de tu script

session_unset();  // Elimina todas las variables de sesión
session_destroy(); // Destruye la sesión
// Redirige al login
header("Location: index.php");
exit();
?>