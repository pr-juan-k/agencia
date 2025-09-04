<?php 
    // Las rutas a 'usuarios.txt' y 'cargar.php' ahora son relativas a 'logueo.php'
    // Como 'usuarios.txt' y 'cargar.php' están en la misma carpeta que 'logueo.php',
    // no necesitamos '..' (un nivel arriba).

    // --- 1. Manejo de Campos Vacíos ---
    if (empty($_POST["email"]) || empty($_POST["contraseña"])) {
        // Redirige al usuario.php (un nivel arriba) si los campos están vacíos
        // Usamos Refresh para que el navegador no dé error, aunque no mostraremos el mensaje aquí.
        header('Refresh:0;url=../usuario.php'); 
        exit(); // Detiene la ejecución del script aquí
    }

    // --- 2. Preparar Datos del Formulario ---
    $email_ingresado = strtolower(trim($_POST["email"])); // Limpiar y convertir a minúsculas
    $pass_ingresada = trim($_POST["contraseña"]); // Limpiar espacios

    $usuario_autenticado = false; // Bandera para saber si el usuario fue autenticado

    // --- 3. Leer y Validar Usuarios desde usuarios.txt ---
    $archivo_usuarios_path = 'usuarios.txt'; // Está en la misma carpeta que logueo.php

    if (file_exists($archivo_usuarios_path) && is_readable($archivo_usuarios_path)) {
        $handle = fopen($archivo_usuarios_path, 'r');
        if ($handle) {
            while (($linea = fgets($handle)) !== false) {
                $linea_limpia = trim($linea);
                $datos = explode(';', $linea_limpia);

                if (count($datos) >= 2) {
                    $email_leido = strtolower(trim($datos[0])); 
                    $pass_leida = trim($datos[1]); 
                    
                    // Comparación estricta de email y contraseña
                    if ($email_ingresado === $email_leido && $pass_ingresada === $pass_leida) {
                        $usuario_autenticado = true;
                        break; // Usuario encontrado, salimos del bucle
                    }
                }
            }
            fclose($handle);
        } else {
            // Error al abrir el archivo de usuarios. En un entorno real, manejar esto mejor (log, etc.)
            header('Refresh:0;url=../usuario.php?error=fileopen'); // Redirige de vuelta con un posible error
            exit();
        }
    } else {
        // Error si el archivo de usuarios no existe o no es legible
        header('Refresh:0;url=../usuario.php?error=filenotfound'); // Redirige de vuelta con un posible error
        exit();
    }

    // --- 4. Procesar el Resultado de la Autenticación ---

    if ($usuario_autenticado) {
        // --- 4a. Registro en log.txt (Opcional pero recomendado) ---
        date_default_timezone_set('America/Argentina/Tucuman'); // Configura tu zona horaria
        $fecHora = date('d-m-Y H:i:s');
        $archivo_log_path = 'log.txt'; // Está en la misma carpeta que logueo.php

        $archivo_log = fopen($archivo_log_path, 'a');
        if ($archivo_log) {
            fputs($archivo_log, $_POST['email'].' '.$fecHora.PHP_EOL);
            fclose($archivo_log);
        }
        // Si hay un error aquí, no lo mostramos para no interferir con la redirección.

        // --- 4b. Redirección Exitosa ---
        header('Location: cargar.php'); // Redirección directa a cargar.php (misma carpeta)
        exit(); // ¡FUNDAMENTAL! Detiene la ejecución para asegurar la redirección

    } else {
        // --- 4c. Redirección por Credenciales Incorrectas ---
        header('Location: ../usuario.php?error=authfail'); // Redirige de vuelta a usuario.php (un nivel arriba)
        exit(); // Detiene la ejecución del script
    }
?>