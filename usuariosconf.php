<?php
require 'db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Conferencias</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        h2 { background: #007bff; color: white; padding: 10px; }
    </style>
</head>
<body>
    <h1>Lista de Asistentes por Conferencia</h1>

    <?php
    $conferencias = $collConferencias->find([], ['sort' => ['id_conferencia' => 1]]);

    foreach ($conferencias as $conf) {
        $id_conf = $conf['id_conferencia'];
        echo "<h2>" . htmlspecialchars($conf['titulo_conf']) . " <small>[" . $conf['fecha_inicio'] . "]</small></h2>";

        // Buscar tickets de esta conferencia y unir con usuario
        // SQL: JOIN Ticket T JOIN Usuario U ...
        $pipeline = [
            [ '$match' => [ 'id_conferencia' => $id_conf ] ],
            [
                '$lookup' => [
                    'from' => 'Usuario',
                    'localField' => 'id_usuario',
                    'foreignField' => 'id_usuario',
                    'as' => 'datos_usuario'
                ]
            ],
            [ '$unwind' => '$datos_usuario' ] // Aplanar el array resultante
        ];

        $asistentes = $collTickets->aggregate($pipeline);
        $asistentesArray = iterator_to_array($asistentes);

        if (count($asistentesArray) === 0) {
            echo "<p><i>No hay inscritos.</i></p>";
        } else {
            echo "<table>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Folio</th>
                        <th>Fecha Registro</th>
                    </tr>";
            foreach ($asistentesArray as $row) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['datos_usuario']['nombre']) . "</td>
                        <td>" . htmlspecialchars($row['datos_usuario']['email']) . "</td>
                        <td>" . htmlspecialchars($row['datos_usuario']['folio']) . "</td>
                        <td>" . $row['fecha_registro'] . "</td>
                      </tr>";
            }
            echo "</table>";
        }
    }
    ?>
</body>
</html>