<?php
    $ruta = '../';
    require("encabezado.php");
    // require("conexion.php"); // Ya no es necesario si solo lees del archivo txt
    // require("logueo.php"); // Revisa si necesitas esto aquí
?>

<main class="container">
    <section>
        <article class="row">
            <section class="menu_tmp">
                <a class="btn btn-dark" href="../cargar.php">+ Cargar Nuevo Producto</a>
            </section>
            <section class="menu_tmp">
                <a class="btn btn-dark" href="../../../index.php">Ver web</a>
            </section>
            <table class="table table-bordered table-hover table-striped w-auto">
                <caption class="caption-top text-center bg-dark">Listado de Productos</caption>
                <tr>
                    <th class="bg-secondary text-white">Producto</th> 
                    <th class="bg-secondary text-white">Foto Principal</th>
                    <th class="bg-secondary text-white">Marca</th> <th class="bg-secondary text-white">Modelo</th> <th class="bg-secondary text-white">Año</th> <th class="bg-secondary text-white">Descripción</th>
                    <th class="bg-secondary text-white">Categoría</th>
                    <th class="bg-secondary text-white">Precio</th>
                    <th class="bg-secondary text-white">Editar</th>
                    <th class="bg-secondary text-white">Eliminar</th>
                </tr>

                <?php
                $ruta_txt = '../txt/juegos.txt';

                if (file_exists($ruta_txt) && is_readable($ruta_txt)) {
                    $lineas = file($ruta_txt, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                    if (!empty($lineas)) {
                        $contador_productos = 1; // Inicializamos un contador para los IDs visuales
                        foreach ($lineas as $indice_linea_actual => $linea) {
                            $datos = explode(';', $linea);

                            // Aseguramos que tenemos suficientes elementos (ahora 9 con los nuevos campos)
                            if (count($datos) >= 9) {
                                $producto_id_unico = htmlspecialchars($datos[0]); 
                                $marca = htmlspecialchars($datos[1]); // NUEVO CAMPO
                                $modelo = htmlspecialchars($datos[2]); // NUEVO CAMPO
                                $anio = htmlspecialchars($datos[3]); // NUEVO CAMPO
                                $descripcion = htmlspecialchars($datos[4]);
                                $categoria = htmlspecialchars($datos[5]);
                                $precio = htmlspecialchars($datos[6]);
                                $foto_principal = htmlspecialchars($datos[7]); 
                                
                                $ruta_foto_completa = '../portadas/' . $foto_principal;
                                if (!file_exists($ruta_foto_completa) || empty($foto_principal)) {
                                    $ruta_foto_completa = '../img/default_product.png';
                                }

                                echo '<tr>';
                                echo '<td>' . $contador_productos . '</td>'; 
                                echo '<td><img src="' . $ruta_foto_completa . '" alt="Foto de ' . $marca . '" style="width: 80px; height: auto;"></td>';
                                echo '<td>' . $marca . '</td>'; // NUEVA CELDA
                                echo '<td>' . $modelo . '</td>'; // NUEVA CELDA
                                echo '<td>' . $anio . '</td>'; // NUEVA CELDA
                                echo '<td>' . $descripcion . '</td>';
                                echo '<td>' . $categoria . '</td>';
                                echo '<td>$' . number_format($precio, 2, ',', '.') . '</td>';

                                // Los enlaces de editar y eliminar DEBEN seguir usando el ID ÚNICO
                                echo '<td><a href="editar_producto.php?id=' . $producto_id_unico . '" ><img src="../bt/modificar.png" alt="Editar"></a></td>';
                                echo '<td><a href="eliminar_producto.php?id=' . $producto_id_unico . '" ><img src="../bt/eliminar.png" alt="Eliminar"></a></td>';
                                echo '</tr>';

                                $contador_productos++; // Incrementamos el contador para la siguiente fila
                            } else {
                                echo '<tr><td colspan="10">Error: Datos incompletos en la línea ' . ($indice_linea_actual + 1) . ' del archivo de productos.</td></tr>';
                            }
                        }
                    } else {
                        echo '<tr><td colspan="10" class="text-center">No hay productos cargados aún.</td></tr>';
                    }
                } else {
                    echo '<tr><td colspan="10" class="text-center">El archivo de productos (juegos.txt) no existe o no se puede leer.</td></tr>';
                }
                ?>
            </table>
        </article>
    </section>
</main>

<?php
    require("pie.php");
?>
