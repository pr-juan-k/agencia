<?php
session_start();

// Define the path to the text file where products are stored
$ruta_txt = '../txt/juegos.txt';
$errores = []; // Array to store any error messages
$producto_datos = null; // Variable to hold product data for pre-filling the form
$producto_id_a_editar = ''; // Stores the unique ID of the product being edited
$linea_a_modificar_indice = -1; // Stores the line index in the file (for internal use)

// --- LÓGICA DE PROCESAMIENTO DEL FORMULARIO ENVIADO (MÉTODO POST) ---
// This block executes when the form is submitted to update a product.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the unique product ID from the hidden input field
    $producto_id_a_editar = isset($_POST['producto_id']) ? $_POST['producto_id'] : '';

    // 1. Validate required text fields
    if (empty($_POST['titulo']) || empty($_POST['descripcion']) || empty($_POST['categoria']) || empty($_POST['precio'])) {
        $errores[] = "Todos los campos de texto son obligatorios.";
    }

    // 2. Read all lines from the product file to find the specific product
    $lineas = [];
    if (file_exists($ruta_txt) && is_readable($ruta_txt)) {
        $lineas = file($ruta_txt, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    } else {
        $errores[] = "El archivo de productos no existe o no se puede leer.";
    }

    // Initialize an array to hold current photo names (will be updated if new photos are uploaded)
    $nombres_fotos_actuales = ['', '', '']; 

    // If no initial errors, search for the product by its ID
    if (empty($errores)) {
        foreach ($lineas as $idx => $linea) {
            $datos_linea = explode(';', $linea);
            // Check if the ID from the file matches the ID of the product being edited
            if (isset($datos_linea[0]) && $datos_linea[0] === $producto_id_a_editar) {
                $linea_a_modificar_indice = $idx; // Store the array index for direct modification
                // Ensure the line has all expected data (ID + 7 data fields = 8 total)
                if (count($datos_linea) >= 8) {
                    $nombres_fotos_actuales[0] = $datos_linea[5]; // Current name of photo 1
                    $nombres_fotos_actuales[1] = $datos_linea[6]; // Current name of photo 2
                    $nombres_fotos_actuales[2] = $datos_linea[7]; // Current name of photo 3
                }
                break; // Product found, exit the loop
            }
        }

        // If the product wasn't found, add an error
        if ($linea_a_modificar_indice === -1) {
            $errores[] = "Error: El producto con el ID especificado no se encontró para actualizar.";
        }
    }
    
    // Arrays to store temporary and final paths for new photo uploads
    $origenes = [];
    $destinos = [];
    // Initialize the array for new photo names with the *current* photo names.
    // If a photo isn't updated, its name will remain from `nombres_fotos_actuales`.
    $nuevos_nombres_fotos_guardadas = $nombres_fotos_actuales; 

    // Process each potential photo upload (foto1, foto2, foto3)
    for ($i = 1; $i <= 3; $i++) {
        $campo_foto = 'foto' . $i;
        // Check if a new file was uploaded for this photo field without errors
        if (isset($_FILES[$campo_foto]) && $_FILES[$campo_foto]['error'] === UPLOAD_ERR_OK && !empty($_FILES[$campo_foto]['name'])) {
            $nombre_original = $_FILES[$campo_foto]['name'];
            $origen = $_FILES[$campo_foto]['tmp_name'];
            $ext = pathinfo($nombre_original, PATHINFO_EXTENSION); // Get file extension

            // Sanitize the product title to create unique, clean filenames
            $titulo_sanitizado = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['titulo']);
            $destino = '../portadas/' . $titulo_sanitizado . '_' . $i . '.' . $ext; // Construct new file path

            $origenes[$i] = $origen; // Store the temporary source path
            $destinos[$i] = $destino; // Store the final destination path
            $nuevos_nombres_fotos_guardadas[$i-1] = basename($destino); // Update the photo name in our array
        }
    }

    // If no errors occurred during validation and product lookup, proceed with updating
    if (empty($errores)) {
        $carpeta_portadas = '../portadas/';
        // Create the 'portadas' directory if it doesn't exist
        if (!file_exists($carpeta_portadas)) {
            mkdir($carpeta_portadas, 0777, true);
        }

        $todas_las_fotos_movidas = true;
        // Attempt to move all newly uploaded files from temp location to 'portadas'
        foreach ($origenes as $idx => $origen) {
            if (!move_uploaded_file($origen, $destinos[$idx])) {
                $todas_las_fotos_movidas = false;
                $errores[] = "Error al mover la foto " . $idx . ". Verifique los permisos de la carpeta 'portadas'.";
                break; // Stop if any file transfer fails
            }
        }

        // If all new photos were moved successfully
        if ($todas_las_fotos_movidas) {
            // Construct the new line of data for the product.
            // The unique ID ($producto_id_a_editar) is kept the same.
            $datos_a_guardar = [
                $producto_id_a_editar,
                $_POST['titulo'],
                $_POST['descripcion'],
                $_POST['categoria'],
                $_POST['precio'],
                $nuevos_nombres_fotos_guardadas[0], // Updated or existing photo name 1
                $nuevos_nombres_fotos_guardadas[1], // Updated or existing photo name 2
                $nuevos_nombres_fotos_guardadas[2]  // Updated or existing photo name 3
            ];
            $nueva_linea = implode(';', $datos_a_guardar); // Join data with semicolons

            // Update the specific line in the array of all lines from the file
            $lineas[$linea_a_modificar_indice] = $nueva_linea;

            // Open the file in write mode ('w') to overwrite its entire content
            $archi = fopen($ruta_txt, 'w');
            if ($archi) {
                // Write each line from the modified array back to the file
                foreach ($lineas as $linea_actual) {
                    fputs($archi, $linea_actual . PHP_EOL); // PHP_EOL ensures proper line endings for the OS
                }
                fclose($archi); // Close the file
                echo '<p>Producto actualizado con éxito.</p>';
                // Redirect back to the product list after a short delay
                header('refresh:2;url=list_products.php');
                exit(); // Stop script execution after sending the redirection header
            } else {
                $errores[] = "No se pudo abrir el archivo para escritura al actualizar. Verifique permisos.";
            }
        }
    }
    // If there were any errors during POST processing, the script will continue
    // to render the form, and the error messages will be displayed.
}

