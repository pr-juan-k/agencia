<?php
session_start();

    require("php/encabezado.php");
    
?>
    
    <form class="container col-md-5" action="php/procesa.php" method="post" enctype="multipart/form-data">
    <h2>Agregar Vehículo</h2>
    <section class="menu_tmp">
        <a class="btn btn-dark" href="../../index.php">Ver web</a> 
        <a class="btn btn-dark" href="php/list_products.php">Ver Cargados</a>
    </section>
      
    <div class="mb-3">
        <label for="formFile1" class="form-label">1 fotografia:</label>
        <input class="form-control" name="foto1" type="file" id="formFile1">
    </div>
    <div class="mb-3">
        <label for="formFile2" class="form-label">2 fotografia:</label>
        <input class="form-control" name="foto2" type="file" id="formFile2">
    </div>
    <div class="mb-3">
        <label for="formFile3" class="form-label">3 fotografia:</label>
        <input class="form-control" name="foto3" type="file" id="formFile3">
    </div>

    <div class="mb-3">
        <label for="marca" class="form-label">Marca:</label>
        <input type="text" name="marca" class="form-control" id="marca">
    </div>

    <div class="mb-3">
        <label for="modelo" class="form-label">Modelo:</label>
        <input type="text" name="modelo" class="form-control" id="modelo">
    </div>

    <div class="mb-3">
        <label for="anio" class="form-label">Año del Modelo:</label>
        <input type="number" name="anio" class="form-control" id="anio" min="1900" max="2100">
    </div>

    <div class="mb-3">
        <label for="descripcion" class="form-label">Descripcion:</label>
        <input type="text" name="descripcion" class="form-control" id="descripcion">
    </div>

    <div class="mb-3">
        <label for="categoria" class="form-label">Categoria:</label>
        <select name="categoria" id="identificador_unico" class="form-select">
            <option value="">Seleccione una categoria</option>
            <option value="auto">Auto</option>
            <option value="camioneta">Camioneta</option>
            <option value="moto">Moto</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="precio">Precio:$</label>
        <input type="number" id="precio" name="precio" placeholder="Ingrese el precio" min="0" step="0.01">
    </div>
      
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Guardar</button>
    </div>
</form>
<?php
    require("php/pie.php");
?>