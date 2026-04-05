<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo = $_POST['titulo_conf'] ?? '';
    $cupo = (int)$_POST['cupo'] ?? 0;
    $fecha = $_POST['fecha_inicio'] ?? '';

    if ($titulo && $cupo > 0 && $fecha) {
        // Generar ID manual
        $ultimaConf = $collConferencias->findOne([], ['sort' => ['id_conferencia' => -1]]);
        $nuevoId = ($ultimaConf) ? $ultimaConf['id_conferencia'] + 1 : 1;

        $collConferencias->insertOne([
            'id_conferencia' => $nuevoId,
            'titulo_conf' => $titulo,
            'cupo' => $cupo,
            'fecha_inicio' => $fecha
        ]);
        
        echo "<p style='color:green'>Conferencia insertada.</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<body>
<form method="POST">
    Titulo: <input type="text" name="titulo_conf" required><br>
    Cupo: <input type="number" name="cupo" required><br>
    Fecha: <input type="date" name="fecha_inicio" required><br>
    <button type="submit">Guardar</button>
</form>
</body>
</html>