// --- LÓGICA PARA MOSTRAR EL FORMULARIO PRELLENADO (MÉTODO GET) ---
// This block executes when the page is loaded via a GET request (e.g., from a link in the product list).
// It fetches the product data to pre-fill the form.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && !empty($_GET['id'])) {
    $producto_id_a_editar = $_GET['id']; // Get the product ID from the URL query string

    if (file_exists($ruta_txt) && is_readable($ruta_txt)) {
        $lineas = file($ruta_txt, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lineas as $idx => $linea) {
            $datos_linea = explode(';', $linea);
            // Find the product by its unique ID
            if (isset($datos_linea[0]) && $datos_linea[0] === $producto_id_a_editar) {
                $producto_datos = $datos_linea; // Store the found product's data
                $linea_a_modificar_indice = $idx; // Also store its line index (useful if needing to re-index the file later)
                break; // Product found, no need to continue searching
            }
        }
    }
    
    // If product data wasn't found or is incomplete (less than 8 fields), display an error and redirect
    if ($producto_datos === null || count($producto_datos) < 8) {
        echo '<p>Error: Producto no encontrado o datos incompletos para editar.</p>';
        header('refresh:3;url=list_products.php');
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && (!isset($_GET['id']) || empty($_GET['id']))) {
    // If no ID was provided in the URL for a GET request, display an error and redirect
    echo '<p>Error: No se ha especificado un producto para editar.</p>';
    header('refresh:3;url=list_products.php');
    exit();
}


// Display any accumulated error messages to the user
if (!empty($errores)) {
    echo '<div style="color: red; padding: 10px; border: 1px solid red; margin-bottom: 10px;">';
    foreach ($errores as $error) {
        echo '<p>' . htmlspecialchars($error) . '</p>';
    }
    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/bootstrap/css/bootstrap.min.css">

    <link rel="stylesheet" href="../css/st.css">
    <title>Carpinteria david</title>
</head>
<body>
    

<main class="container col-md-5">
    <h2>Editar Producto</h2>
    <form action="editar_producto.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="producto_id" value="<?php echo htmlspecialchars($producto_id_a_editar); ?>">
        <input type="hidden" name="indice_linea" value="<?php echo htmlspecialchars($linea_a_modificar_indice); ?>">
        
        <div class="mb-3">
            <label for="formFile1" class="form-label">1 fotografía (actual: <?php echo htmlspecialchars($producto_datos[5] ?? 'N/A'); ?>):</label>
            <input class="form-control" name="foto1" type="file" id="formFile1">
            <small class="text-muted">Dejar vacío para mantener la foto actual.</small>
        </div>
        <div class="mb-3">
            <label for="formFile2" class="form-label">2 fotografía (actual: <?php echo htmlspecialchars($producto_datos[6] ?? 'N/A'); ?>):</label>
            <input class="form-control" name="foto2" type="file" id="formFile2">
            <small class="text-muted">Dejar vacío para mantener la foto actual.</small>
        </div>
        <div class="mb-3">
            <label for="formFile3" class="form-label">3 fotografía (actual: <?php echo htmlspecialchars($producto_datos[7] ?? 'N/A'); ?>):</label>
            <input class="form-control" name="foto3" type="file" id="formFile3">
            <small class="text-muted">Dejar vacío para mantener la foto actual.</small>
        </div>

        <div class="mb-3">
            <label for="titulo" class="form-label">Título:</label>
            <input type="text" name="titulo" class="form-control" id="titulo" value="<?php echo htmlspecialchars($producto_datos[1] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción:</label>
            <input type="text" name="descripcion" class="form-control" id="descripcion" value="<?php echo htmlspecialchars($producto_datos[2] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="categoria" class="form-label">Categoría:</label>
            <select name="categoria" id="categoria" required>
                <option value="dormitorio1" <?php if (($producto_datos[3] ?? '') == 'dormitorio1') echo 'selected'; ?>>dormitorio 1</option>
                <option value="salon2" <?php if (($producto_datos[3] ?? '') == 'salon2') echo 'selected'; ?>>salón 2</option>
                <option value="cocina3" <?php if (($producto_datos[3] ?? '') == 'cocina3') echo 'selected'; ?>>cocina 3</option>
                <option value="bano3" <?php if (($producto_datos[3] ?? '') == 'bano3') echo 'selected'; ?>>baño 3</option>
                </select>
        </div>
        <div class="mb-3">
            <label for="precio">Precio:$</label>
            <input type="number" id="precio" name="precio" placeholder="Ingrese el precio" min="0" step="0.01" value="<?php echo htmlspecialchars($producto_datos[4] ?? ''); ?>" required>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Actualizar Producto</button>
            <a href="list_products.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</main>

<?php
require("pie.php"); // Include your footer file
?>