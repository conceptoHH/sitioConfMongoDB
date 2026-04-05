<?php
require 'db.php';

// Pipeline de Agregación para obtener conferencias y contar tickets vendidos
$pipeline = [
    [
        '$lookup' => [
            'from' => 'Ticket',              // Colección a unir
            'localField' => 'id_conferencia',// Campo en Conferencias
            'foreignField' => 'id_conferencia', // Campo en Ticket
            'as' => 'tickets_vendidos'       // Nombre del array resultante
        ]
    ],
    [
        '$addFields' => [
            'vendidos' => [ '$size' => '$tickets_vendidos' ] // Contar tamaño del array
        ]
    ],
    [
        '$project' => [
            'tickets_vendidos' => 0 // Ocultar el array pesado, solo queremos el conteo
        ]
    ],
    [ '$sort' => ['id_conferencia' => 1] ]
];

try {
    $cursor = $collConferencias->aggregate($pipeline);
} catch (Exception $e) {
    die("Error obteniendo conferencias: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Conferencias</title>
<style>
    * {
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background-color: #f4f4f4;
      margin: 0;
      padding: 2em;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    form {
      background-color: #fff;
      padding: 2em;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 500px;
      margin-bottom: 2em;
    }

    .campo {
      display: flex;
      flex-direction: column;
      margin-bottom: 1em;
    }

    label {
      margin-bottom: 5px;
      font-weight: bold;
    }

    input, select {
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    button {
      padding: 10px;
      background-color: #28a745;
      border: none;
      border-radius: 5px;
      color: #fff;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #218838;
    }

    table {
      width: 100%;
      max-width: 800px;
      border-collapse: collapse;
      background-color: #fff;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 10px;
      border: 1px solid #ddd;
      text-align: center;
    }

    th {
      background-color: #007bff;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
  </style>
</head>
<body>

  <h1>Registro de Conferencias</h1>

  <form method="POST" action="enviar.php">
    <div class="campo">
      <label>Nombre completo:</label>
      <input type="text" name="nombre" required>
    </div>
    <div class="campo">
      <label>Email:</label>
      <input type="email" name="email" required>
    </div>
    <div class="campo">
      <label>Elige la conferencia:</label>
      <select name="id_conferencia" required>
        <option value="">-- Selecciona --</option>
        <?php 
        // Necesitamos iterar el cursor dos veces o guardarlo en array
        $conferenciasArray = iterator_to_array($cursor);
        foreach($conferenciasArray as $conf): 
        ?>
          <option value="<?= $conf['id_conferencia'] ?>">
            <?= htmlspecialchars($conf['titulo_conf']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit">Enviar Inscripción</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Título</th>
        <th>Cupo</th>
        <th>Restantes</th>
        <th>Fecha Inicio</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($conferenciasArray as $conf): 
          $restantes = $conf['cupo'] - $conf['vendidos'];
          $restantes = ($restantes < 0) ? 0 : $restantes;
      ?>
      <tr>
        <td><?= $conf['id_conferencia'] ?></td>
        <td><?= htmlspecialchars($conf['titulo_conf']) ?></td>
        <td><?= $conf['cupo'] ?></td>
        <td><strong><?= $restantes ?></strong></td>
        <td><?= $conf['fecha_inicio'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>