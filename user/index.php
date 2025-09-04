<?php 
    $ruta = 'css';
    require("php/encabezado.php");
    

?>
<main>
    <form action="php/logueo.php" method="post">
        <section>
        <h2>Iniciar secion</h2>
        <p>Ingrese su Usuario y Contraseña</p>
        </section>
        <input type="text" name="email" id="em"  >
        <label for="em">Usuario</label>
        <input type="password" name="contraseña" id="cont">
        <label for="cont">Contraseña</label>
        <input id="bt" type="submit" value="Ingresar">
    </form>
</main>


<?php 
    require("php/pie.php");

?>