<?php 
    $ruta = '../css';
    require("encabezado.php");
    require("listado.php");
    
    if (!empty($_POST["email"]) && !empty($_POST["contraseña"])){
        
        $usuario = fopen('../txt/usuarios.txt','r');
         $usua = fgets($usuario);
         $usuarioArr = explode(';',$usua);
         $us = trim($usuarioArr[1]);
         $usua = fgets($usuario);
         $contraioArr = explode(';',$usua);
         $contra = trim($contraioArr[1]);

         //echo $_POST["email"];
         //echo $_POST["contraseña"];
         //echo $us ;
         //echo $contra;

         if(($_POST["contraseña"] == $contra)) {
            echo '<main>';
            muestro($_POST['email'],$_POST['contraseña']);
            
            //Creamos fecha y hora
            date_default_timezone_set('America/Argentina/Tucuman');
            $fecHora = date('d-m-Y H:i:s');
            //creamos ruta
            $ruta = '../txt/';
            $docum = 'log.txt';
            $archivo = fopen($ruta.$docum,'a');
            $guardo = fputs($archivo,$_POST['email'].' '.$fecHora.PHP_EOL);
            echo 'se guarda';
            fclose($archivo);
            if ($guardo) {
                echo '<p>Iniciaste sesion</p>';
                header('refresh:1;url=../cargarP/cargar.php');
            }
            echo '</main>';

         }else{
            header('refresh:2;url=../index.php');
            echo "<main>";
            echo '<p>no se procesa</p>';
            echo "</main>";
            
         }
         

        
    }
    else{
        echo "<main>";
        echo "<p>UNO DE LOS CAMPOS ESTA VACIO</p>";
        header('refresh:3;url=../index.php');
        echo "</main>";
    }
    require("pie.php");

?>