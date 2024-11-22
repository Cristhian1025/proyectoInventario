<!DOCTYPE html>
<html lang="es" >
<head>
  <meta charset="UTF-8">
  <title>Ingresar</title>
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  <link rel="stylesheet" href="css/style_2.css">
</head>
<body>
  
<div class="screen-1">
  <div class="caja1">
    <div class="name">
      <h1>INVENTARIO EN LA NUBE</h1>
    </div>
    <p>Acceda y gestione su inventario <br> de su negocio desde el navegador.</p>
  </div>
</div>  

<div class="screen-1">
  <form class="formulario" action="login.php" method="POST">
  <div class="logo">
    <img title="loginIMG"   class="logoindex" src="imagenes/inventario.jpg" width="300" height="auto">
  </div>
  <div class="email">
    <label for="email">Nombre de Usuario</label>
    <div class="sec-2">
      <input type="text" name="   usuario" placeholder="Username" required/>
    </div>
  </div>

  <div class="password">
    <label for="password">Contraseña</label>
    <div class="sec-2">      
      <ion-icon name="lock-closed-outline"></ion-icon>
      <input class="pas" type="password" name="contrasenia" required placeholder="············"/>
    </div>
  </div>
   <button type="submit" class="login" name="login" >Ingresar</button>
  
  <div class="footer"><span>Registrarse</span><span>Contraseña olvidada?</span></div>
</form>
</div>
</body>
</html>
