<?php

$conn = mysqli_connect(
    'localhost',
    'root',
    '1025',
    'inventario'
);

if (isset($conn)){
    echo "coneccioon";
}


?>