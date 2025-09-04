<?php
session_start();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_a_eliminar = $_GET['id'];
    $ruta_txt = '../txt/juegos.txt';

    if (file_exists($ruta_txt) && is_readable($ruta_txt)) {
        $lineas = file($ruta_txt, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lineas_a_mantener = [];
        $producto_encontrado = false;

        foreach ($lineas as $linea) {
            $datos = explode(';', $linea);
            // El ID ahora está en el índice 0
            $producto_id = isset($datos[0]) ? $datos[0] : '';

            if ($producto_id === $id_a_eliminar) {
                $producto_encontrado = true;
                // Opcional: Eliminar las fotos físicas asociadas si ya no se usarán
                if (isset($datos[5]) && !empty($datos[5]) && file_exists('../portadas/' . $datos[5])) {
                    unlink('../portadas/' . $datos[5]);
                }
                if (isset($datos[6]) && !empty($datos[6]) && file_exists('../portadas/' . $datos[6])) {
                    unlink('../portadas/' . $datos[6]);
                }
                if (isset($datos[7]) && !empty($datos[7]) && file_exists('../portadas/' . $datos[7])) {
                    unlink('../portadas/' . $datos[7]);
                }
            } else {
                // Si la línea no es la que queremos eliminar, la añadimos al array para mantenerla
                $lineas_a_mantener[] = $linea;
            }
        }

        if ($producto_encontrado) {
            // Reescribir todo el archivo con las líneas que queremos mantener
            $archi = fopen($ruta_txt, 'w');
            if ($archi) {
                foreach ($lineas_a_mantener as $linea) {
                    fputs($archi, $linea . PHP_EOL);
                }
                fclose($archi);
                echo '<p>Producto eliminado con éxito.</p>';
                header('refresh:2;url=list_products.php'); // Redirigir al listado
                exit();
            } else {
                echo '<p>Error: No se pudo abrir el archivo para escritura.</p>';
            }
        } else {
            echo '<p>Error: No se encontró el producto con el ID especificado.</p>';
        }
    } else {
        echo '<p>Error: El archivo de productos no existe o no se puede leer.</p>';
    }
} else {
    echo '<p>Error: No se ha especificado un ID de producto para eliminar.</p>';
}

// Redirigir en caso de errores o falta de ID
header('refresh:3;url=list_products.php');
exit();
?>